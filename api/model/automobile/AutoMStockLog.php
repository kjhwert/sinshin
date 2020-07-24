<?php

class AutoMStockLog extends Model
{
    protected $table = 'automobile_stock_log';

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select a.id, a.customer_code, a.supply_code, a.name as product_name,
                    concat(a.brand_code,'/',a.car_code) as car_code,
                    a.customer, a.supplier, b.remain_qty, c.name, @rownum:= @rownum+1 AS RNUM
                    from automobile_master a
                    inner join (
                        select * from (
                              select * from automobile_stock_log
                              where stts = 'ACT'
                              order by created_at desc LIMIT 18446744073709551615) a
                        group by a.product_id
                    ) b
                    on a.id = b.product_id
                    inner join user c
                    on b.created_id = c.id,
                (SELECT @rownum:= 0) AS R
                where a.stts = 'ACT' and c.stts = 'ACT'
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function show($id = null, array $params = [])
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

        $sql = "select count(b.id) as cnt
                from automobile_master a
                         inner join automobile_stock_log b
                                    on a.id = b.product_id
                         inner join user c
                                    on b.created_id = c.id
                where a.id = {$params['id']} and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT'";
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

        $params = [
            'id' => $params['id'],
            'page'=> $page,
            'perPage'=>$perPage,
            'params'=>$params,
            'paging' => [
                'total_page' => $totalPageCount,
                'start_page' => $startPage,
                'end_page' => $endPage
            ]
        ];

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select a.customer_code, a.supply_code, a.name as product_name,
                   a.customer, a.supplier, b.change_qty, b.remain_qty, b.created_at, c.name,
                   (case
                        when b.change_qty > 0 then '입고'
                        when b.change_qty < 0 then '투입'
                    end) as type,
                    concat(a.brand_code,'/', a.car_code) as car_code,
                   @rownum:= @rownum+1 AS RNUM
                from automobile_master a
                inner join automobile_stock_log b
                on a.id = b.product_id
                inner join user c
                on b.created_id = c.id,
                     (SELECT @rownum:= 0) AS R
                where a.id = {$params['id']} and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT'
                order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function mainIndex ()
    {
        $sql = "select a.id, concat(a.name,' (', a.brand_code,'/',a.car_code,')') as product_name, b.remain_qty
                from automobile_master a
                inner join (
                    select * from (
                        select * from automobile_release_log
                        where stts = 'ACT'
                        order by created_at desc LIMIT 18446744073709551615) a
                    group by a.product_id
                ) b
                on a.id = b.product_id
                inner join user c
                on b.created_id = c.id
                where a.stts = 'ACT' and c.stts = 'ACT'
                order by b.remain_qty desc limit 10";

        $result = $this->fetch($sql);
        $cnt = 10 - count($result);
        
        if ($cnt > 0) {
            while($cnt > 0) {
                array_push($result, ['product_name' => '', 'remain_qty' => 0]);
                $cnt--;
            }
        }

        return new Response(200, $result, '');
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(id) as cnt from (
                    select id
                        from automobile_stock_log
                    where stts = 'ACT' group by product_id ) a
                ";
    }
}
