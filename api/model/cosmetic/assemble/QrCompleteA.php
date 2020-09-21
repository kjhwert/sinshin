<?php

class QrCompleteA extends QrCompleteP
{
    protected $sort = [
        'date' => 'process_date',
        'product' => 'product_name'
    ];

    protected function getDeptId ()
    {
        return Dept::$ASSEMBLE;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no, c.jaje_code,
                   d.name as product_name, b.process_date, e.name as type
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join (select * from change_stts 
                                        where process_status = {$process_complete}
                                        and dept_id = {$dept_id}
                                        order by created_at desc LIMIT 18446744073709551615
                                        ) bb
                            on aa.id = bb.qr_id 
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id
                            ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join process_code e
                on a.process_type = e.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id order by {$this->sorting($params['params'])}) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where aa.process_stts = {$process_complete} and bb.process_status = {$process_complete}
                            and bb.dept_id = {$dept_id} 
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function qrShow ($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name, qr.id, qr.process_stts, qr.qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];
        $process_start = Code::$PROCESS_START;
        $process_complete = Code::$PROCESS_COMPLETE;

        if ($result['process_stts'] === $process_complete) {
            return new Response(403, [], '이미 처리 되었습니다.');
        }

        if ($result['process_stts'] !== $process_start) {
            return new Response(403, [], 'QR코드를 확인해주세요.');
        }

        return new Response(200, $result);
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select
                   cs.process_date, o.order_no, po.id, u.name as manager, e.name as type,
                   pm.name as product_name, po.code as process_code, qc.qty, @rownum:= @rownum+1 AS RNUM
                    from qr_code qc
                    inner join process_order po
                    on qc.process_order_id = po.id
                    inner join `order` o on qc.order_id = o.id
                    inner join change_stts cs
                    on qc.id = cs.qr_id
                    inner join `user` u
                    on cs.created_id = u.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                    left join process_code e
                    on po.process_type = e.code,
                    (SELECT @rownum:= 0) AS R
                where po.id = {$id} and cs.process_status = {$process_complete}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function create(array $data = [])
    {
        $this->isAvailableUser();
        $data = $this->validate($data, $this->createRequired);

        $print_qty = $data['print_qty'];
        $created_at = $data['created_at'];
        unset($data['print_qty']);
        unset($data['created_at']);

        $dept_id = $this->getDeptId();
        $process_start = Code::$PROCESS_START;

        $print_result = [];
        try {
            $this->db->beginTransaction();

            $sql = "select name from dept where id = {$this->token['dept_id']}";
            $dept_name = $this->fetch($sql)[0]['name'];

            for ($i = 0; $i < $print_qty; $i++) {
                try {

                    $sql = "insert into qr_code set    
                                {$this->dataToString($data)},
                                process_stts = {$process_start},
                                dept_id = {$dept_id},
                                created_id = {$this->token['id']},
                                created_at = '{$created_at}'
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $qr_id = $this->db->lastInsertId();

                    $sql = "select o.order_no, b.name as product_name, a.created_at, a.qty
                            from qr_code a
                                inner join product_master b
                                on a.product_id = b.id
                                inner join `order` o
                                on a.order_id = o.id
                            where a.id = {$qr_id}
                            ";

                    $result = $this->fetch($sql)[0];
                    $result['qr_id'] = (int)$qr_id;
                    $result['dept_name'] = $dept_name;

                    array_push($print_result, $result);

                } catch (Exception $e) {
                    throw $e;
                }
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }

        return new Response(200, $print_result);
    }
}
