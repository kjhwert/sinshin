<?php

class QrComplete extends Model
{
    protected $table = 'qr_code';
    protected $createRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',
        'qty' => 'integer',
        'print_qty' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'string'
    ];

    protected $sort = [
        'date' => 'process_date',
        'asset' => 'asset_name',
        'product' => 'product_name'
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $searchableAsset = 'aa.asset_id';
    protected $reversedSort = true;

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no, c.jaje_code, b.display_name,
                   d.name as product_name, ifnull(e.name,'') as material_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date, cc.name as asset_name, cc.display_name
                            from qr_code aa
                            inner join (select * from change_stts 
                                        where process_status = {$process_complete}
                                        and dept_id = {$dept_id}
                                        order by created_at desc LIMIT 18446744073709551615
                                        ) bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id 
                            and aa.dept_id = {$dept_id}
                            {$this->searchAsset($params['params'])} 
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id
                            ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join material_master e
                on d.material_id = e.id
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT' and e.stts = 'ACT'
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
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_complete} and bb.process_status = {$process_complete} 
                            and aa.dept_id = {$dept_id}
                            and bb.dept_id = {$dept_id}
                            {$this->searchAsset($params)}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join material_master e
                on d.material_id = e.id
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT' and e.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select
                   cs.process_date, a.name as asset_name, o.order_no, po.id, o.id as order_id, u.name as manager,
                   pm.name as product_name, mm.name as material_name, a.asset_no, po.code as process_code,
                   o.jaje_code, qc.qty, a.display_name, @rownum:= @rownum+1 AS RNUM
                    from qr_code qc
                    inner join process_order po
                    on qc.process_order_id = po.id
                    inner join `order` o on qc.order_id = o.id
                    inner join change_stts cs
                    on qc.id = cs.qr_id
                    inner join `user` u
                    on cs.created_id = u.id
                    inner join asset a
                    on qc.asset_id = a.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                    inner join material_master mm
                    on pm.material_id = mm.id,
                    (SELECT @rownum:= 0) AS R
                where po.id = {$id} and cs.process_status = {$process_complete}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function qrShow ($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name, a.name as asset_name, a.asset_no,
                    qr.id, qr.process_stts, qr.qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                inner join material_master mm
                on qr.material_id = mm.id
                inner join asset a
                on qr.asset_id = a.id
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

            $sql = "select material_id from product_master where id = {$data['product_id']}";
            $material_id = $this->fetch($sql)[0]['material_id'];

            for ($i = 0; $i < $print_qty; $i++) {
                try {

                    $sql = "insert into qr_code set    
                                {$this->dataToString($data)},
                                process_stts = {$process_start},
                                material_id = {$material_id},
                                dept_id = {$dept_id},
                                created_id = {$this->token['id']},
                                created_at = '{$created_at}'
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $qr_id = $this->db->lastInsertId();

                    $sql = "select o.order_no, b.name as product_name, b.code, a.created_at, d.asset_no,
                                   a.qty, c.unit, c.name as material_name, d.name as asset_name, d.id as asset_id
                            from qr_code a
                                inner join product_master b
                                on a.product_id = b.id
                                inner join material_master c
                                on b.material_id = c.id
                                inner join asset d
                                on a.asset_id = d.id
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

    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();
        $this->isDeptProcess($id);

        $process_start = Code::$PROCESS_START;
        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select process_stts from qr_code where id = {$id}";
        $stts = $this->fetch($sql)[0]['process_stts'];

        if ($process_start !== $stts) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        $sqls = [
            "update {$this->table} set
                process_stts = {$process_complete},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
            ",
            "insert into change_stts set
                qr_id = {$id},
                dept_id = {$this->token['dept_id']},
                process_status = {$process_complete},
                process_date = SYSDATE(),
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
            "insert into box set
                qr_id = {$id},
                process_end_at = SYSDATE(),
                process_status = {$process_complete},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            "
        ];

        return $this->setTransaction($sqls);
    }
}
