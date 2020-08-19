<?php


class AuthGroup extends Model
{
    protected $table = 'auth_group';
    protected $fields = ['id','name'];
    protected $paging = true;

    public static $INJECTION = 9;
    public static $PAINTING = 10;
    public static $ASSEMBLE = 11;

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
