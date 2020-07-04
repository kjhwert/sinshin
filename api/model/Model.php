<?php

class Model
{
    protected $db = null;
    protected $table = null;
    public $primaryKey = 'id';
    protected $searchable = [];
    protected $fields = [];
    protected $paging = true;
    public $token = [];
    protected $required = [];

    public function __construct()
    {
        $this->token = $this->tokenValidation();
        $this->db = Database::getInstance()->getDatabase();
    }

    /**
     * @param array $params
     * @return Response
     */
    public function index (array $params = [])
    {
        $sql = $this->getIndexQuery($params);
        return new Response(200, $this->fetch($sql), '');
    }

    /**
     * @param null $id
     * @return Response
     */
    public function show ($id = null)
    {
        $sql = "select {$this->getFields()} from {$this->table} where {$this->primaryKey} = {$id} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }

    /**
     * @param array $data
     * @return Response
     */
    public function create(array $data = [])
    {
        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }

    /**
     * @param null $id
     * @param array $data
     * @return Response
     */
    public function update($id = null, array $data = [])
    {
        $sql = "update {$this->table} set {$this->dataToString($data)} where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '수정되었습니다.');
    }

    /**
     * @param null $id
     * @return Response
     */
    public function destroy ($id = null)
    {
        $sql = "update {$this->table} set stts = 'DELETE' where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '삭제되었습니다.');
    }

    protected function paramsValidation ()
    {

    }

    public function tokenValidation ()
    {
        if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            return (new ErrorHandler())->unAuthorized();
        }

        $token = $_SERVER['HTTP_AUTHORIZATION'];

        if (!$token) {
            return (new ErrorHandler())->unAuthorized();
        }

        try {
            $payload = JWT::decode($token, JWT::$tokenKey, array('HS256'));
            $returnArray = array('id' => $payload->userId);
            if (isset($payload->exp)) {
                $returnArray['exp'] = date(DateTime::ISO8601, $payload->exp);;
            }

            return $returnArray;
        }
        catch(Exception $e) {
            return new Response(400, [], $e->getMessage());
        }
    }

    protected function pagination(array $params = [])
    {
        if (!array_key_exists('page', $params)) {
            (new ErrorHandler())->typeNull('page');
        }

        if (!array_key_exists('perPage', $params)) {
            (new ErrorHandler())->typeNull('perPage');
        }

        $page = (int)$params["page"] - 1;
        $perPage = $params["perPage"];

        unset($params["page"]);
        unset($params["perPage"]);

        return ['page'=> $page, 'perPage'=>$perPage, 'params'=>$params];
    }

    /**
     * @param array $params
     */
    protected function getIndexQuery (array $params = [])
    {
        if ($this->paging) {
            $params = $this->pagination($params);

            $perPage = $params["perPage"];
            $page = ((int)$params["page"] * (int)$perPage);
            $params = $params["params"];

            $sql = "select {$this->getFields()} from {$this->table}";

            if (!empty($params)) {
                $sql .= " where {$this->search($params)}
                    and stts = 'ACT'
                    order by {$this->primaryKey} desc
                    limit {$page},{$perPage}";
            } else {
                $sql .= " where stts = 'ACT'
                    order by {$this->primaryKey} desc
                    limit {$page},{$perPage}";
            }

            return $sql;
        }

        $sql = "select {$this->getFields()} from {$this->table}";

        if (!empty($params)) {
            $sql .= " where {$this->search($params)}
                    and stts = 'ACT'
                    order by {$this->primaryKey} desc";
        } else {
            $sql .= " where stts = 'ACT'
                    order by {$this->primaryKey} desc";
        }

        return $sql;
    }

    protected function fetch ($sql = null)
    {
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getFields ()
    {
        return implode(", ", $this->fields);
    }

    protected function search (array $params = [])
    {
        return implode(" and ", array_map(function ($key, $value) {
            if ($key === "search") {
                return implode(" and ", array_map(function ($val) use ($value) {
                    return "{$val} like '%{$value}%'";
                }, $this->searchable));
            }

            return "{$key} = {$value}";
        }, array_keys($params), $params));
    }

    protected function dataToString (array $data = [])
    {
        $filter = array_filter($data, function ($key) {
            return $key !== $this->primaryKey;
        },ARRAY_FILTER_USE_KEY);

        return implode(', ',array_map(function ($key, $value) {
            if($key === $this->primaryKey) {
                return;
            }

            if (gettype($value) === "integer") {
                return "{$key} = {$value}";
            }

            return "{$key} = \"{$value}\"";
        }, array_keys($filter), $filter));
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
