<?php

class Model
{
    protected $db = null;
    protected $table = null;
    public $primaryKey = 'id';
    protected $searchableText = null;
    protected $searchableDate = null;
    protected $searchableAsset = null;
    protected $fields = [];
    protected $paging = true;
    public $token = [];
    protected $createRequired = [];
    protected $updateRequired = [];
    protected $reversedSort = false;
    protected $qrMasterId = 1;
    protected $sort = [];

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
        if ($this->paging) {
            return $this->getPagingQuery($params);
        }

        $sql = "select {$this->getFields()}, @rownum:= @rownum+1 AS RNUM 
                from {$this->table},
                (SELECT @rownum:= 0) AS R
                where stts = 'ACT' {$this->searchText($params)}
                order by RNUM desc";

        return new Response(200, $this->fetch($sql), '');
    }

    protected function getPagingQuery (array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select {$this->getFields()}, @rownum:= @rownum+1 AS RNUM from {$this->table},
                (SELECT @rownum:= 0) AS R
                where stts = 'ACT' {$this->searchText($params['params'])}
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
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
        $data = $this->validate($data, $this->createRequired);

        $sql = "insert into {$this->table} 
                set {$this->dataToString($data)}, 
                stts = 'ACT', 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '?????????????????????.');
    }

    /**
     * @param null $id
     * @param array $data
     * @return Response
     */
    public function update($id = null, array $data = [])
    {
        $this->validate($data, $this->updateRequired);

        $sql = "update {$this->table} set {$this->dataToString($data)} where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '?????????????????????.');
    }

    /**
     * @param null $id
     * @return Response
     */
    public function destroy ($id = null)
    {
        $sql = "update {$this->table} set stts = 'DELETE' where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '?????????????????????.');
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
            $returnArray = array('id' => $payload->userId, 'dept_id'=>$payload->dept_id);
            if (isset($payload->exp)) {
                $returnArray['exp'] = date(DateTime::ISO8601, $payload->exp);;
            }

            return $returnArray;
        }
        catch(Exception $e) {
            return new Response(400, [], $e->getMessage());
        }
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count({$this->primaryKey}) as cnt 
                from {$this->table} 
                where stts = 'ACT' {$this->searchText($params)}";
    }

    protected function pagination(array $params = [])
    {
        if (!array_key_exists('page', $params)) {
            (new ErrorHandler())->typeNull('page');
        }

        if (!array_key_exists('perPage', $params)) {
            (new ErrorHandler())->typeNull('perPage');
        }

        $page = (int)($params['page']-1);
        $perPage = (int)$params['perPage'];
        $pageLength = 10; // ????????? ??????
        $sql = $this->paginationQuery($params);
        $totalCount = $this->fetch($sql)[0]['cnt'];
        $totalCount = (Int)$totalCount;

        $totalPageCount = (int)(($totalCount - 1) / $perPage) + 1;
        $startPage = ( (int)($page / $pageLength)) * $pageLength + 1;
        $endPage = $startPage + $pageLength - 1;
        if ( $totalPageCount <= $endPage){
            $endPage = $totalPageCount;
        }

        unset($params["page"]);
        unset($params["perPage"]);

        return [
            'page'=> $page,
            'perPage'=>$perPage,
            'params'=>$params,
            'paging' => [
                'total_page' => $totalPageCount,
                'start_page' => $startPage,
                'end_page' => $endPage
            ]
        ];
    }

    protected function fetch ($sql = null)
    {
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return new Response(403, [], $e.message);
        }
    }

    protected function getFields ()
    {
        return implode(", ", $this->fields);
    }

    protected function searchAsset(array $params = [])
    {
        $asset_id = $params['asset_id'];

        if (!$asset_id || $asset_id === 0) {
            return "";
        }

        return "and {$this->searchableAsset} = {$asset_id}";
    }

    protected function searchText (array $params = [])
    {
        $search = $params['search'];

        if ($search) {
            if ($this->searchableText === null) {
                return (new ErrorHandler())->typeNull('searchableText');
            }

            return "and {$this->searchableText} like '%{$search}%'";
        } else {
            return "";
        }
    }

    protected function searchDate (array $params = [])
    {
        if ($this->searchableDate === null) {
            return (new ErrorHandler())->typeNull('searchableDate');
        }

        $search = "";

        if ($params['start_date']) {
            $search .= "and {$this->searchableDate} >= '{$params['start_date']} 00:00:00' ";
        }

        if ($params['end_date']) {
            $search .= "and {$this->searchableDate} <= '{$params['end_date']} 23:59:59' ";
        }

        return $search;
    }

    protected function dataToString (array $data = [])
    {
        $filter = array_filter($data, function ($val, $key) {
            if(!$val || is_object($val) || is_array($val)) {
                return;
            }

            return $key !== $this->primaryKey;
        },ARRAY_FILTER_USE_BOTH);

        return implode(', ',array_map(function ($key, $value) {
            if (gettype($value) === "integer") {
                return "{$key} = {$value}";
            }

            return "{$key} = \"{$value}\"";
        }, array_keys($filter), $filter));
    }

    protected function validate (array $data = [], $required = [])
    {
        $result = array_diff_key($required, $data);
        if ($result) {
            $result = implode(',',array_keys($result));

            (new ErrorHandler())->typeNull($result);
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $required) && $required[$key] === "integer") {
                if(!is_numeric($value)) {
                    (new ErrorHandler())->typeError($key);
                }

                if (gettype($value) === 'double') {
                    $data[$key] = (float)$value;
                } else {
                    $data[$key] = (int)$value;
                }
            }
        }

        return $data;
    }

    protected function sorting (array $data = [])
    {
        $sort = $data['sort'];
        $order = $data['order'];

        if (!$sort) {
            return (new ErrorHandler())->typeNull('sort');
        }

        if (!$order) {
            return (new ErrorHandler())->typeNull('order');
        }

        if ($this->reversedSort) {
            $order = $order === "asc" ? "desc" : "asc";
        }

        if (!$this->sort[$sort]) {
            return new Response(403, [], '????????? sort array??? ??????????????????.');
        }

        return "{$this->sort[$sort]} {$order}";
    }

    protected function setTransaction (array $data = [])
    {
        try {
            $this->db->beginTransaction();

            foreach ($data as $query) {
                try {
                    $stmt = $this->db->prepare($query);
                    $stmt->execute();
                } catch (Exception $e) {
                    throw $e;
                }
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'????????? ?????? ??? ????????? ?????????????????????.');
        }

        return new Response(200, [], '?????????????????????.');
    }

    protected function isAvailableUser ()
    {
        $dept_id = $this->getDeptId();

        if ($this->token['dept_id'] !== $dept_id) {
            return new Response(403, [],'?????? ?????? ???????????? ?????? ??????????????????.');
        }
    }

    protected function isDeptProcess ($qr_id = null)
    {
        $sql = "select dept_id from qr_code where id = {$qr_id}";
        $created_dept_id = $this->fetch($sql)[0]['dept_id'];

        if ($created_dept_id !== $this->token['dept_id']) {
            return new Response(403, [], "??? ????????? QR????????? ????????? ??? ????????????.");
        }
    }

    protected function getDeptId ()
    {
        return Dept::$INJECTION;
    }

    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
