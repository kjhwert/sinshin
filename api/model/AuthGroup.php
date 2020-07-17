<?php


class AuthGroup extends Model
{
    protected $table = 'auth_group';
    protected $fields = ['id','name'];
    protected $paging = true;

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = (int)$params["perPage"];
        $page = ((int)$params["page"] * $perPage);

        $sql = "select id, name from auth_group 
                    where stts = 'ACT'
                order by {$this->primaryKey} desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

//    public function show($id = null)
//    {
//        $sql = "select a.id, a.menu, a.function, IF(b.auth_id iS NULL,'NO','YES') as has
//                from auth_master a
//                    left join (select a.auth_group_id, a.auth_id from auth_list a
//                        inner join auth_group b
//                        on a.auth_group_id = b.id
//                        where a.stts = 'ACT' and b.stts = 'ACT' and b.id = {$id}) as b
//                    on a.id = b.auth_id
//                where a.stts = 'ACT'";
//
//        return new Response(200, $this->fetch($sql), '');
//    }

    public function create(array $data = [])
    {
        $sql = "select count(id) as cnt from auth_group where stts = 'ACT' and name = '{$data['name']}'";
        $cnt = $this->fetch($sql)[0]['cnt'];

        if ($cnt > 0) {
            return new Response(400, [], '이미 등록되어있는 그룹명입니다.');
        }

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }
}
