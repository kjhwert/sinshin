<?php

require_once "../user/User.php";

class Login
{
    protected $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function verification (array $params = [])
    {
        $id = $params['user_id'];
        $pw = $params['user_pw'];

        $sql = "select count('id') as cnt from user where user_id = '{$id}'";
        $result = $this->fetch($sql)[0];

        if ($result['cnt'] === 0) {
            return new Response(
                406,
                [],
                "존재하지 않는 아이디입니다."
            );
        }

        $sql = "select password('{$pw}') as pw";
        $pw = $this->fetch($sql)[0]['pw'];

        $sql = "select a.id, user_id, a.name, tel, email, duty, b.name as dept, c.name as position from user as a
                left join dept as b
                on a.dept_id = b.id
                left join code as c
                on a.position = c.id 
                where user_id = '{$id}' and user_pw = '{$pw}' and a.stts = 'ACT'";

        $result = $this->fetch($sql);

        if (empty($result)) {
            return new Response(
                406,
                [],
                "비밀번호가 일치하지 않습니다."
            );
        }

        $result = $result[0];

        $result['token'] = $this->getToken($result['id']);

        unset($result['dept_id']);
        return new Response(
            200,
            $result,
            ""
        );

    }

    protected function getToken ($id)
    {
        $payloadArray = array();
        $payloadArray['userId'] = $id;
        if (isset($nbf)) {$payloadArray['nbf'] = $nbf;}
        if (isset($exp)) {$payloadArray['exp'] = $exp;}

        return JWT::encode($payloadArray, JWT::$tokenKey);
    }

    protected function rowCount ($sql = null)
    {
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->rowCount();
    }

    protected function fetch ($sql = null)
    {
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
