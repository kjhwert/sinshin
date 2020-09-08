<?php

class Order extends Model
{
    protected $table = 'order';

    public function index(array $params = [])
    {
        $timestamp = strtotime("-1 months");
        $date = date("Y-m-d H:i:s", $timestamp);

        $sql = "select o.id, o.order_no from `order` o
                    inner join process_order po
                    on o.id = po.order_id 
                    where o.order_date > '{$date}'
                    and po.code is not null
                    and po.asset_id is not null
                    group by o.id";

        return new Response(200, $this->fetch($sql, $this->db));
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
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";

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
