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
        'product' => 'product_name',
        'customer' => 'b.to_name'
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
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no, c.jaje_code, b.asset_no,
                   d.name as product_name, b.process_date, b.to_name, b.manager
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                    bb.process_date, dd.name as to_name, ee.name as manager, ff.asset_no
                            from qr_code aa
                            inner join (select * from change_stts 
                                        where process_status = {$process_release}
                                        and dept_id = {$dept_id}
                                        order by created_at desc LIMIT 18446744073709551615
                                        ) bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            inner join `user` ee
                            on aa.created_id = ee.id
                            inner join asset ff
                            on aa.asset_id = ff.id
                            where bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
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
        $process_release = Code::$PROCESS_RELEASE;
        $dept_id = $this->getDeptId();

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
                            and aa.dept_id = {$dept_id}
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    /**
     * @param array $data
     * 출고할 제품의 상태를 변경한다.
     */
    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);
        $this->isAvailableUser();

        $process_release = Code::$PROCESS_RELEASE;
        $group_id = $this->generateRandomString();

        try {
            $this->db->beginTransaction();

            foreach ($data['qr_ids'] as $qr_id) {

                $this->isDeptProcess($qr_id);
                $sql = "select id from box where qr_id = {$qr_id}";
                $box_id = $this->fetch($sql)[0]['id'];

                /** Lot일 경우 pass */
                if (!$box_id) {
                    continue;
                }

                $sqls = [
                    "insert into `release` set
                     box_id = {$box_id},
                     qr_id = {$qr_id},
                     group_id = '{$group_id}',
                     to_id = {$data['to_id']},
                     from_id = {$data['from_id']},
                     dept_id = {$this->token['dept_id']},
                     is_outsourcing = '{$data['is_outsourcing']}',
                     in_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update qr_code set
                     from_id = {$data['from_id']},
                     process_stts = {$process_release},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$qr_id}
                    ",
                    "insert into change_stts set
                     qr_id = {$qr_id},
                     dept_id = {$this->token['dept_id']},
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
            }
            $this->db->commit();

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
        $this->isAvailableUser();
        $this->isDeptProcess($id);

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
        $process_complete = Code::$PROCESS_COMPLETE;
        $sql = "select process_stts from qr_code where id = {$qr_id}";
        $process_stts = $this->fetch($sql)[0]['process_stts'];

        if ($process_stts === $process_complete) {
            return new Response(403, [], '재고처리 후 출고하세요.');
        }

        if ($process_stock !== $process_stts) {
            return new Response(403, [], '재고상태의 제품이 아닙니다.');
        }
    }

    protected function printBox($box_id = null)
    {
        $sql = "update box set lot_id = null where id = {$box_id}";
        $this->fetch($sql);

        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where a.id = {$box_id} and qc.process_stts = {$process_stock}";

        return new Response(200, $this->fetch($sql));
    }

    protected function printLotList($lot_id = null)
    {
        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id
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
        $sql = "select id from lot where qr_id = {$id}";
        return $this->fetch($sql)[0]['id'];
    }

    protected function isBoxQrCode ($id = null)
    {
        $sql = "select id from box where qr_id = {$id}";
        return $this->fetch($sql)[0]['id'];
    }
}
