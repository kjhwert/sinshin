<?php

class Import extends Model
{
    protected $table = 'MES_ProductTest';
    protected $indexRequired = [
      'pass' => 'string'
    ];

    public function __construct()
    {
        $this->token = $this->tokenValidation();
        $this->db = ErpDatabase::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $this->validate($params, $this->indexRequired);
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (select
                       case
                           when t.testType = 'I' then '수입검사'
                           when t.testType = 'P' then '내부공정검사'
                           when t.testType = 'D' then '출하검사'
                           else ''
                       end as type,
                       case
                           when t.pass = 'Y' then '합격'
                           when t.pass = 'N' then '불합격'
                       end as pass,
                       t.testDate, t.companyName, u.name as manager, po.code, p.modelName, 
                       p.name as product_name, t.ipgoQuantity,
                       t.testQuantity, t.defectQuantity, t.description, t.tester,
                       round((t.defectQuantity / t.testQuantity)*100,1) as defectPercent
                    from MES_ProductTest t
                    inner join MES_User u
                    on t.tester = u.account
                    inner join MES_ProcessOrder po
                    on t.processOrderId = po.id
                    inner join MES_Product p
                    on t.productCode = p.code
                where pass = '{$params['params']['pass']}'
                order by t.testDate asc limit 18446744073709551615 ) tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count({$this->primaryKey}) as cnt 
                from {$this->table} 
                where pass = '{$params['pass']}'";
    }

    public function show($id = null)
    {

    }

    public function create(array $data = [])
    {

    }

    public function update($id = null, array $data = [])
    {

    }

    public function destroy($id = null)
    {

    }

}
