<?php

class User extends Model
{
    protected $table = 'user';
    public $primaryKey = 'id';
    protected $searchable = ['user_id'];
    protected $fields = ['user_id','name'];

    public function create(array $data = [])
    {
        $sql = "select password('{$data['user_pw']}') as pw";
        $data['user_pw'] = $this->fetch($sql)[0]['pw'];

        $sql = "insert into {$this->table} set {$this->dataToString($data)}";
        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }
}
