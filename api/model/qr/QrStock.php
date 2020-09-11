<?php

class QrStock extends Model
{
    protected $sort = [
        'date' => 'process_date',
        'asset' => 'asset_name',
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $searchableAsset = 'aa.asset_id';

    protected $updateRequired = [
        'lot_id' => 'integer'
    ];

    protected $reversedSort = true;

    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no,
                   d.name as product_name, b.asset_name, b.process_date, b.display_name
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date, cc.name as asset_name, cc.display_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where bb.process_status = {$process_stock} 
                            and aa.dept_id = {$dept_id}
                            {$this->searchAsset($params['params'])} 
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
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                                    bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where bb.process_status = {$process_stock} and aa.dept_id = {$dept_id}
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

    public function qrShow ($id = null)
    {
        (new QrBox())->isBox($id);

        $sql = "select o.order_no, pm.name as product_name,
                    qr.id as qr_id, qr.process_stts, qr.qty, pm.id as product_id, 1 as box_qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];

        return new Response(200, $result);
    }

    public function update($id = null, array $data = [])
    {
        $data = $this->validate($data, $this->updateRequired);
        $this->isAvailableUser();
        $this->isDeptProcess($id);
        (new QrBox())->isBox($id);

        $sql = "select process_stts, product_id from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];
        $process_complete = Code::$PROCESS_COMPLETE;
        $process_stock = Code::$PROCESS_STOCK;

        /** Lot을 변경할 경우 */
        if ($result['process_stts'] === $process_stock) {

            $sql = "select lot_id from box where qr_id = {$id}";
            $lot_id = $this->fetch($sql)[0]['lot_id'];

            if ($lot_id === $data['lot_id']) {
                return new Response(403, [], '이미 처리되었습니다.');
            }

            $sqls = [
                "update box set
                    lot_id = {$data['lot_id']},
                    updated_id = {$this->token['id']},
                    updated_at = SYSDATE()
                where qr_id = {$id}
                ",
                "update inventory set
                    lot_id = {$data['lot_id']},
                    updated_id = {$this->token['id']},
                    updated_at = SYSDATE()
                where qr_id = {$id}
                "
            ];

            $this->setTransaction($sqls);
            return $this->qrShow($id);
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
                dept_id = {$this->token['dept_id']},
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

        $this->setTransaction($sqls);
        return $this->qrShow($id);
    }

    protected function setTransaction (array $data = [])
    {
        try {
            $this->db->beginTransaction();

            foreach ($data as $query) {
                try {
                    $stmt = $this->db->prepare($query);
                    $stmt->execute();
                } catch (Exception $e) {
                    throw $e;
                }
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }
    }
}
