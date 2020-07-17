<?php


class UserAuth extends Model
{
    protected $table = 'user_auth';
    protected $paging = false;

    public function index(array $params = [])
    {
        $sql = "select a.id, a.name, 'NO' as has from auth_group a where a.stts = 'ACT'";
        return new Response(200, $this->fetch($sql),"");
    }

    public function show($id = null)
    {
        $sql = "select a.id, a.name, IF(b.id iS NULL,'NO','YES') as has from auth_group a
                    left join (
                        select * from user_auth where user_uid = {$id} and stts = 'ACT'
                        ) b
                    on a.id = b.auth_group_id
                    where a.stts = 'ACT'";

        return new Response(200, $this->fetch($sql),"");
    }

    public function update($id = null, array $data = [])
    {
        $sql = "delete from {$this->table} where user_uid = {$id}";
        $this->fetch($sql);

        $sql = "insert into {$this->table} (user_uid, auth_group_id, stts, created_id, created_at) values ";

        $authGroup = explode(',',$data['auth_group_id']);

        foreach ($authGroup as $group) {
            if ($group) {
                $sql .= "({$id},{$group},'ACT',{$this->token['id']}, SYSDATE()),";
            }
        }

        $sql = rtrim($sql, ",");

        return new Response(200, $this->fetch($sql),"등록되었습니다.");
    }

    public function create(array $data = [])
    {

    }

    public function destroy($id = null)
    {

    }
}
