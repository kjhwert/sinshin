<?php


class Dept extends Model
{
    protected $fields = ['id','name'];
    protected $table = 'dept';
    protected $paging = false;

    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where group_id = {$params['group_id']} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
