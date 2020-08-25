<?php

class ProcessOrder extends Model
{
    protected $table = 'process_order';

    public function index(array $params = [])
    {
        if (!$params['material_id']) {
            return new Response(403, [], '원자재 정보가 없습니다.');
        }

        if (!$params['type']) {
            return new Response(403, [], '타입을 선택해주세요.');
        }

        $sql = "select po.id
                from process_order po
                inner join material_master mm
                on po.material_id = mm.id
                where po.order_id = {$params['order_id']} and po.material_id = {$params['material_id']}
                and mm.type = '{$params['type']}'
                ";
        return new Response(200, $this->fetch($sql));
    }

    /**
     * @param null $id = order id
     * @param array $data
     * @return Response
     */
    public function show($id = null, array $data = [])
    {
        $sql = "select po.id, mm.code as jaje_code, 
                        pm.name as product_name, mm.name as material_name 
                from process_order po
                inner join product_master pm
                on po.product_id = pm.id
                inner join material_master mm
                on po.material_id = mm.id
                where po.id = {$id}
                ";
        
        return new Response(200, $this->fetch($sql));
    }
}
