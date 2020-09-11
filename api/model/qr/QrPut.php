<?php

class QrPut extends Model
{
    protected $createRequired = [
        'to_id' => 'integer',
        'from_id' => 'integer',
        'is_outsourcing' => 'string'
    ];

    protected $createOutSourcingRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',
        'product_id' => 'integer',
        'qty' => 'integer',
        'from_id' => 'integer',
        'process_date' => 'string',
        'print_qty' => 'integer'
    ];

    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';

    protected $reversedSort = true;
    protected $sort = [
        'date' => 'process_date',
        'product' => 'product_name'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.product_qty, c.order_no, b.box_qty,
                   d.name as product_name, b.process_date, b.customer_name, ifnull(e.name,'') as type
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date, cm.name as customer_name
                            from qr_code aa
                            inner join warehouse w
                            on aa.id = w.qr_id
                            inner join customer_master cm
                            on w.from_id = cm.id
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_warehousing}
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
        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id
                            from qr_code aa
                            inner join warehouse w
                            on aa.id = w.qr_id
                            inner join customer_master cm
                            on w.from_id = cm.id
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where bb.process_status = {$process_warehousing}
                            and aa.dept_id = {$dept_id}
                            {$this->searchAsset($params)}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
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
        $process_warehousing = Code::$PROCESS_WAREHOUSING;

        $sql = "select
                    cs.process_date, o.order_no, po.id,
                       MIN(cs.process_date) as start_date, MAX(cs.process_date) as end_date,
                    pm.name as product_name, o.jaje_code, sum(qc.qty) as qty,
                    po.code as process_code
                from qr_code qc
                     inner join process_order po
                        on qc.process_order_id = po.id
                     inner join `order` o
                        on qc.order_id = o.id
                     inner join change_stts cs
                        on qc.id = cs.qr_id
                     inner join product_master pm
                        on qc.product_id = pm.id
                where po.id = {$id} and cs.process_status = {$process_warehousing}
                ";

        return new Response(200, $this->fetch($sql));
    }

    /**
     * @param array $data
     * 외주 입고 QR코드를 생성한다.
     */
    public function createOutsourcingQrCode (array $data = [])
    {
        $this->validate($data, $this->createOutSourcingRequired);
        $this->isAvailableUser();

        $process_warehousing = Code::$PROCESS_WAREHOUSING;

        $print_result = [];
        try {
            $this->db->beginTransaction();

            for ($i = 0; $i < $data['print_qty']; $i++) {
                try {
                    $sql = "insert into qr_code set
                            order_id = {$data['order_id']},
                            process_order_id = {$data['process_order_id']},
                            product_id = {$data['product_id']},
                            dept_id = {$this->token['dept_id']},
                            qty = {$data['qty']},
                            from_id = {$data['from_id']},
                            process_stts = {$process_warehousing},
                            created_id = {$this->token['id']},
                            created_at = '{$data['process_date']}'
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $qr_id = $this->db->lastInsertId();

                    $sql = "insert into box set
                            qr_id = {$qr_id},
                            process_status = {$process_warehousing},
                            created_id = {$this->token['id']},
                            created_at = SYSDATE()
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();

                    $sql = "select o.order_no, a.qty, a.created_at, c.name as product_name, 
                                    cm.name as customer_name
                                from qr_code a
                                inner join `order` o
                                on a.order_id = o.id
                                inner join product_master c
                                on a.product_id = c.id
                                inner join customer_master cm
                                on a.from_id = cm.id
                            where a.id = {$qr_id}";

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

        return new Response(200, $print_result, '');
    }

    /**
     * @param array $data
     * Qr 코드를 입고 처리한다.
     */
    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);
        $this->isAvailableUser();
        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $group_id = $this->generateRandomString();

        try {
            $this->db->beginTransaction();

            foreach ($data['qr_ids'] as $qr_id) {

                $sql = "select id from box where qr_id = {$qr_id}";
                $box_id = $this->fetch($sql)[0]['id'];

                /** Lot일 경우 pass */
                if (!$box_id) {
                    continue;
                }

                $sqls = [
                    "insert into `warehouse` set
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
                     dept_id = {$this->token['dept_id']},
                     process_stts = {$process_warehousing},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$qr_id}
                    ",
                    "insert into change_stts set
                     qr_id = {$qr_id},
                     dept_id = {$this->token['dept_id']},
                     process_status = {$process_warehousing},
                     process_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update box set
                     process_status = {$process_warehousing},
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

        return new Response(200, [],'입고 되었습니다.');
    }

    /**
     * @param null $id
     * @param array $data
     * 스캔한 QR 코드의 결과를 return 한다.
     */
    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();

        $lot_id = $this->isLotQrCode($id);
        if ($lot_id) {
            return $this->printLotList($lot_id);
        }

        $box_id = $this->isBoxQrCode($id);
        if ($box_id) {
            $this->isDeptProcess($id);
            return $this->printBox($box_id);
        }

        return new Response(403, [],'lot 혹은 box를 스캔해주세요.');
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

    protected function printBox($box_id = null)
    {
        $sql = "update box set lot_id = null where id = {$box_id}";
        $this->fetch($sql);

        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, 
                        pm.id as product_id, qc.process_stts
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where a.id = {$box_id}";

        $result = $this->fetch($sql)[0];

        return new Response(200, $result, '');
    }

    protected function printLotList($lot_id = null)
    {
        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, 
                        pm.id as product_id, qc.process_stts
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where a.lot_id = {$lot_id}";

        return new Response(200, $this->fetch($sql), '');
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

    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }
}
