<?php

class DefectGroup extends Model
{
    protected $fields = ['a.id','a.name'];
    protected $table = 'defect_group';
    protected $paging = false;

    public static $INJECTION = 4;
    public static $PAINTING = 5;
    public static $ASSEMBLE = 6;

    public function index(array $params = [])
    {
        $sql = "select {$this->getFields()}, b.name as dept_name, @rownum:= @rownum+1 AS RNUM 
                from {$this->table} a
                left join dept_group b
                on a.dept_group_id = b.id,
                (SELECT @rownum:= 0) AS R
                where a.stts = 'ACT' and b.stts = 'ACT' 
                order by RNUM desc";

        return new Response(200, $this->fetch($sql), '');
    }
}
