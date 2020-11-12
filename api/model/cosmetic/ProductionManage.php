<?php

class ProductionManage extends Model
{
    public function __construct()
    {
        $this->token = $this->tokenValidation();
        $this->db = ErpDatabase::getInstance()->getDatabase();
    }

    public function injectionIndex(array $params = [])
    {
        $injection = CustomerMaster::$INJECTION;

        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select machine.machineNo, po.priority, mold.code, product.name,
                           po.requestDeliveryDate, po.quantity, 
                           ifnull(po.completeQuantity,'') completeQuantity,
                           (po.quantity - ifnull(po.completeQuantity, 0)) restQuantity, 
                           mold.capacity, mold.cycleTime, '-' spendTime
                        from MES_ProcessOrder po
                        inner join MES_Product product
                            on po.productCode = product.code
                        inner join MES_Process process
                            on po.productCode = process.productCode
                        left join MES_Mold mold
                            on process.moldId = mold.id
                        inner join MES_Machine machine
                            on po.machineId = machine.id
                        where po.status in ('P', 'R') and po.companyId = {$injection}
                        order by machine.machineNo desc, po.priority desc limit 10000 ) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                    limit {$page}, {$perPage}
                    ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function injectionPaginationQuery (array $params = [])
    {
        $injection = CustomerMaster::$INJECTION;
        return "select count(po.id) as cnt
                    from MES_ProcessOrder po
                    inner join MES_Product product
                        on po.productCode = product.code
                    inner join MES_Process process
                        on po.productCode = process.productCode
                    inner join MES_Mold mold
                        on process.moldId = mold.id
                    inner join MES_Machine machine
                        on po.machineId = machine.id
                    where po.status in ('P', 'R') and po.companyId = {$injection}";
    }

    public function paintingIndex(array $params = [])
    {
        $painting = CustomerMaster::$PAINTING;

        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select po.sujuNum, po.priority, product.name, po.processTypeName as type,
                           po.requestDeliveryDate as requestDate, po.quantity, 
                           ifnull(po.completeQuantity,'') completeQuantity,
                           suju.requestDeliveryDate as completeDate, po.orderDate
                        from MES_ProcessOrder po
                        inner join MES_Product product
                            on po.productCode = product.code
                        inner join MES_Process process
                            on po.productCode = process.productCode
                        inner join MES_Suju suju
                            on po.sujuNum = suju.sujuNum
                        where po.companyId = {$painting} 
                        and po.status in ('R', 'P')
                        order by po.priority desc limit 10000 ) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                    limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paintingPaginationQuery(array $params = []) {
        $painting = CustomerMaster::$PAINTING;

        return "select count(po.id) as cnt
                    from MES_ProcessOrder po
                    inner join MES_Product product
                        on po.productCode = product.code
                    inner join MES_Process process
                        on po.productCode = process.productCode
                    where po.companyId = {$painting} and po.status in ('R', 'P')";
    }

    public function assembleIndex(array $params = [])
    {
        $assemble = CustomerMaster::$ASSEMBLE;

        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select po.sujuNum, product.name, po.processTypeName,
                           po.requestDeliveryDate as requestDate, po.quantity,
                           suju.requestDeliveryDate as completeDate, po.orderDate
                        from MES_ProcessOrder po
                        inner join MES_Product product
                            on po.productCode = product.code
                        inner join MES_Process process
                            on po.productCode = process.productCode
                        inner join MES_Suju suju
                            on po.sujuNum = suju.sujuNum
                        where po.companyId = {$assemble} and po.status in ('R', 'P')
                        order by po.orderDate asc limit 10000 ) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                    limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function assemblePaginationQuery(array $params = []) {
        $assemble = CustomerMaster::$ASSEMBLE;

        return "select count(po.id) as cnt
                    from MES_ProcessOrder po
                    inner join MES_Product product
                        on po.productCode = product.code
                    inner join MES_Process process
                        on po.productCode = process.productCode
                    where po.companyId = {$assemble} and po.status in ('R', 'P')";
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

        switch ($params['type']) {
            case 'injection':
                $sql = $this->injectionPaginationQuery($params);
                break;
            case 'painting' :
                $sql = $this->paintingPaginationQuery($params);
                break;
            case 'assemble' :
                $sql = $this->assemblePaginationQuery($params);
                break;
        }

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
}
