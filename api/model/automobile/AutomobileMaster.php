<?php

class AutomobileMaster extends Model
{
    protected $table = 'automobile_master';
    protected $searchableText = 'customer_code';
    protected $fields = ['id','customer','supplier','customer_code','supply_code','car_code',
                        'name','brand_code','product_price','plating_price','supply_price','note1','note2'];
    protected $paging = true;
    protected $createRequired = [
        'customer' => 'string',
        'supplier' => 'string',
        'customer_code' => 'string',
        'supply_code' => 'string',
        'car_code' => 'string',
        'name' => 'string',
        'brand_code' => 'string',
        'product_price' => 'integer',
        'plating_price' => 'integer',
        'supply_price' => 'integer'
    ];

    public function index (array $params = [])
    {
        if (!$params['paging'] || $params['paging'] === false) {
            return $this->getPagingQuery($params);
        }

        $sql = "select {$this->getFields()}, @rownum:= @rownum+1 AS RNUM 
                from {$this->table},
                (SELECT @rownum:= 0) AS R
                where stts = 'ACT' {$this->searchText($params)}
                order by RNUM desc";

        return new Response(200, $this->fetch($sql), '');
    }

    public function destroy ($id = null)
    {
        $this->validateUser();
        $sql = "update {$this->table} set stts = 'DELETE' where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '삭제되었습니다.');
    }

    protected function validateUser()
    {
        $automobile = AuthGroup::$AUTOMOBILE;
        $admin = AuthGroup::$ADMIN;

        $sql = "select auth_group_id from user_auth where user_uid = {$this->token['id']}";
        $auth = $this->fetch($sql)[0]['auth_group_id'];

        if ($auth !== $automobile && $auth !== $admin) {
            return new Response(403, [], '권한이 없습니다.');
        }
    }
}
