<?php

class QrDefect extends Model
{
    protected $createRequired = [
        'order_id' => 'integer',
        'process_order_id' => 'integer',
        'product_id' => 'integer',
        'defect_id' => 'integer',
        'qty' => 'integer'
    ];

    protected $sort = [
        'date' => 'process_date',
        'product' => 'product_name'
    ];

    protected $searchableText = 'product_name';
    protected $searchableDate = 'd.created_at';
    protected $searchableAsset = 'c.asset_id';
    protected $reversedSort = true;

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, ifnull(d.defect_qty, 0) as defect_qty,
                   round((ifnull(d.defect_qty,0)/p.product_qty)*100,1) as defect_percent,
                   ifnull(p.product_qty,0) as product_qty, p.asset_name, o.order_no, p.product_name,
                   ifnull(d.created_at,'') as process_date, ifnull(d.user_name,'') as manager,
                   p.display_name
                from process_order a
                inner join `order` o
                on a.order_id = o.id
                left join (
                    select sum(a.qty) as defect_qty, a.process_order_id, a.created_at, c.name as user_name
                    from cosmetics_defect_log a
                         inner join defect b
                         on a.defect_id = b.id
                         inner join user c
                         on a.created_id = c.id
                    where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and a.dept_id = {$dept_id}
                    group by a.process_order_id) as d
                on a.id = d.process_order_id
                inner join (
                    select sum(a.qty) as product_qty, a.process_order_id, c.name as asset_name,
                           d.name as product_name, c.display_name
                    from qr_code a
                      inner join change_stts b
                      on a.id = b.qr_id
                      inner join asset c
                      on a.asset_id = c.id
                      inner join product_master d
                      on a.product_id = d.id
                      where b.process_status = {$process_complete}
                      and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                      {$this->searchAsset($params['params'])}
                    group by a.process_order_id) as p
                on a.id = p.process_order_id
                where a.stts = 'ACT' and o.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                order by {$this->sorting($params['params'])}) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery(array $params = [])
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        return "select count(tot.id) as cnt from (
                    select d.created_at, p.product_name, a.id
                    from process_order a
                    left join (
                        select sum(a.qty) as defect_qty, a.process_order_id, 
                        a.created_at, c.name as user_name
                        from cosmetics_defect_log a
                             inner join defect b
                             on a.defect_id = b.id
                             inner join user c
                             on a.created_id = c.id
                        where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and a.dept_id = {$dept_id}
                        group by a.process_order_id) as d
                    on a.id = d.process_order_id
                    inner join (
                        select sum(a.qty) as product_qty, a.process_order_id, c.name as asset_name,
                               d.name as product_name
                        from qr_code a
                          inner join change_stts b
                          on a.id = b.qr_id
                          inner join asset c
                          on a.asset_id = c.id
                          inner join product_master d
                          on a.product_id = d.id
                          where b.process_status = {$process_complete}
                          and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                          {$this->searchAsset($params)}
                        group by a.process_order_id) as p
                    on a.id = p.process_order_id
                    where a.stts = 'ACT'
                    {$this->searchDate($params)} {$this->searchText($params)}) as tot
                ";
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select po.id, a.created_at, b.asset_name, o.order_no, b.product_name,
                   b.product_qty, a.defect_qty, round((a.defect_qty/b.product_qty)*100,1) as defect_percent,
                   a.manager, b.display_name, po.code as process_code
                from process_order po
                inner join `order` o
                on po.order_id = o.id
                inner join (
                    select sum(a.qty) as defect_qty, a.process_order_id, c.name as manager, a.created_at
                    from cosmetics_defect_log a
                         inner join defect b
                         on a.defect_id = b.id
                         inner join user c
                         on a.created_id = c.id
                    where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and a.dept_id = {$dept_id}
                    group by a.process_order_id) a
                on po.id = a.process_order_id
                inner join (
                    select sum(a.qty) as product_qty, a.process_order_id, c.name as asset_name,
                           d.name as product_name, c.display_name
                    from qr_code a
                         inner join change_stts b
                         on a.id = b.qr_id
                         inner join asset c
                         on a.asset_id = c.id
                         inner join product_master d
                         on a.product_id = d.id
                    where b.process_status = {$process_complete}
                      and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                    group by a.process_order_id) b
                on po.id = b.process_order_id
                where po.id = {$id}";

        return new Response(200, $this->fetch($sql));
    }

    public function showDefect($id = null)
    {
        $sql = "select a.qty, b.name as defect_name
                from cosmetics_defect_log a
                inner join defect b
                on a.defect_id = b.id
                where a.process_order_id = {$id}";

        return new Response(200, $this->fetch($sql));
    }

    public function create(array $data = [])
    {
        $data = $this->validate($data, $this->createRequired);
        $this->isAvailableUser();

        $sql = "select id from cosmetics_defect_log
                where order_id = {$data['order_id']}
                and process_order_id = {$data['process_order_id']}
                and defect_id = {$data['defect_id']}
                ";

        $log_id = $this->fetch($sql)[0]['id'];

        if ($log_id) {
            $sql = "update cosmetics_defect_log set
                        qty = {$data['qty']},
                        updated_id = {$this->token['id']},
                        updated_at = SYSDATE()
                        where id = {$log_id}
                    ";
        } else {
            $sql = "insert into cosmetics_defect_log set
                        {$this->dataToString($data)},
                        dept_id = {$this->token['dept_id']},
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";
        }

        return new Response(200, $this->fetch($sql), '등록 되었습니다.');
    }
}
