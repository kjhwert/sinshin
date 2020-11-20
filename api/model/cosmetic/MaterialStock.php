<?php

class MaterialStock extends Model
{
    protected $table = 'material_stock';
    protected $createRequired = [
        'material_id' => 'integer',
        'qty' => 'integer',
        'stock_date' => 'string',
        'type' => 'string'
    ];
    protected $updateRequired = [
        'qty' => 'integer'
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
                    where a.stts = 'ACT' and b.stts = 'ACT' 
                    {$this->getMaterialType($material_type)}
                    {$this->searchName($params['params'])} 
                    {$this->searchDate($params['params'])}
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql),'', $params['paging']);
    }

    protected function warehousePaginationQuery (array $params = [])
    {
        $material_type = $params['material_type'];

        return "select count(*) as cnt 
                from material_master a
                inner join material_stock b
                on a.id = b.material_id
                where a.stts = 'ACT' and b.stts = 'ACT' 
                {$this->searchName($params)}
                {$this->searchDate($params)}
                {$this->getMaterialType($material_type)}
                ";
    }

    public function stockIndex(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $material_type = $params['params']['material_type'];

        $sql = "select @rownum:= @rownum+1 AS RNUM, tot.* from (
                       select a.id, a.code, a.name, a.type, a.model, round((c.remain_qty / a.qty),0) as remain_qty, 
                                a.unit, c.remain_qty as total, c.created_at, a.qty,
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
                       and e.stts = 'ACT' {$this->getMaterialType($material_type)} {$this->searchName($params['params'])}
                       order by c.created_at asc
                    ) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql),'', $params['paging']);
    }

    protected function searchName (array $params = [])
    {
        $search = $params['search'];

        if ($search) {
            return "and (a.name like '%{$search}%' or a.code like '%{$search}%')";
        } else {
            return "";
        }
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

        if($data['type'] === 'IN') { // 사출
            return $this->materialStockCreate($data);
        }

        if ($data['type'] === 'CO') { // 도장
            return $this->coatingStockCreate($data);
        }
    }

    protected function materialStockCreate (array $data = []) {
        if (!$data['lot_no']) {
            (new ErrorHandler())->typeNull('LOT_NO');
        }

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
            ",
            "insert into material_lot set
                material_id = {$data['material_id']},
                lot_no = '{$data['lot_no']}',
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            "
        ];

        return $this->setTransaction($sqls);
    }

    protected function coatingStockCreate (array $data = []) {
        $sql = "select remain_qty from material_stock_log 
                where material_id = {$data['material_id']}
                order by created_at desc limit 1";
        $remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];

        $qty = $data['qty'];
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
            ",
            "insert into material_lot set
                material_id = {$data['material_id']},
                lot_no = '{$data['lot_no']}',
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            "
        ];

        return $this->setTransaction($sqls);
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

    /**
     * @param null $id
     * @param array $data
     * @return Response
     * 도료 사용 등록
     */
    public function update($id = null, array $data = [])
    {
        $sql = "select log.change_qty, log.remain_qty, mm.type
                from material_master mm
                inner join material_stock_log log
                where mm.id = {$id} order by log.created_at desc limit 1
                ";

        $result = $this->fetch($sql)[0];

        if ($result['type'] === 'IN') {
            return (new ErrorHandler())->badRequest();
        }

        $remain_qty = (int)$result['remain_qty'];

        $change_qty = -(int)$data['qty'];
        $remain_qty = $remain_qty + $change_qty;

        if ($remain_qty < 0) {
            return new Response(403, [], '재고가 부족합니다.');
        }

        $sql = "insert into material_stock_log set 
                change_qty = {$change_qty},
                remain_qty = {$remain_qty},
                material_id = {$id},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ";

        return new Response(200, $this->fetch($sql), '등록 되었습니다.');
    }
}
