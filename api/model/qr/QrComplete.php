<?php

class QrComplete extends Model
{
    protected $table = 'qr_code';
    protected $createRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',
        'qty' => 'integer',
        'print_qty' => 'integer',
        'product_id' => 'integer'
    ];

    protected $sort = [
        'process_date' => 'process_date',
        'asset_name' => 'asset_name',
        'product_name' => 'product_name'
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $injection = AuthGroup::$INJECTION;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no, c.jaje_code,
                   d.name as product_name, e.name as material_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_complete} and bb.process_status = {$process_complete} 
                            and aa.auth_group_id = {$injection}
                            {$this->searchAsset($params['params'])} 
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_id = d.id
                inner join material_master e
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
        $injection = AuthGroup::$INJECTION;

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_complete} and bb.process_status = {$process_complete} 
                            and aa.auth_group_id = {$injection}
                            {$this->searchAsset($params)}
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_id = d.id
                inner join material_master e
                on d.material_id = e.id
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT' and e.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select
                   cs.process_date, a.name as asset_name, o.order_no, po.id,
                   pm.name as product_name, mm.name as material_name, o.jaje_code, qc.qty
                    from qr_code qc
                    inner join process_order po
                    on qc.process_order_id = po.id
                    inner join `order` o on qc.order_id = o.id
                    inner join change_stts cs
                    on qc.id = cs.qr_id
                    inner join asset a
                    on qc.asset_id = a.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                    inner join material_master mm
                    on pm.material_id = mm.id
                where po.id = {$id} and qc.process_stts = {$process_complete} and cs.process_status = {$process_complete}
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function create(array $data = [])
    {
        $data = $this->validate($data, $this->createRequired);
        $process_complete = Code::$PROCESS_COMPLETE;

        $print_qty = $data['print_qty'];
        unset($data['print_qty']);

        $injection = AuthGroup::$INJECTION;

        $print_result = [];
        try {
            $this->db->beginTransaction();

            for ($i = 0; $i < $print_qty; $i++) {
                try {

                    $sql = "insert into {$this->table} set
                                {$this->dataToString($data)},
                                process_stts = {$process_complete},
                                auth_group_id = {$injection},
                                dept_id = {$this->token['dept_id']},
                                created_id = {$this->token['id']},
                                created_at = SYSDATE()
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $qr_id = $this->db->lastInsertId();

                    $sql = "select a.order_id, b.name as material_name, b.code,
                                   a.qty, c.unit, c.name as product_name, d.name as asset_name
                            from qr_code a
                                inner join product_master b
                                on a.product_id = b.id
                                inner join material_master c
                                on b.material_id = c.id
                                inner join asset d
                                on a.asset_id = d.id
                            where a.id = {$qr_id}
                            ";

                    $result = $this->fetch($sql)[0];
                    $result['qr_id'] = (int)$qr_id;

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
        $process_complete = Code::$PROCESS_COMPLETE;

        $sqls = [
            "update {$this->table} set
                process_stts = {$process_complete},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
            ",
            "insert into change_stts set
                qr_id = {$id},
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
