<?php

class Order extends Model
{
    protected $table = 'order';
    protected $searchableText = 'o.order_no';

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $timestamp = strtotime("-1 months");
        $date = date("Y-m-d H:i:s", $timestamp);

        $sql = "select o.id, o.order_no from `order` o
                    inner join process_order po
                    on o.id = po.order_id 
                    where po.code is not null
                    {$this->searchText($params)}
                    group by o.id";

        return new Response(200, $this->fetch($sql, $this->db));
    }

    public function show($id = null)
    {
        $sql = "select o.id as order_id, o.order_no, pm.name as product_name, 
                    pm.id as product_id, pm.code as product_code
                    from `order` o 
                    inner join product_master pm
                    on o.product_code = pm.code
                    where o.id = {$id}
                ";

        return new Response(200, $this->fetch($sql, $this->db)[0]);
    }

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from `order` order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Suju where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select count(id) as cnt from `order` where id = {$result['id']}";
            $has = $this->fetch($sql, $mes)[0]['cnt'];

            if ($has > 0) {
                continue;
            }

            $sql = "insert into `order` set
                        id = {$result['id']},
                        order_no = '{$result['sujuNum']}',
                        customer_id = {$result['deliveryCompanyId']},
                        product_code = '{$result['productCode']}',
                        jaje_code = '{$result['jajeCode']}',
                        qty = {$result['quantity']},
                        order_date = '{$result['sujuDate']}',
                        request_date = '{$result['requestDeliveryDate']}',
                        supply_id = {$result['sujuCompanyId']},
                        order_type = '{$result['sujuType']}',
                        created_id = 1,
                        created_at = SYSDATE()
                    ";

            $this->fetch($sql, $mes);
        }
    }

    public function update($id = null, array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from `order` order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'] - 10000;

        $sql = "select * from MES_Suju where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select updated_at from `order` where id = {$result['id']}";
            $update_date = $this->fetch($sql, $mes)[0]['updated_at'];

            if (!$result['updateDate'] || $update_date === $result['updateDate']) {
                continue;
            }

            $sql = "update `order` set
                        updated_id = 1,
                        updated_at = '{$result['updateDate']}'
                    ";

            if ($result['sujuNum']) {
                $sql .= ", order_no = '{$result['sujuNum']}'";
            }

            if ($result['deliveryCompanyId']) {
                $sql .= ", customer_id = {$result['deliveryCompanyId']}";
            }

            if ($result['productCode']) {
               $sql .= ", product_code = '{$result['productCode']}'";
            }

            if ($result['jajeCode']) {
                $sql .= ", jaje_code = '{$result['jajeCode']}'";
            }

            if ($result['quantity']) {
                $sql .= ", qty = {$result['quantity']}";
            }

            if ($result['sujuDate']) {
                $sql .= ", order_date = '{$result['sujuDate']}'";
            }

            if ($result['requestDeliveryDate']) {
                $sql .= ", request_date = '{$result['requestDeliveryDate']}'";
            }

            if ($result['sujuCompanyId']) {
                $sql .= ", supply_id = {$result['sujuCompanyId']}";
            }

            if ($result['sujuType']) {
                $sql .= ", order_type = '{$result['sujuType']}'";
            }

            $sql .= " where id = {$result['id']}";

            $this->fetch($sql, $mes);
        }
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
