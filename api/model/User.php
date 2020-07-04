<?php


class User extends Model
{
    protected $table = 'user';
    protected $fields = ['user_id','name', 'dept_id', 'tel', 'email', 'position', 'duty'];
    protected $required = [
        'user_id' => 'string',
        'user_pw'=> 'string',
        'name' => 'string',
        'dept_id' => 'integer',
        'position' => 'integer'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = (int)$params["perPage"];
        $page = ((int)$params["page"] * $perPage);

        $sql = "select count(id) as total from user where stts = 'ACT'";
        $result = $this->fetch($sql)[0]['total'];

        $total = ceil($result / $perPage);

        $sql = "select a.id, user_id, a.name, a.dept_id, tel, email, position, duty, 
                    a.created_at, b.name as dept, c.name as position, @rownum := @rownum+1 AS RNUM 
                from user as a
                    left join dept as b
                    on a.dept_id = b.id
                    left join code as c
                    on a.position = c.id,
                        (SELECT @rownum := 0) AS R
                where a.stts = 'ACT'
                order by RNUM desc
                limit {$page}, {$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $total);
    }

    public function create(array $data = [])
    {
        $this->validate($data);

        $sql = "select password('{$data['user_pw']}') as pw";
        $data['user_pw'] = $this->fetch($sql)[0]['pw'];

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql)[0], '등록되었습니다.');
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

    protected function validate (array $data = [])
    {
        $result = array_diff_key($this->required, $data);
        if ($result) {
            $result = implode(',',array_keys($result));

            (new ErrorHandler())->typeNull($result);
        }
    }
}
