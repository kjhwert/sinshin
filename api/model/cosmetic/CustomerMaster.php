<?php

class CustomerMaster extends Model
{
    protected $table = 'customer_master';
    protected $searchableText = 'name';

    public static $PAINTING = 32; // 도장
    public static $INJECTION = 30; // 사출
    public static $ASSEMBLE = 184; // 조립

    public function index(array $params = [])
    {
        $sql = "select id, name from customer_master 
                where stts = 'ACT' {$this->searchText($params)} {$this->searchType($params)}";

        return new Response(200, $this->fetch($sql, $this->db));
    }

    public function pagingIndex (array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select id, name, ceo_name, ifnull(business_no,'') as business_no, business_address, 
                            business_tel, business_fax,
                            manager_name, manager_position, manager_tel, manager_email, business_section    
                        from {$this->table}
                        where stts = 'ACT' {$this->searchType($params['params'])} {$this->searchText($params['params'])}) 
                        as tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                    limit {$page}, {$perPage}
                    ";

        return new Response(200, $this->fetch($sql, $this->db), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count({$this->primaryKey}) as cnt 
                from {$this->table} 
                where stts = 'ACT' {$this->searchType($params)} {$this->searchText($params)}";
    }

    protected function searchType (array $params = [])
    {
        $type = $params['type'];

        if ($type && $type !== '') {
            return "and type = '{$type}'";
        } else {
            return "";
        }
    }

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from {$this->table} order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Company where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);
        $count = count($erp_results);

        foreach ($erp_results as $result) {
            $sql = "insert into {$this->table} set
                        name = '{$result['name']}',
                        type = '{$result['companyType']}',
                        data_sync = 'ERP',
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";

            if ($result['tel']) {
                $sql .= ", business_tel = '{$result['tel']}'";
            }

            if ($result['fax']) {
                $sql .= ", business_fax = '{$result['fax']}'";
            }

            if ($result['ceoName']) {
                $sql .= ", ceo_name = '{$result['ceoName']}'";
            }

            if ($result['address']) {
                $sql .= ", business_address = '{$result['address']}'";
            }

            if ($result['personInChargeName']) {
                $sql .= ", manager_name = '{$result['personInChargeName']}'";
            }

            if ($result['personInChargePosition']) {
                $sql .= ", manager_position = '{$result['personInChargePosition']}'";
            }

            if ($result['personInChargeTel']) {
                $sql .= ", manager_tel = '{$result['personInChargeTel']}'";
            }

            if ($result['email']) {
                $sql .= ", manager_email = '{$result['email']}'";
            }

            if ($result['code']) {
                $sql .= ", code = '{$result['code']}'";
            }

            $this->fetch($sql, $mes);
        }

        $this->update();
        return $count;
    }

    public function responseCreate () {
        $count = $this->create();
        return new Response(200, [], "{$count} 개의 데이터가 갱신 되었습니다.");
    }

    public function update($id = null, array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select * from MES_Company";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select updated_at from {$this->table} where id = {$result['id']}";
            $updated_at = $this->fetch($sql, $mes);

            if (!$result['updateDate'] || $updated_at === $result['updateDate']) {
                continue;
            }

            $sql = "update {$this->table} set
                        name = '{$result['name']}',
                        type = '{$result['companyType']}',
                        data_sync = 'ERP',
                        updated_id = 1,
                        updated_at = '{$result['updateDate']}'
                    ";

            if ($result['tel']) {
                $sql .= ", business_tel = '{$result['tel']}'";
            }

            if ($result['fax']) {
                $sql .= ", business_fax = '{$result['fax']}'";
            }

            if ($result['ceoName']) {
                $sql .= ", ceo_name = '{$result['ceoName']}'";
            }

            if ($result['address']) {
                $sql .= ", business_address = '{$result['address']}'";
            }

            if ($result['personInChargeName']) {
                $sql .= ", manager_name = '{$result['personInChargeName']}'";
            }

            if ($result['personInChargePosition']) {
                $sql .= ", manager_position = '{$result['personInChargePosition']}'";
            }

            if ($result['personInChargeTel']) {
                $sql .= ", manager_tel = '{$result['personInChargeTel']}'";
            }

            if ($result['email']) {
                $sql .= ", manager_email = '{$result['email']}'";
            }

            if ($result['code']) {
                $sql .= ", code = '{$result['code']}'";
            }

            $sql .= " where id = {$result['id']}";

            $this->fetch($sql, $mes);
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
