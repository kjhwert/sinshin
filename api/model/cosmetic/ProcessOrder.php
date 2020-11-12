<?php

class ProcessOrder extends Model
{
    protected $table = 'process_order';

    protected $injectionProcessCode = "'M'";
    protected $paintingProcessCode = "'D', 'D2', 'C', 'R', 'Q', 'C1', 'E'";
    protected $assembleProcessCode = "'F', 'F1', 'J', 'SS', 'G', 'H'";

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $this->token = $this->tokenValidation();

        if (!$params['order_id']) {
            return new Response(403, [], '수주 정보가 없습니다.');
        }

        $sql = "select po.id, po.code, pm.name as product_name, pm.id as product_id, pm.code as product_code
                from process_order po
                inner join product_master pm
                on po.product_code = pm.code
                where po.order_id = {$params['order_id']}
                and po.process_type in ({$this->getDeptProcessType()}) 
                and po.code is not null
                ";

        return new Response(200, $this->fetch($sql, $this->db));
    }

    protected function getDeptProcessType () {
        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        switch ($this->token['dept_id']) {
            case $injection:
                return $this->injectionProcessCode;
            case $painting:
                return $this->paintingProcessCode;
            case $assemble:
                return $this->assembleProcessCode;
            default:
                return new Response(403, [], '출력 권한이 없습니다.');
        }
    }

    /**
     * @param null $id = order id
     * @param array $data
     * @return Response
     */
    public function show($id = null, array $data = [])
    {
        $this->token = $this->tokenValidation();

        $sql = "select po.id, mm.code as jaje_code, a.asset_no, a.id as asset_id, pm.id as product_id,
                        pm.name as product_name, mm.name as material_name, mm.id as material_id 
                from process_order po
                inner join product_master pm
                on po.product_code = pm.code
                left join material_master mm
                on pm.material_id = mm.id
                left join asset a
                on po.asset_id = a.id
                where po.id = {$id}
                ";

        return new Response(200, $this->fetch($sql, $this->db));
    }

    public function assembleShow($id = null)
    {

    }

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from process_order order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'] - 5000;

        $sql = "select * from MES_ProcessOrder where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select count(id) as cnt from process_order where id = '{$result['id']}'";
            $has = $this->fetch($sql, $mes)[0]['cnt'];

            if ($has > 0) {
                continue;
            }

            $sql = "select * from MES_Process where productCode = '{$result['productCode']}'";
            $process = $this->fetch($sql, $erp)[0];

            $sql = "insert into process_order set 
                        id = {$result['id']},
                        code = '{$result['code']}',
                        order_id = {$result['sujuId']},
                        product_code = '{$result['productCode']}',
                        jaje_code = '{$result['jajeCode']}', 
                        process_type = '{$result['processType']}',
                        qty = {$result['quantity']},
                        customer_id = {$result['companyId']},
                        order_status = '{$result['status']}',
                        created_id = 1,
                        created_at = SYSDATE()
                    ";

            if($process['moldId']) {
                $sql .= ", mold_id = {$process['moldId']}";
            }

            if ($result['priority']) {
                $sql .= ", ord = {$result['priority']}";
            }

            if ($result['orderDate']) {
                $sql .= ", order_date = '{$result['orderDate']}'";
            }

            if ($result['requestDeliveryDate']) {
                $sql .= ", request_date = '{$result['requestDeliveryDate']}'";
            }

            if ($result['machineId']) {
                $sql .= ", asset_id = {$result['machineId']}";
            }

            $this->fetch($sql, $mes);
        }
    }

    public function update($id = null, array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from process_order order by id desc limit 1";
        $max_id = $this->fetch($sql, $mes)[0]['id'];

        $max_id = (int)$max_id - 10000;

        $sql = "select * from MES_ProcessOrder where id >= {$max_id}";
        $erp_results = $this->fetch($sql, $erp);

        $count = 0;

        foreach ($erp_results as $result) {
            $sql = "select updated_at, mold_id from process_order where id = {$result['id']}";
            $updated_at = $this->fetch($sql, $mes)[0]['updated_at'];

            if (!$result['updateDate'] || $updated_at === $result['updateDate']) {
                continue;
            }

            $sql = "select * from MES_Process where productCode = '{$result['productCode']}'";
            $process = $this->fetch($sql, $erp)[0];

            $sql = "update process_order set
                        updated_id = 1
                    ";

            if ($result['updateDate']) {
                $sql .= ", updated_at = '{$result['updateDate']}'";
            }

            if ($process['moldId']) {
                $sql .= ", mold_id = {$process['moldId']}";
            }

            if ($result['code']) {
                $sql .= ", code = '{$result['code']}'";
            }

            if ($result['priority']) {
                $sql .= ", ord = {$result['priority']}";
            }

            if ($result['orderDate']) {
                $sql .= ", order_date = '{$result['orderDate']}'";
            }

            if ($result['requestDeliveryDate']) {
                $sql .= ", request_date = '{$result['requestDeliveryDate']}'";
            }

            if ($result['machineId']) {
                $sql .= ", asset_id = {$result['machineId']}";
            }

            if ($result['workStatus']) {
                $sql .= ", order_status = '{$result['workStatus']}'";
            }

            $sql .= " where id = {$result['id']}";

            $count++;
            $this->fetch($sql, $mes);
        }

        return new Response(200, [], "{$count}개의 데이터가 갱신되었습니다.");
    }

    protected function fetch ($sql = null, $db = null)
    {
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return new Response(403, [], $e.message);
        }
    }
}
