<?php

class Defect extends Model
{
    protected $table = 'defect';
    protected $paging = false;

    static $TRUST_LOSS = 23;
    static $SIZE_LOSS = 24;

    public function index (array $params = [])
    {
        if ($params['type'] === 'car') {
            $sql = "select a.id, a.name, a.name_en, b.name as group_name, a.group_id 
                    from {$this->table} a
                    inner join defect_group b
                    on a.group_id = b.id
                    where a.stts = 'ACT' and (a.group_id = 1 or a.group_id = 2)";

            return new Response(200, $this->fetch($sql), '');
        }

        if (!$params['group_id']) {
            return new Response(403, [], 'group_id 값이 존재하지 않습니다.');
        }

        $sql = "select a.id, a.name, a.name_en, b.name as group_name, a.group_id 
                    from {$this->table} a
                    inner join defect_group b
                    on a.group_id = b.id
                    where a.group_id = {$params['group_id']} and a.stts = 'ACT'";

        return new Response(200, $this->fetch($sql), '');
    }
}
