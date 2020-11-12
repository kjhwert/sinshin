<?php


class User extends Model
{
    protected $table = 'user';
    protected $fields = ['user_id','name', 'dept_id', 'tel', 'email', 'position', 'duty'];
    protected $searchableText = 'a.name';
    protected $createRequired = [
        'user_id' => 'string',
        'user_pw'=> 'string',
        'name' => 'string',
        'dept_id' => 'integer',
        'position' => 'integer'
    ];

    protected $changePwRequired = [
        'pre_pw' => 'string',
        'change_pw' => 'string'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = (int)$params["perPage"];
        $page = ((int)$params["page"] * $perPage);

        $sql = "select 
                    a.id, user_id, a.name, a.dept_id, tel, email, position, ifnull(duty,'') as duty,
                    a.created_at, b.name as dept, c.name as position, @rownum := @rownum+1 AS RNUM, 
                    d.path, ifnull(d.created_at,'') as last_access
                         from user as a
                         left join dept as b
                                   on a.dept_id = b.id
                         left join code as c
                                   on a.position = c.id
                         left join (
                                select * from 
                                (select * from user_log 
                                    where stts = 'ACT' 
                                    order by created_at desc LIMIT 18446744073709551615) 
                                as a group by a.created_id
                         ) as d
                                   on a.id = d.created_id,
                     (SELECT @rownum := 0) AS R
                where a.stts = 'ACT' {$this->searchText($params['params'])}
                order by RNUM desc
                limit {$page}, {$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(a.id) as cnt 
                from {$this->table} a
                left join dept as b
                           on a.dept_id = b.id
                     left join code as c
                           on a.position = c.id
                     left join (
                            select * from 
                            (select * from user_log 
                                where stts = 'ACT' 
                                order by created_at desc LIMIT 18446744073709551615) 
                            as a group by a.created_id
                     ) as d
                               on a.id = d.created_id
                where a.stts = 'ACT' {$this->searchText($params)}";
    }

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);

        $sql = "select count(id) as cnt from {$this->table} where user_id = '{$data['user_id']}'";
        $cnt = $this->fetch($sql)[0]['cnt'];

        if ($cnt > 0) {
            return new Response(403, [], '이미 존재하는 아이디입니다.');
        }

        $sql = "select password('{$data['user_pw']}') as pw";
        $data['user_pw'] = $this->fetch($sql)[0]['pw'];

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        $query = $this->db->prepare($sql);
        $query->execute();
        $id = $this->db->lastInsertId();

        return new Response(200, ['id' => $id], '등록되었습니다.');
    }

    public function show ($id = null)
    {
        $sql = "select a.id, user_id, a.name, dept_id, tel, email, b.id as dept_id, b.group_id as dept_group_id, a.position, duty from {$this->table} as a
                left join dept as b
                on a.dept_id = b.id
                left join code as c
                on a.position = c.id
                where a.{$this->primaryKey} = {$id} and a.stts = 'ACT'";

        $result = $this->fetch($sql)[0];

        return new Response(200, $result, '');
    }

    public function changePw(array $data = [])
    {
        $this->validate($data, $this->changePwRequired);

        $sql = "select password('{$data['pre_pw']}') as pw";
        $change = $this->fetch($sql)[0]['pw'];

        $sql = "select user_pw from user where id = {$this->token['id']}";
        $pre = $this->fetch($sql)[0]['user_pw'];

        if ($change !== $pre) {
            return new Response(403, [],'기존의 비밀번호가 일치하지 않습니다.');
        }

        $sql = "select password('{$data['change_pw']}') as pw";
        $pw = $this->fetch($sql)[0]['pw'];

        $sql = "update {$this->table} set user_pw = '{$pw}' where {$this->primaryKey} = {$this->token['id']}";
        return new Response(200, $this->fetch($sql), '변경되었습니다.');
    }
}
