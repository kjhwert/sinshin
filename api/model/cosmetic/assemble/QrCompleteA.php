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
                from (select a.id, b.product_qty, a.order_no,
                   d.name as product_name, b.box_qty, a.id order_id
                from `order` a
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join (
                                select * 
                                from change_stts
                                where dept_id = {$dept_id}
                                and process_status = {$process_complete}
                            ) bb
                            on aa.id = bb.qr_id
                            where aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id
                            ) b
                on a.id = b.order_id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id order by {$this->sorting($params['params'])}) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        $results = $this->fetch($sql);
        foreach ($results as $key=>$value) {
            $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                        from (select pc.name as type, pm.name as product_name,
                               count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                               bb.process_date, u.name as manager, po.code
                        from qr_code aa
                        inner join (
                            select *
                                from change_stts
                                where process_status = {$process_complete}
                                and dept_id = {$dept_id}
                            order by created_at desc limit 18446744073709551615
                        ) bb
                        on aa.id = bb.qr_id
                        inner join `user` u
                        on bb.created_id = u.id
                        inner join product_master pm
                        on aa.product_id = pm.id
                        inner join process_order po
                        on aa.process_order_id = po.id
                        left join process_code pc
                        on po.process_type = pc.code
                        where aa.stts = 'ACT'
                          and bb.stts = 'ACT'
                          and aa.order_id = {$value['id']}
                        group by aa.process_order_id ) tot,
                        (SELECT @rownum:= 0) AS R
                        order by RNUM desc";

            $results[$key]['process_order'] = $this->fetch($sql);
        }

        return new Response(200, $results, '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        return "select count(a.id) cnt
                from `order` a
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join (
                                select * 
                                from change_stts
                                where dept_id = {$dept_id}
                                and process_status = {$process_complete}
                            ) bb
                            on aa.id = bb.qr_id
                            where aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id
                            ) b
                on a.id = b.order_id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}
                group by a.id";
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
        $dept_id = $this->getDeptId();

        $sql = "select
                   cs.process_date, o.order_no, u.name as manager,
                   pm.name as product_name, qc.qty, @rownum:= @rownum+1 AS RNUM
                    from qr_code qc
                    inner join `order` o 
                    on qc.order_id = o.id
                    inner join (
                        select * 
                        from change_stts
                        where process_status = {$process_complete}
                        and dept_id = {$dept_id}) cs
                    on qc.id = cs.qr_id
                    inner join `user` u
                    on cs.created_id = u.id
                    inner join product_master pm
                    on qc.product_id = pm.id,
                    (SELECT @rownum:= 0) AS R
                where o.id = {$id}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function create(array $data = [])
    {
        $this->isAvailableUser();
        $data = $this->validate($data, $this->createRequired);

        if ($data['print_qty'] > 50) {
            return new Response(403, [], '출력 수량이 50장을 초과할 수 없습니다.');
        }

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
                                qr_master_id = {$this->qrMasterId},
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
