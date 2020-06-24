<?php


class Dept extends Model
{
    protected $fields = ['id','name'];
    protected $table = 'dept';
    protected $paging = false;

    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where {$this->search($params)} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
