<?php

class MaterialMaster extends Model{
    protected $table = 'material_master';
    protected $fields = ['id','name','type','unit','supplier','qty','code','created_at'];
    protected $searchableText = 'code';

    public function index(array $params = [])
    {
        if (!$params['type']) {
            return new Response(403, [],'type이 존재하지 않습니다.');
        }

        $sql = "select {$this->getFields()}, @rownum:= @rownum+1 AS RNUM 
                from {$this->table},
                (SELECT @rownum:= 0) AS R
                where stts = 'ACT' and type = '{$params['type']}' {$this->searchText($params)}
                order by RNUM desc";

        return new Response(200, $this->fetch($sql), '');
    }
}
