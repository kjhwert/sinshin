<?php

class QrStart extends Model
{
    protected $table = 'process_order';
    protected $injectionCreateRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',
        'qty' => 'integer',
        'print_qty' => 'integer',
        'asset_id' => 'integer',
        'material_id' => 'integer',
        'lot_no' => 'string'
    ];
    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $searchableAsset = 'aa.asset_id';
    protected $reversedSort = true;
    protected $sort = [
        'date' => 'process_date',
        'asset' => 'asset_name'
    ];

    protected $dept = null;
    protected $qrMasterId = 1;

    protected $updateRequired = [];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.product_qty, c.order_no, c.jaje_code, b.display_name,
                   d.name as product_name, b.material_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, sum(aa.qty) as product_qty, 
                                bb.process_date, cc.name as asset_name, cc.display_name, mm.name as material_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            inner join material_master mm
                            on aa.material_id = mm.id
                            where bb.process_status = {$process_start}
                            and bb.dept_id = {$dept_id}
                            {$this->searchAsset($params['params'])}
                            and aa.stts = 'ACT' and bb.stts = 'ACT' and mm.stts = 'ACT'
                            group by aa.process_order_id
                            ) b
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
        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where bb.process_status = {$process_start}
                            and aa.dept_id = {$dept_id}
                            and bb.dept_id = {$dept_id}
                            {$this->searchAsset($params)}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_start = Code::$PROCESS_START;

        $dept_id = $this->getDeptId();

        $sql = "select
                    cs.process_date, a.name as asset_name, o.order_no, po.id, a.id as asset_id, o.id as order_id,
                       MIN(cs.process_date) as start_date, MAX(cs.process_date) as end_date, mm.id as material_master,
                    pm.name as product_name, pm.id as product_id, mm.name as material_name, o.jaje_code, sum(qc.qty) as qty,
                    a.display_name, a.asset_no, po.code as process_code
                from qr_code qc
                     inner join process_order po
                        on qc.process_order_id = po.id
                     inner join `order` o
                        on qc.order_id = o.id
                     inner join change_stts cs
                        on qc.id = cs.qr_id
                     inner join asset a
                        on qc.asset_id = a.id
                     inner join product_master pm
                        on qc.product_id = pm.id
                     inner join material_master mm
                        on qc.material_id = mm.id
                where po.id = {$id} 
                and qc.process_stts = {$process_start} 
                and cs.process_status = {$process_start}
                and cs.dept_id = {$dept_id}
                ";

        return new Response(200, $this->fetch($sql));
    }

    /**
     * @param array $data
     * @return Response|void
     */
    public function create(array $data = [])
    {
        $this->hasUniqueType($data);
        $this->isAvailableUser();

        if (array_key_exists('product_id',$data)) {
            return $this->createProductQrCode($data);
        }

        if (array_key_exists('material_id',$data)) {
            return $this->createMaterialQrCode($data);
        }

        if (array_key_exists('color_id',$data)) {
            return $this->createColorQrCode($data);
        }
    }

    public function qrShow ($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name, mm.name as material_name, mm.id as material_id,
                        a.name as asset_name, qr.id, qr.process_stts, qr.qty, mm.unit, 1 as box_qty, a.asset_no
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

        return new Response(200, $result);
    }

    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();
        $this->isDeptProcess($id);

        $process_start = Code::$PROCESS_START;

        $sql = "select material_id, process_stts from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];

        if ($result['process_stts'] === $process_start) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        $material_id = $result['material_id'];

        $sql = "select log.change_qty, log.remain_qty, mm.qty
                from material_master mm
                inner join material_stock_log log
                where mm.id = {$material_id} order by log.created_at desc limit 1
                ";

        $result = $this->fetch($sql)[0];

        $remain_qty = (int)$result['remain_qty'];

        if ($remain_qty < 0) {
            return new Response(403, [], '재고가 부족합니다.');
        }

        $change_qty = -(int)$result['qty'];
        $remain_qty += $change_qty;

        $sqls = [
            "update qr_code set
                process_stts = {$process_start},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
                ",
            "insert into change_stts set
                qr_id = {$id},
                dept_id = {$this->token['dept_id']},
                process_status = {$process_start},
                process_date = SYSDATE(),
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
            "insert into material_stock_log set 
                change_qty = {$change_qty},
                remain_qty = {$remain_qty},
                material_id = {$material_id},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
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

    protected function hasUniqueType (array $data = [])
    {
        if (
            !array_key_exists('product_id',$data) &&
            !array_key_exists('material_id',$data) &&
            !array_key_exists('color_id', $data)
        ) {
            return new Response(403, [],'제품, 원자재 혹은 배합원료에 대한 정보가 필요합니다.');
        }

        $i = 0;
        foreach ($data as $key=>$value) {
            if ($i > 1) {
                return new Response(403, [], '한가지 유형만 지정할 수 있습니다.');
            }

            if($key === 'product_id' || $key === 'material_id' || $key === 'color_id') {
                $i++;
            }
        }
    }

    protected function createProductQrCode (array $data = [])
    {
        /**
         *  제품 재고가 있는지 확인.
         */

        $data = $this->validate($data, $this->injectionCreateRequired);

        $print_qty = $data['print_qty'];
        unset($data['print_qty']);
    }

    protected function createMaterialQrCode (array $data = [])
    {
        $data = $this->validate($data, $this->injectionCreateRequired);

        $dept_id = $this->getDeptId();
        if ($this->token['dept_id'] !== $dept_id) {
            return new Response(403, [], '해당 부서 직원이 아닙니다.');
        }

        if ($data['print_qty'] > 50) {
            return new Response(403, [], '출력 수량이 50장을 초과할 수 없습니다.');
        }

        $print_qty = $data['print_qty'];
        unset($data['print_qty']);

        $sql = "select pm.id 
                    from process_order po
                    inner join product_master pm
                    on po.product_code = pm.code
                    where po.id = {$data['process_order_id']}";

        $product_id = $this->fetch($sql)[0]['id'];

        $sql = "select qty, id from material_master where id = {$data['material_id']}";
        $result = $this->fetch($sql)[0];

        $qty = $result['qty'];
        $material_id = $result['id'];

        $sql = "select remain_qty 
                from material_stock_log 
                where material_id = {$material_id} 
                order by created_at desc limit 1";

        $remain_qty = $this->fetch($sql)[0]['remain_qty'];
        $necessary_qty = $print_qty;

        $remain_qty = (int)($remain_qty / $qty);

        if ($necessary_qty > $remain_qty) {
            return new Response(403, [], "원자재 재고량이 부족합니다. 출력 수량 : {$necessary_qty} 재고량 : {$remain_qty}");
        }

        /** @var  $sql
         *  Lot_no를 등록한다.
         */
        $sql = "update {$this->table} set lot_no = '{$data['lot_no']}' 
                where id = {$data['process_order_id']}";

        $this->fetch($sql);
        unset($data['lot_no']);

        $print_result = [];
//        try {
//            $this->db->beginTransaction();

            for ($i = 0; $i < $print_qty; $i++) {
//                try {
                    $sql = "insert into qr_code set
                            {$this->dataToString($data)},
                            product_id = {$product_id},
                            dept_id = {$this->token['dept_id']},
                            qr_master_id = {$this->qrMasterId},
                            created_id = {$this->token['id']},
                            created_at = SYSDATE()
                            ";

//                    $stmt = $this->db->prepare($sql);
//                    $stmt->execute();
                    $this->fetch($sql);
                    $qr_id = $this->db->lastInsertId();

                    $sql = "select o.order_no, b.name as material_name, b.code as jaje_code, d.id as asset_id,
                                   a.qty, b.unit, c.name as product_name, d.asset_no
                                from qr_code a
                                inner join `order` o
                                on a.order_id = o.id
                                inner join product_master c
                                on a.product_id = c.id
                                inner join material_master b
                                on a.material_id = b.id
                                inner join asset d
                                on a.asset_id = d.id
                            where a.id = {$qr_id}";

                    $result = $this->fetch($sql)[0];
                    $result['qr_id'] = (int)$qr_id;

                    array_push($print_result, $result);

//                } catch (Exception $e) {
//                    throw $e;
//                }
            }

//            $this->db->commit();

//        } catch (Exception $e) {
//            $this->db->rollBack();
//            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
//        }

        return new Response(200, $print_result, '');
    }

    protected function createColorQrCode (array $data = [])
    {
        /**
         *  배합원료 재고가 있는지 확인.
         */

        $data = $this->validate($data, $this->injectionCreateRequired);

        $print_qty = $data['print_qty'];
        unset($data['print_qty']);
    }
}
