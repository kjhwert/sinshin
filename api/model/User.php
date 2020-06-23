<?php

class User extends Model
{
    protected $table = 'user';
    public $primaryKey = 'id';
    protected $searchable = ['user_id'];
    protected $fields = ['user_id','name', 'dept_id', 'tel', 'email', 'position', 'duty'];
    protected $foreign = [
        'dept_id' => 'Dept',
        'position' => 'Code'
    ];

    public function create(array $data = [])
    {
        $sql = "select password('{$data['user_pw']}') as pw";
        $data['user_pw'] = $this->fetch($sql)[0]['pw'];

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}
                stts = 'ACT',
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }

    /** Dept Relations
     * @param $id
     * @return mixed
     */
    protected function getDept ($id)
    {
        $sql = "select id, name from dept where stts = 'ACT' and id = {$id}";
        return $this->fetch($sql)[0];
    }

    /** Position Relations
     * @param $id
     * @return mixed
     */
    protected function getPosition ($id)
    {
        $sql = "select id, name from code where stts = 'ACT' and id = {$id}";
        return $this->fetch($sql)[0];
    }

    protected function getAuth ($id)
    {

    }
}
