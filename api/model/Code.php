<?php


class Code extends Model
{
    protected $fields = ['id','name'];
    protected $table = 'code';

    /**
     * @param array $params
     * @return Response
     */
    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where {$this->search($params)} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
