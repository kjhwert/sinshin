<?php

class AutomobileMaster extends Model
{
    protected $table = 'automobile_master';
    protected $searchableText = 'name';
    protected $fields = ['id','customer','supplier','customer_code','supply_code','car_code',
                        'name','brand_code','product_price','plating_price','supply_price','note1','note2'];
    protected $paging = true;

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
}
