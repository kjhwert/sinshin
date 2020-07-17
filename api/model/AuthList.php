<?php


class AuthList extends Model
{
    protected $table = 'auth_list';
    protected $fields = ['id', 'auth_id', 'auth_group_id'];
    protected $paging = true;

    public function show($id = null)
    {
        $sql = "select a.id, a.menu, a.function, IF(b.auth_id iS NULL,'NO','YES') as has 
                from auth_master a
                    left join (select a.auth_group_id, a.auth_id from auth_list a
                        inner join auth_group b
                        on a.auth_group_id = b.id
                        where a.stts = 'ACT' and b.stts = 'ACT' and b.id = {$id}) as b
                    on a.id = b.auth_id
                where a.stts = 'ACT'";

        return new Response(200, $this->fetch($sql), '');
    }

    public function update($id = null, array $data = [])
    {
        $sql = "delete from {$this->table} where auth_group_id = {$data['auth_group_id']}";
        $this->fetch($sql);

        $sql = "insert into {$this->table} (auth_id, auth_group_id, stts, created_id, created_at) values ";

        $authMaster = explode(',',$data['auth_master_id']);

        foreach ($authMaster as $master) {
            if ($master) {
                $sql .= "({$master},{$data['auth_group_id']},'ACT',{$this->token['id']}, SYSDATE()),";
            }
        }

        $sql = rtrim($sql, ",");

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }
}
