<?php

class MoldMaster extends Model
{
    protected $table = 'mold_master';
    protected $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select code, asset_no, model_name, product_name, ifnull(unit_weight, '') as unit_weight, 
                    ifnull(runner, '') runner, ifnull(cycle_time, '') cycle_time, 
                    ifnull(cavity, '') cavity, ifnull(shot_cnt, '') shot_cnt, ton, made_name,
                    ifnull(standard, '') standard, ifnull(mold_no, '') mold_no,
                    '신신화학' supplier, ifnull(qty,'-') qty,
                    @rownum:= @rownum+1 AS RNUM 
                    from {$this->table},
                    (SELECT @rownum:= 0) AS R
                    where stts = 'ACT' {$this->searchText($params['params'])}
                    order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql, $this->db), '', $params['paging']);
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

    public function create(array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select id from mold_master order by id desc limit 1";
        $mes_id = $this->fetch($sql, $mes)[0]['id'];

        $sql = "select * from MES_Mold where id > {$mes_id}";
        $erp_results = $this->fetch($sql, $erp);

        foreach ($erp_results as $result) {

            $sql = "select count(*) cnt from mold_master where id = {$result['id']}";
            $hasMes = $this->fetch($sql, $mes)[0]['cnt'];

            if ($hasMes > 0) {
                continue;
            }

            $sql = "insert into mold_master set
                        id = {$result['id']},
                        asset_no = '{$result['assetNo']}',
                        code = '{$result['code']}',
                        model_name = '{$result['modelName']}',
                        product_name = '{$result['productName']}',
                        ton = '{$result['ton']}',
                        data_sync = 'ERP',
                        created_id = 1,
                        created_at = '{$result['createDate']}'
                    ";

            if ($result['price']) {
                $sql .= ", price = {$result['price']}";
            }

            if ($result['partName']) {
                $sql .= ", part_name = '{$result['partName']}'";
            }

            if ($result['standard']) {
                $sql .= ", standard = '{$result['productStandard']}'";
            }

            if ($result['unitWeight']) {
                $sql .= ", unit_weight = '{$result['unitWeight']}'";
            }

            if ($result['runner']) {
                $sql .= ", runner = '{$result['runner']}'";
            }

            if ($result['cycleTime']) {
                $sql .= ", cycle_time = '{$result['cycleTime']}'";
            }

            if ($result['designCapacity']) {
                $sql .= ", design_cavity = {$result['designCapacity']}";
            }

            if($result['capacity']) {
                $sql .= ", cavity = {$result['capacity']}";
            }

            if ($result['shotCount']) {
                $sql .= ", shot_cnt = {$result['shotCount']}";
            }

            if ($result['chulgoCompanyId']) {
                $sql .= ", out_id = {$result['chulgoCompanyId']}";
            }

            if ($result['chulgoDate']) {
                $sql .= ", out_date = '{$result['chulgoDate']}'";
            }

            if ($result['makerName']) {
                $sql .= ", made_name = '{$result['makerName']}'";
            }

            if ($result['makeDate']) {
                $sql .= ", made_date = '{$result['makeDate']}'";
            }

            if ($result['ownerCompanyId']) {
                $sql .= ", customer_id = {$result['ownerCompanyId']}";
            }

            if ($result['updateDate']) {
                $sql .= ", updated_at = '{$result['updateDate']}'";
            }

            if ($result['makerMoldNo']) {
                $sql .= ", mold_no = '{$result['makerMoldNo']}'";
            }

            if ($result['moldGroupCount']) {
                $sql .= ", qty = '{$result['moldGroupCount']}'";
            }

            $this->fetch($sql, $mes);
        }

        $this->update();
    }

    public function update($id = null, array $data = [])
    {
        $erp = ErpDatabase::getInstance()->getDatabase();
        $mes = Database::getInstance()->getDatabase();

        $sql = "select * from MES_Mold";
        $erp_results = $this->fetch($sql, $erp);
        $count = 0;

        foreach ($erp_results as $result) {

            $sql = "select updated_at from mold_master where id = {$result['id']}";
            $updated_at = $this->fetch($sql, $mes)[0]['updated_at'];

            if ($result['updateDate'] === $updated_at) {
                continue;
            }

            $count++;

            $sql = "update mold_master set
                        asset_no = '{$result['assetNo']}',
                        code = '{$result['code']}',
                        model_name = '{$result['modelName']}',
                        product_name = '{$result['productName']}',
                        ton = '{$result['ton']}'
                    ";

            if ($result['price']) {
                $sql .= ", price = {$result['price']}";
            }

            if ($result['partName']) {
                $sql .= ", part_name = '{$result['partName']}'";
            }

            if ($result['standard']) {
                $sql .= ", standard = '{$result['productStandard']}'";
            }

            if ($result['unitWeight']) {
                $sql .= ", unit_weight = '{$result['unitWeight']}'";
            }

            if ($result['runner']) {
                $sql .= ", runner = '{$result['runner']}'";
            }

            if ($result['cycleTime']) {
                $sql .= ", cycle_time = '{$result['cycleTime']}'";
            }

            if ($result['designCapacity']) {
                $sql .= ", design_cavity = {$result['designCapacity']}";
            }

            if($result['capacity']) {
                $sql .= ", cavity = {$result['capacity']}";
            }

            if ($result['shotCount']) {
                $sql .= ", shot_cnt = {$result['shotCount']}";
            }

            if ($result['chulgoCompanyId']) {
                $sql .= ", out_id = {$result['chulgoCompanyId']}";
            }

            if ($result['chulgoDate']) {
                $sql .= ", out_date = '{$result['chulgoDate']}'";
            }

            if ($result['makerName']) {
                $sql .= ", made_name = '{$result['makerName']}'";
            }

            if ($result['makeDate']) {
                $sql .= ", made_date = '{$result['makeDate']}'";
            }

            if ($result['ownerCompanyId']) {
                $sql .= ", customer_id = {$result['ownerCompanyId']}";
            }

            if ($result['updateDate']) {
                $sql .= ", updated_at = '{$result['updateDate']}'";
            }

            if ($result['makerMoldNo']) {
                $sql .= ", mold_no = '{$result['makerMoldNo']}'";
            }

            if ($result['moldGroupCount']) {
                $sql .= ", qty = '{$result['moldGroupCount']}'";
            }

            $sql .= " where id = {$result['id']}";

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
