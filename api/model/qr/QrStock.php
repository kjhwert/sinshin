<?php

class QrStock extends Model
{
    protected $sort = [
        'process_date' => 'process_date',
        'product_name' => 'product_name'
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $searchableAsset = 'aa.asset_id';

    protected $updateRequired = [
        'lot_id' => 'integer'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_stock = Code::$PROCESS_STOCK;
        $injection = Dept::$INJECTION;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no,
                   d.name as product_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_stock} and bb.process_status = {$process_stock} 
                            and aa.dept_id = {$injection}
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
        $process_stock = Code::$PROCESS_STOCK;
        $injection = Dept::$INJECTION;

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_stock} and aa.dept_id = {$injection}
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

    public function update($id = null, array $data = [])
    {
        $data = $this->validate($data, $this->updateRequired);

        $sql = "select process_stts, product_id from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];
        $process_complete = Code::$PROCESS_COMPLETE;
        $process_stock = Code::$PROCESS_STOCK;

//        $sql = "select qc.product_id from box a
//                    inner join qr_code qc
//                on a.qr_id = qc.id
//                where a.lot_id = {$data['lot_id']}";
//
//        $lot_product_id = $this->fetch($sql)[0]['product_id'];
//
//        if ($result['product_id'] !== $lot_product_id) {
//            return new Response(403, [], '동일한 제품만 올릴 수 있습니다.');
//        }

        if ($result['process_stts'] === $process_stock) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        if ($result['process_stts'] !== $process_complete) {
            return new Response(403, [], '공정완료 상태인 제품을 스캔해주세요.');
        }

        $sqls = [
            "update qr_code set
                process_stts = {$process_stock},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
            ",
            "insert into change_stts set
                qr_id = {$id},
                process_status = {$process_stock},
                process_date = SYSDATE(),
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
            "update box set
                lot_id = {$data['lot_id']},
                process_status = {$process_stock},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where qr_id = {$id}
            ",
            "insert into inventory set
                qr_id = {$id},
                lot_id = {$data['lot_id']},
                inven_date = SYSDATE(),
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            "
        ];

        return $this->setTransaction($sqls);
    }
}
