<?php

class MaterialStock extends Model
{
    protected $table = 'material_stock';
    protected $createRequired = [
        'material_id' => 'integer',
        'qty' => 'integer',
        'stock_date' => 'string'
    ];
    protected $searchableText = 'a.code';
    protected $searchableDate = 'b.stock_date';

    public function warehouseIndex (array $params = [])
    {
        $params = $this->warehousePagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $material_type = $params['params']['material_type'];

        $sql = "select a.code, a.name, round((b.qty / a.qty),0) as qty, b.qty as total, a.unit, b.stock_date,
                       c.name as manager, @rownum:= @rownum+1 AS RNUM
                    from material_master a
                    inner join material_stock b
                    on a.id = b.material_id
                    inner join user c
                    on b.created_id = c.id,
                    (SELECT @rownum:= 0) AS R
                    where a.stts = 'ACT' and b.stts = 'ACT' {$this->getMaterialType($material_type)}
                    {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql),'', $params['paging']);
    }

    public function stockIndex(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $material_type = $params['params']['material_type'];

        $sql = "select @rownum:= @rownum+1 AS RNUM, tot.* from (
                       select a.id, a.code, a.name, a.type, a.model, round((c.remain_qty / a.qty),0) as remain_qty, a.unit,
                              remain_qty as total, c.created_at,
                              b.stock_date, e.name as manager
                       from material_master a
                        inner join (select * from (
                                      select material_id,created_id, stock_date from material_stock
                                      where stts = 'ACT'
                                      order by created_at desc LIMIT 18446744073709551615) aa
                                    group by aa.material_id ) b
                        on a.id = b.material_id
                        inner join (select * from (
                                      select material_id, remain_qty, created_at from material_stock_log
                                      where stts = 'ACT'
                                      order by created_at desc LIMIT 18446744073709551615) aa
                                    group by aa.material_id) c
                        on a.id = c.material_id
                        inner join customer_master d
                                   on a.supplier = d.id
                        inner join user e
                                   on b.created_id = e.id
                       where a.stts = 'ACT' and d.stts = 'ACT'
                       and e.stts = 'ACT' {$this->getMaterialType($material_type)} {$this->searchText($params['params'])}
                       order by c.created_at asc
                    ) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql),'', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(a.id) as cnt 
                from material_master a
                inner join (select * from (
                              select material_id,created_id, stock_date from material_stock
                              where stts = 'ACT'
                              order by created_at desc LIMIT 18446744073709551615) aa
                            group by aa.material_id ) b
                on a.id = b.material_id
                inner join (select * from (
                              select material_id, remain_qty from material_stock_log
                              where stts = 'ACT'
                              order by created_at desc LIMIT 18446744073709551615) aa
                            group by aa.material_id) c
                on a.id = c.material_id
                inner join customer_master d
                           on a.supplier = d.id
                inner join user e
                           on b.created_id = e.id
               where a.stts = 'ACT' and d.stts = 'ACT'
               and e.stts = 'ACT' and a.type = '{$params['material_type']}' {$this->searchText($params)}";
    }

    protected function getMaterialType ($type = null)
    {
        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;

        $m_injection = Code::$MATERIAL_INJECTION;
        $m_painting = Code::$MATERIAL_PAINTING;

        if ($this->token['dept_id'] === $injection) {
            return "and a.type = '{$m_injection}'";
        }

        if ($this->token['dept_id'] === $painting) {
            return "and a.type = '{$m_painting}'";
        }

        return "and a.type = '{$type}'";
    }

    /**
     * @param array $data
     * 원자재(사출, 도료) 입고등록
     * 수량은 입고 이력으로 관리
     */
    public function create(array $data = [])
    {
        $data = $this->validate($data, $this->createRequired);

        $sql = "select remain_qty from material_stock_log 
                where material_id = {$data['material_id']}
                order by created_at desc limit 1";
        $remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];

        $qty = $data['qty'] * 25;
        $remain = $qty + $remain_qty;

        $sqls = [
            "insert into {$this->table} set
                material_id = {$data['material_id']},
                stock_date = '{$data['stock_date']}',
                qty = {$qty},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
            "insert into material_stock_log set
                material_id = {$data['material_id']},
                change_qty = {$qty},
                remain_qty = {$remain},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            "
        ];

        return $this->setTransaction($sqls);
    }

    protected function warehousePaginationQuery (array $params = [])
    {
        return "select count(b.id) as cnt 
                from material_master a
                inner join material_stock b
                on a.id = b.material_id
                where a.stts = 'ACT' and b.stts = 'ACT' {$this->searchText($params)}";
    }

    protected function warehousePagination(array $params = [])
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

        $sql = $this->warehousePaginationQuery($params);
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
