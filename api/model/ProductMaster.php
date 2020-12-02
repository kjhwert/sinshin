<?php

class ProductMaster extends Model
{
    protected $table = 'product_master';
    protected $fields = ['code', 'name', 'model'];
    protected $searchableText = 'pm.name';

    protected $searchIndexRequired = [
        'search' => 'string'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $this->token = $this->tokenValidation();
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select pm.code, pm.name, pm.model, pm.id,
                        case
                            when pm.type = 'F' then '완제품'
                            when pm.type = 'P' then '부품'
                            when pm.type = 'O' then '공정품'
                            when pm.type = 'S' then '공용'
                        end as type 
                        from {$this->table} pm
                where stts = 'ACT' {$this->searchText($params['params'])}
                order by id asc) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql, $this->db), '', $params['paging']);
    }

    public function searchIndex (array $params = [])
    {
        $this->token = $this->tokenValidation();
        $this->validate($params, $this->searchIndexRequired);

        if (mb_strlen($params['search'], 'utf-8') <= 1) {
            return new Response(403, [], '2자 이상 검색해주세요.');
        }

        $sql = "
                select pm.code, pm.name, pm.model, pm.id,
                        case
                            when pm.type = 'F' then '완제품'
                            when pm.type = 'P' then '부품'
                            when pm.type = 'O' then '공정품'
                            when pm.type = 'S' then '공용'
                        end as type, ifnull(pc.name,'') as process_type
                        from {$this->table} pm
                        left join process_code pc
                        on pm.process_type = pc.code
                where stts = 'ACT' {$this->searchText($params)}
                ";

        return new Response(200, $this->fetch($sql, $this->db), '');
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(pm.id) as cnt 
                from {$this->table} pm
                where pm.stts = 'ACT' {$this->searchText($params)} {$this->searchType($params)}";
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

    protected function searchType (array $params = [])
    {
        $type = $params['type'];

        if ($type) {
            return "and type = '{$type}'";
        } else {
            return "";
        }
    }

    /**
     *  ERP의 product 정보를 가져온다.
     * @param array $data
     * @return Response
     */
    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from product_master order by id desc limit 1";
        $id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Product where id > {$id}";
        $erp_results = $this->fetch($sql, $erp);

        $createCount = 0;
        foreach ($erp_results as $result) {

            $sql = "select count(id) as cnt from product_master where id = {$result['id']}";
            $has = $this->fetch($sql, $mes)[0]['cnt'];

            if ($has > 0 || $result['deleted'] === 'Y') {
                continue;
            }

            $result['name'] = str_replace("'", "", $result['name']);

            $sql = "insert into product_master set
                        id = {$result['id']},
                        code = '{$result['code']}',
                        name = '{$result['name']}',
                        model = '{$result['modelName']}',
                        part_name = '{$result['partName']}',
                        part_code = '{$result['partProductCode']}',
                        process_type = '{$result['processType']}',
                        type = '{$result['type']}',
                        data_sync = 'ERP',
                        created_id = 1,
                        created_at = SYSDATE()
                    ";

            $this->fetch($sql, $mes);
            $createCount += 1;
        }

        $deleteCount = $this->destroy();

        return new Response(200, [], "{$createCount} 개의 데이터가 갱신 되고 {$deleteCount} 개의 데이터가 삭제되었습니다.");
    }

    /**
     * @param null $id
     * @param array $data
     * @return Response
     * 삭제된 내역을 업데이트 한다.
     */
    public function update($id = null, array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select pro.code from MES_Process p 
                    inner join MES_Product pro
                    on p.productCode = pro.code
                    where p.resourceId is not null and pro.deleted = 'N'";
        $erp_results = $this->fetch($sql, $erp);

        $updateCount = 0;
        foreach ($erp_results as $result) {

            $sql = "select material_id from product_master where code = '{$result['code']}'";
            $material_id = $this->fetch($sql, $mes)[0]['material_id'];

            if (!$material_id) {
                continue;
            }

            $sql = "update product_master set
                        material_id = {$material_id}
                        where code = '{$result['code']}'
                    ";

            $updateCount += 1;
            $this->fetch($sql, $mes);
        }

        return new Response(200, [], "{$updateCount} 개의 데이터가 갱신되었습니다.");
    }

    public function destroy($id = null)
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select * from MES_Product where deleted = 'Y'";
        $erp_results = $this->fetch($sql, $erp);

        $deleteCount = 0;
        foreach ($erp_results as $result) {
            $sql = "select stts from product_master where code = '{$result['code']}'";
            $stts = $this->fetch($sql, $mes)[0]['stts'];

            if ($stts === 'DELETE') {
                continue;
            }

            $sql = "update product_master set
                        stts = 'DELETE'
                        where code = '{$result['code']}'
                    ";

            $this->fetch($sql, $mes);
            $deleteCount += 1;
        }

        return $deleteCount;
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
