<?php

class MaterialMaster extends Model
{
    protected $table = 'material_master';
    protected $searchableText = 'mm.code';

    public function index(array $params = [])
    {
        if (!$params['type']) {
            return new Response(403, [],'type이 존재하지 않습니다.');
        }

        $sql = "select mm.id, mm.code, mm.name, mm.type, mm.qty, @rownum:= @rownum+1 AS RNUM 
                    from {$this->table} mm,
                    (SELECT @rownum:= 0) AS R
                    where stts = 'ACT' and type = '{$params['type']}' 
                    {$this->searchName($params)}
                    order by RNUM desc";

        return new Response(200, $this->fetch($sql, $this->db), '');
    }

    protected function searchName (array $params = [])
    {
        $search = $params['search'];

        if ($search) {
            return "and (mm.name like '%{$search}%' or mm.code like '%{$search}%')";
        } else {
            return "";
        }
    }

    public function pagingIndex (array $params = []) {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                    from (select mm.id, mm.name, mm.type, mm.unit, 
                                mm.qty, mm.code, mm.created_at, cm.name as supplier
                            from {$this->table} mm
                            inner join customer_master cm
                            on mm.supplier = cm.id
                            where mm.stts = 'ACT' and mm.type like '%{$params['params']['type']}%' {$this->pagingSearchText($params['params'])}
                          ) as tot,
                          (SELECT @rownum:= 0) AS R
                        order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql, $this->db), '', $params['paging']);
    }

    protected function pagingSearchText (array $params = []) {
        $search = $params['search'];
        return "and (mm.code like '%{$search}%' or mm.name like '%{$search}%')";
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count({$this->primaryKey}) as cnt 
                from {$this->table} mm
                where mm.stts = 'ACT' and mm.type like '%{$params['type']}%' {$this->searchText($params)}";
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
        $pageLength = 10; // 페이징 길이
        $sql = $this->paginationQuery($params);
        $totalCount = $this->fetch($sql, $this->db)[0]['cnt'];
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

    public function show($id = null)
    {
        $sql = "select * from {$this->table} where stts = 'ACT' and id = {$id}";
        return new Response(200, $this->fetch($sql, $this->db), '');
    }

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from material_master order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Resource where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);
        $count = count($erp_results);

        foreach ($erp_results as $result) {
            $sql = "insert into material_master set
                        name = '{$result['name']}',
                        type = '{$result['categoryCode']}',
                        unit = '{$result['unit']}',
                        supplier = {$result['purchaseCompanyId']},
                        code = '{$result['code']}',
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";

            $this->fetch($sql, $mes);
        }

        return new Response(200, [], "{$count}개의 데이터가 갱신 되었습니다.");
    }

    protected function fetch ($sql = null, $db = null)
    {
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return new Response(403, [], $e.message);
        }
    }
}
