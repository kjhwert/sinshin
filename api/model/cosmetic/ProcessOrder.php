<?php

class ProcessOrder extends Model
{
    protected $table = 'process_order';

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        if (!$params['order_id']) {
            return new Response(403, [], '수주 정보가 없습니다.');
        }

        $sql = "select id, code
                from process_order 
                where order_id = {$params['order_id']} 
                and code is not null
                and asset_id is not null
                ";
        return new Response(200, $this->fetch($sql, $this->db));
    }

    /**
     * @param null $id = order id
     * @param array $data
     * @return Response
     */
    public function show($id = null, array $data = [])
    {
        $sql = "select po.id, mm.code as jaje_code, a.asset_no, a.id as asset_id,
                        pm.name as product_name, mm.name as material_name 
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

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from process_order order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_ProcessOrder where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select count(id) as cnt from process_order where id = {$result['id']}";
            $has = $this->fetch($sql, $mes)[0]['cnt'];

            if ($has > 0) {
                continue;
            }

            $sql = "insert into process_order set 
                        id = {$result['id']},
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

        $max_id = (int)$max_id - 5000;

        $sql = "select * from MES_ProcessOrder where id >= {$max_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {
            $sql = "update process_order set
                        updated_id = 1,
                        updated_at = SYSDATE()
                    ";

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

            $sql .= " where id = {$result['id']}";

            $this->fetch($sql, $mes);
        }

        return new Response(200, [], '갱신 되었습니다.');
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
