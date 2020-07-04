<?php

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
        $result['auth'] = $this->getUserAuth($result['id']);

        unset($result['dept_id']);
        return new Response(
            200,
            $result,
            ""
        );

    }

    protected function getUserAuth ($id)
    {
        $sql = "select c.menu, c.function from user_auth as a
                    right join user as b
                    on a.user_uid = b.id
                    left join (
                        select aa.auth_group_id, bb.menu, bb.function from auth_list as aa
                        left join auth_master as bb
                        on aa.auth_id = bb.id
                    ) as c
                    on a.auth_group_id = c.auth_group_id
                where a.user_uid = {$id}";

        $result = $this->fetch($sql);

        $auth = [];
        foreach ($result as $item) {
            $tmp = array_values($item);
            if (array_key_exists($tmp[0],$auth)) {
                array_push($auth[$tmp[0]], $tmp[1]);
            } else {
                $auth[$tmp[0]] = [$tmp[1]];
            }
        }

        return $auth;
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
