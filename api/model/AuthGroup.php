<?php


class AuthGroup extends Model
{
    protected $table = 'auth_group';
    protected $fields = ['id','name'];
    protected $paging = false;

    public function index(array $params = [])
    {
        $sql = "select id, name from auth_group where stts = 'ACT'";

        return new Response(200, $this->fetch($sql), '');
    }

    public function show($id = null)
    {
        $sql = "select b.id as auth_list_id , b.auth_master_id, b.menu, b.function from auth_group as a
                left join (
                    select aa.id, aa.auth_group_id, bb.id as auth_master_id, bb.menu, bb.function from auth_list as aa
                        left join auth_master as bb
                        on aa.auth_id = bb.id
                    where aa.stts = 'ACT' and bb.stts = 'ACT'
                ) as b
                on a.id = b.auth_group_id
                where a.stts = 'ACT' and a.id = {$id}";

        return new Response(200, $this->fetch($sql), '');
    }
}
