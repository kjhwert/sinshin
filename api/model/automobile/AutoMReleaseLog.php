<?php

class AutoMReleaseLog extends Model
{
    protected $table = 'automobile_release_log';
    protected $createRequired = [
        'release_qty' => 'integer',
        'product_id' => 'integer'
    ];
    protected $searchableText = 'a.customer_code';

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (
                    select a.id, a.customer_code, a.supply_code, a.name as product_name,
                           a.brand_code, a.car_code,
                           a.customer, a.supplier, b.remain_qty, c.name
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
                    where a.stts = 'ACT' and c.stts = 'ACT' {$this->searchText($params['params'])}
                    order by b.created_at asc) tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(*) as cnt
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
                    where a.stts = 'ACT' and c.stts = 'ACT' {$this->searchText($params)}";
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
                         inner join automobile_release_log b
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

        $sql = "select a.customer_code, a.supply_code, a.name as product_name, b.id, b.memo,
                   a.customer, a.supplier, b.change_qty, b.remain_qty, b.created_at, c.name,
                   (case
                        when b.change_qty > 0 then '생산'
                        when b.change_qty < 0 then '출고'
                    end) as type,
                    a.brand_code, a.car_code,
                   @rownum:= @rownum+1 AS RNUM
                from automobile_master a
                inner join automobile_release_log b
                on a.id = b.product_id
                inner join user c
                on b.created_id = c.id,
                     (SELECT @rownum:= 0) AS R
                where a.id = {$params['id']} and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT'
                order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function releasable($id, array $data = [])
    {
        $sql = "select remain_qty from automobile_release_log 
                where product_id = {$id} 
                order by created_at desc limit 1";

        return new Response(200, $this->fetch($sql)[0]);
    }

    public function showMemo ($id, array $data = [])
    {
        $sql = "select * from {$this->table} where id = {$id}";

        return new Response(200, $this->fetch($sql)[0]);
    }

    public function release_list (array $params = [])
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

        $sql = "select count(a.id) as cnt from automobile_release_log a
                    inner join automobile_master b
                    on a.product_id = b.id
                    inner join user c
                    on a.created_id = c.id
                where change_qty < 0";
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

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                    from (
                        select b.brand_code, b.car_code,
                           b.customer_code, b.supply_code, b.name as product_name,
                           b.customer, b.supplier, abs(a.change_qty) as release_qty, a.created_at, c.name
                        from automobile_release_log a
                        inner join automobile_master b
                        on a.product_id = b.id
                        inner join user c
                        on a.created_id = c.id
                    where a.stts = 'ACT' 
                    and b.stts = 'ACT' 
                    and change_qty < 0 
                    order by a.created_at asc) tot,
                (SELECT @rownum:= 0) AS R 
                order by RNUM desc limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);
        $remain_qty = $this->hasEnoughStock($data);

        $release = -$data['release_qty'];
        $remain = $remain_qty - $data['release_qty'];

        $sql = "insert into {$this->table} set
                product_id = {$data['product_id']},
                change_qty = {$release},
                remain_qty = {$remain},
                created_id = {$this->token['id']},
                created_at = SYSDATE()";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }

    public function update($id = null, array $data = [])
    {
        $sql = "update {$this->table} set
                memo = '{$data['memo']}',
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}";

        return new Response(200, $this->fetch($sql), '수정되었습니다.');
    }

    protected function hasEnoughStock (array $data = [])
    {
        $sql = "select remain_qty from automobile_release_log where product_id = {$data['product_id']} order by created_at desc limit 1";
        $remain_qty = $this->fetch($sql)[0]['remain_qty'];

        if ($data['release_qty'] > $remain_qty) {
            return new Response(403, [], '재고가 충분하지 않습니다.');
        }

        return $remain_qty;
    }

}
