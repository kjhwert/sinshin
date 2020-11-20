<?php

class AutoMStockLog extends Model
{
    protected $table = 'automobile_stock_log';
    protected $searchableText = 'a.customer_code';
    protected $updateRequired = [
        'out_qty' => 'integer'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select a.id, a.customer_code, a.supply_code, a.name as product_name,
                            a.brand_code, a.car_code, b.created_at,
                            a.customer, a.supplier, b.remain_qty, c.name
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
                            on b.created_id = c.id
                        where a.stts = 'ACT' and c.stts = 'ACT' {$this->searchText($params['params'])} 
                        order by b.created_at asc) as tot,
                (SELECT @rownum:= 0) AS R
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
                        when b.type = 1 then '입고'
                        when b.type = 2 then '투입'
                        when b.type = 3 then '반출'
                    end) as type,
                    a.brand_code, a.car_code,
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
        $sql = "select a.id, a.name as product_name, b.remain_qty, a.supply_code
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
                array_push($result, ['product_name' => '', 'remain_qty' => 0, 'supply_code' => '']);
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

    /**
     * @param null $id = 제품 id
     * @param array $data
     * @return Response
     * 반출 등록
     */
    public function update($id = null, array $data = [])
    {
        $this->validate($data, $this->updateRequired);

        $sql = "select remain_qty from automobile_stock_log
                    where stts = 'ACT' and product_id = {$id}
                    order by created_at desc limit 1";

        $remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];
        $out_qty = (int)$data['out_qty'];

        if ($out_qty > $remain_qty) {
            return new Response(403, [], '입고량을 초과할 수 없습니다.');
        }

        $out_qty = -$out_qty;
        $remain_qty = $remain_qty + $out_qty;

        $sql = "insert into automobile_stock_log set
                    product_id = {$id},
                    change_qty = {$out_qty},
                    remain_qty = {$remain_qty},
                    type = 3, ##반출타입
                    created_id = {$this->token['id']},
                    created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }
}
