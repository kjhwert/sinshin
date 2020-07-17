<?php

class UserLog extends Model
{
    protected $table = 'user_log';
    protected $fields = ['id','path','created_at'];
    protected $paging = true;
    protected $createRequired = [
        'path' => 'string'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select b.user_id, b.name, a.path, a.created_at, @rownum:= @rownum+1 AS RNUM from {$this->table} a
                inner join user b
                on a.created_id = b.id,
                (SELECT @rownum:= 0) AS R
                where a.stts = 'ACT' and b.stts = 'ACT'
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }
}
