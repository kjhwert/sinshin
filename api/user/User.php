<?php


class User extends Model
{
    protected $table = 'user';
    protected $fields = ['user_id','a.name', 'a.dept_id', 'tel', 'email', 'position', 'duty'];

    public function index(array $params = [])
    {
        $sql = "select {$this->getFields()}, b.name as dept, c.name as position from user as a
                left join dept as b
                on a.dept_id = b.id
                left join code as c
                on a.position = c.id
                where a.stts = 'ACT'
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function create(array $data = [])
    {
        $sql = "select password('{$data['user_pw']}') as pw";
        $data['user_pw'] = $this->fetch($sql)[0]['pw'];

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }

    public function show ($id = null)
    {
        $sql = "select {$this->getFields()}, b.name as dept, c.name as position from {$this->table} as a
                left join dept as b
                on a.dept_id = b.id
                left join code as c
                on a.position = c.id
                where a.{$this->primaryKey} = {$id} and a.stts = 'ACT'";

        return new Response(200, $this->fetch($sql), '');
    }
}
