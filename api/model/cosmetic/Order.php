<?php

class Order extends Model
{
    protected $table = 'order';

    public function index(array $params = [])
    {
        if (!$params['material_id']) {
            return new Response(403, [], 'material_id 값이 존재하지 않습니다.');
        }

        $sql = "select id, order_no from `order` where material_id = {$params['material_id']}";
        return new Response(200, $this->fetch($sql));
    }
}
