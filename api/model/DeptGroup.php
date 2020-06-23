<?php


class DeptGroup extends Model
{
    protected $fields = ['id','name'];
    protected $table = 'dept_group';

    /**
     * @param array $params
     * @return Response
     */
    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
