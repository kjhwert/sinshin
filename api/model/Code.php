<?php


class Code extends Model
{
    protected $fields = ['id','name', 'name_en'];
    protected $table = 'code';

    /**
     * @param array $params
     * @return Response
     */
    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where group_id = {$params['group_id']} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
