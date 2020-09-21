<?php

class CustomerMaster extends Model
{
    protected $table = 'customer_master';
    protected $searchableText = 'name';

    public static $PAINTING = 32; // 도장
    public static $INJECTION = 30; // 사출
    public static $ASSEMBLE = 184; // 조립

    public function index(array $params = [])
    {
        $sql = "select id, name from customer_master 
                where 1=1 {$this->searchText($params)} {$this->searchType($params)}";

        return new Response(200, $this->fetch($sql));
    }

    protected function searchType (array $params = [])
    {
        $type = $params['type'];

        if ($type) {
            return "and type = '{$type}'";
        } else {
            return "";
        }
    }
}
