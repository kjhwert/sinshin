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
        'material_id' => 'integer'
    ];
    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';
    protected $reversedSort = true;
    protected $sort = [
        'process_date' => 'process_date',
        'asset_name' => 'asset_name'
    ];

    protected $updateRequired = [

    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_start = Code::$PROCESS_START;
        $injection = AuthGroup::$INJECTION;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, sum(b.qty) as product_qty, c.order_no, c.jaje_code,
                   d.name as product_name, e.name as material_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_start} and bb.process_status = {$process_start}
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
        $process_start = Code::$PROCESS_START;
        $injection = AuthGroup::$INJECTION;

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_start} and bb.process_status = {$process_start} 
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
        $process_start = Code::$PROCESS_START;

        $sql = "select
                    cs.process_date, a.name as asset_name, o.order_no, po.id, 
                       MIN(cs.process_date) as start_date, MAX(cs.process_date) as end_date,
                    pm.name as product_name, mm.name as material_name, o.jaje_code, sum(qc.qty) as qty
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
                        on pm.material_id = mm.id
                where po.id = {$id} and qc.process_stts = {$process_start} and cs.process_status = {$process_start}
                ";

        return new Response(200, $this->fetch($sql));
    }

    /**
     * @param array $data
     * @return Response|void
     */
    public function create(array $data = [])
    {
        /**
         *  material, product, color 에 대한 정보가 필요하네.
         *  order_id *
         *  process_order_id *
         *  production_plan_id - 얘는 어떻게하지? 얘는 ERP 데이터 받아봐야 알라나...
         *  process_id (공정유형 : 사상, 유업 등) - 선택
         *  asset_id - 선택(사출은 필수)
         *  from_id - 입고처 id (거래처 검색해서 해야하나?)
         *  qty *
         */

        $this->hasUniqueType($data);

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

    public function update($id = null, array $data = [])
    {
        $process_start = Code::$PROCESS_START;

        $sql = "select qty, material_id from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];

        $qty = (int)$result['qty'];
        $material_id = $result['material_id'];

        $sql = "select change_qty, remain_qty from material_stock_log 
                where material_id = {$material_id} order by created_at desc limit 1";

        $result = $this->fetch($sql)[0];

        $remain_qty = (int)$result['remain_qty'];

        if ($remain_qty < $qty) {
            return new Response(403, [], '재고가 부족합니다.');
        }

        $change_qty = -$qty;
        $remain_qty += $change_qty;

        $sqls = [
            "update {$this->table} set
                process_stts = {$process_start},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
                ",
            "insert into change_stts set
                qr_id = {$id},
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
//            "insert into box set
//                qr_id = {$id},
//                process_start_at = SYSDATE(),
//                process_status = {$process_start},
//                created_id = {$this->token['id']},
//                created_at = SYSDATE()
//            "
        ];

        return $this->setTransaction($sqls);
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

        $print_qty = $data['print_qty'];
        unset($data['print_qty']);

        $sql = "select remain_qty 
                from material_stock_log 
                where material_id = {$data['material_id']} 
                order by created_at desc limit 1";

        $remain_qty = $this->fetch($sql)[0]['remain_qty'];
        $necessary_qty = $data['qty'] * $print_qty;

        if ($necessary_qty > $remain_qty) {
            return new Response(403, [], "원자재 재고량이 부족합니다. 출력 수량 : {$necessary_qty} 재고량 : {$remain_qty}");
        }

        $sql = "select id from product_master where material_id = {$data['material_id']}";
        $product_id = $this->fetch($sql)[0]['id'];

        $injection = AuthGroup::$INJECTION;

        $print_result = [];
        try {
            $this->db->beginTransaction();

            for ($i = 0; $i < $print_qty; $i++) {
                try {
                    $sql = "insert into {$this->table} set
                            {$this->dataToString($data)},
                            product_id = {$product_id},
                            auth_group_id = {$injection},
                            dept_id = {$this->token['dept_id']},
                            created_id = {$this->token['id']},
                            created_at = SYSDATE()
                            ";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $qr_id = $this->db->lastInsertId();

                    $sql = "select a.order_id, b.name as material_name, b.code,
                                   a.qty, b.unit, c.name as product_name, d.name as asset_name
                                from qr_code a
                                inner join material_master b
                                on a.material_id = b.id
                                left join product_master c
                                on b.id = c.material_id
                                inner join asset d
                                on a.asset_id = d.id
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
