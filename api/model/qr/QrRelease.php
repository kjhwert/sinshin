<?php

class QrRelease extends Model
{
    protected $createRequired = [
        'to_id' => 'integer',
        'from_id' => 'integer',
        'is_outsourcing' => 'string'
    ];

    protected $sort = [
        'date' => 'process_date',
        'name' => 'product_name'
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $reversedSort = true;

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_release = Code::$PROCESS_RELEASE;
        $injection = Dept::$INJECTION;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no, c.jaje_code,
                   d.name as product_name, e.name as material_name, b.process_date, b.to_name, b.manager
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, 
                                    dd.name as to_name, ee.name as manager
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            inner join `user` ee
                            on aa.created_id = ee.id
                            where aa.process_stts = {$process_release} and bb.process_status = {$process_release} 
                            and aa.dept_id = {$injection}
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
        $process_release = Code::$PROCESS_RELEASE;
        $injection = Dept::$INJECTION;

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            where aa.process_stts = {$process_release} and bb.process_status = {$process_release} 
                            and aa.dept_id = {$injection}
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

    /**
     * @param array $data
     * 출고할 제품의 상태를 변경한다.
     */
    public function create(array $data = [])
    {
        /**
         *  release insert
         *  qr_code update
         *  change_stts insert
         *  lot은 입고될 때 dept_id 업데이트
         *  box update
         */
        $process_release = Code::$PROCESS_RELEASE;
        $group_id = $this->generateRandomString();

        try {
            $this->db->beginTransaction();

            foreach ($data['qr_ids'] as $qr_id) {

                $sql = "select id from box where qr_id = {$qr_id}";
                $box_id = $this->fetch($sql)[0]['id'];

                $sqls = [
                    "insert into `release` set
                     box_id = {$box_id},
                     qr_id = {$qr_id},
                     group_id = '{$group_id}',
                     to_id = {$data['to_id']},
                     from_id = {$data['from_id']},
                     is_outsourcing = '{$data['is_outsourcing']}',
                     in_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update qr_code set
                     process_stts = {$process_release},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$qr_id}
                    ",
                    "insert into change_stts set
                     qr_id = {$qr_id},
                     process_status = {$process_release},
                     process_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update box set
                     process_status = {$process_release},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$box_id}
                    "
                ];

                foreach ($sqls as $query) {
                    try {
                        $stmt = $this->db->prepare($query);
                        $stmt->execute();
                    } catch (Exception $e) {
                        throw $e;
                    }
                }

                $this->db->commit();
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }

        return new Response(200, [],'출고되었습니다.');
    }

    /**
     * @param null $id
     * @param array $data
     * @return Response
     * 출고할 제품을 QR스캔앱 리스트에 담는다.
     */
    public function update($id = null, array $data = [])
    {
        if (!$id) {
            return (new ErrorHandler())->typeNull('id');
        }

        $lot_id = $this->isLotQrCode($id);
        if ($lot_id) {
            return $this->printLotList($lot_id);
        }

        $box_id = $this->isBoxQrCode($id);
        if ($box_id) {
            $this->isStockQrCode($id);
            return $this->printBox($box_id);
        }

        return new Response(403, [],'lot 혹은 box를 스캔해주세요.');
    }

    protected function isStockQrCode($qr_id = null)
    {
        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select process_stts from qr_code where id = {$qr_id}";
        $process_stts = $this->fetch($sql)[0]['process_stts'];

        if ($process_stock !== $process_stts) {
            return new Response(403, [], '재고상태의 제품이 아닙니다.');
        }
    }

    protected function printBox($box_id = null)
    {
        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where a.id = {$box_id} and qc.process_stts = {$process_stock}";

        return new Response(200, $this->fetch($sql)[0]);
    }

    protected function printLotList($lot_id = null)
    {
        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where a.lot_id = {$lot_id} and a.process_status = {$process_stock}";

        return new Response(200, $this->fetch($sql));
    }

    protected function isLotQrCode ($id = null)
    {
        $sql = "select id from lot where id = {$id}";
        return $this->fetch($sql)[0]['id'];
    }

    protected function isBoxQrCode ($id = null)
    {
        $sql = "select id from box where qr_id = {$id}";
        return $this->fetch($sql)[0]['id'];
    }

    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
