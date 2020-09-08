<?php

class ProductMaster extends Model
{
    protected $table = 'product_master';

    /**
     *  ERP의 product 정보를 가져온다.
     */
    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from product_master order by id desc limit 1";
        $id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Product where id > {$id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select count(id) as cnt from product_master where id = {$result['id']}";
            $has = $this->fetch($sql, $mes)[0]['cnt'];

            if ($has > 0) {
                continue;
            }

            $result['name'] = str_replace("'", "", $result['name']);

            $sql = "insert into product_master set
                        id = {$result['id']},
                        code = '{$result['code']}',
                        name = '{$result['name']}',
                        model = '{$result['modelName']}',
                        part_name = '{$result['partName']}',
                        part_code = '{$result['partProductCode']}',
                        process_type = '{$result['processType']}',
                        data_sync = 'ERP',
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";

            $material = "select resourceId from MES_Process where resourceId is not null and productCode = '{$result['code']}'";
            $material_id = $this->fetch($material, $erp)[0]['resourceId'];

            if ($material_id) {
                $sql .= ", material_id = {$material_id}";
            }

            $this->fetch($sql, $mes);
        }

        return new Response(200, [], '등록 되었습니다.');
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
