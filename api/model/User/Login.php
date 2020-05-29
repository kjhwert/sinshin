<?php

require_once "model/User/User.php";

class Login extends User
{
    public function verification (array $params = [])
    {
        $id = $params['user_id'];
        $pw = $params['user_pw'];

        $sql = "select count({$this->primaryKey}) as cnt from {$this->table} where user_id = '{$id}'";
        $result = $this->rowCount($sql);

        if ($result === 0) {
            return new Response(
                406,
                [],
                "존재하지 않는 아이디입니다."
            );
        }

        $sql = "select password('{$pw}') as pw";
        $pw = $this->fetch($sql)[0]['pw'];

        $sql = "select id from {$this->table} where user_id = '{$id}' and user_pw = '{$pw}'";
        $result = $this->fetch($sql)[0];
        $result['token'] = $this->getToken($result['id']);

        if ($result) {
            return new Response(
                200,
                $result,
                ""
            );
        }

        return new Response(
            406,
            [],
            "비밀번호가 일치하지 않습니다."
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