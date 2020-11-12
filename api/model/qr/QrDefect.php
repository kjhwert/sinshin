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
        'date' => 'd.start_date',
        'product' => 'product_name'
    ];

    protected $searchableText = 'product_name';
    protected $searchableDate = 'a.created_at';
    protected $searchableAsset = 'c.asset_id';
    protected $reversedSort = true;

    protected $injectionProcessCode = "'M'";
    protected $paintingProcessCode = "'D', 'D2', 'C', 'R', 'Q', 'C1', 'E'";
    protected $assembleProcessCode = "'F', 'F1', 'J', 'SS', 'G', 'H'";

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
                   ifnull(d.start_date,'') as start_date, ifnull(d.end_date,'') as end_date, ifnull(d.user_name,'') as manager,
                   p.display_name
                from process_order a
                inner join `order` o
                on a.order_id = o.id
                left join (
                    select sum(a.qty) as defect_qty, a.process_order_id, c.name as user_name,   
                            min(a.created_at) as start_date, max(a.created_at) as end_date
                    from cosmetics_defect_log a
                         inner join defect b
                         on a.defect_id = b.id
                         inner join user c
                         on a.created_id = c.id
                    where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and a.dept_id = {$dept_id}
                    {$this->searchDate($params['params'])}
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
                      and b.dept_id = {$dept_id}
                      and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                      {$this->searchAsset($params['params'])}
                    group by a.process_order_id) as p
                on a.id = p.process_order_id
                where a.stts = 'ACT' and o.stts = 'ACT'
                {$this->searchText($params['params'])}
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
                        select sum(a.qty) as defect_qty, a.process_order_id, min(a.created_at) as start_date, 
                                max(a.created_at) as end_date,
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
                               d.name as product_name, a.created_at
                        from qr_code a
                          inner join change_stts b
                          on a.id = b.qr_id
                          inner join asset c
                          on a.asset_id = c.id
                          inner join product_master d
                          on a.product_id = d.id
                          where b.process_status = {$process_complete}
                          and b.dept_id = {$dept_id}
                          and a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                          {$this->searchAsset($params)}
                        group by a.process_order_id) as p
                    on a.id = p.process_order_id
                    where a.stts = 'ACT'
                    {$this->searchDate($params)} {$this->searchText($params)}) as tot
                ";
    }

    public function tabletIndex (array $params = [])
    {
        if (!$params['order_no']) {
            return (new ErrorHandler())->typeNull('order_no');
        }

        $dept_id = $this->getDeptId();
        $process_start = Code::$PROCESS_START;

        $sql = "select a.asset_no, pm.name as product_name, o.order_no, po.id,
                    o.id as order_id, pm.id as product_id, aa.name as material_name, aa.qty, aa.unit
                    from process_order po
                    inner join `order` o
                    on po.order_id = o.id
                    inner join product_master pm
                    on po.product_code = pm.code
                    inner join asset a
                    on po.asset_id = a.id
                    inner join (
                        select qc.process_order_id, mm.name, mm.qty, mm.unit 
                        from qr_code qc
                        inner join change_stts cs
                        on qc.id = cs.qr_id
                        inner join material_master mm
                        on qc.material_id = mm.id
                        and cs.process_status = {$process_start}
                        and cs.dept_id = {$dept_id}
                        group by qc.process_order_id
                    ) aa
                    on po.id = aa.process_order_id
                where pm.name like '%{$params['product_name']}%'
                and po.process_type in ({$this->getDeptProcessType()})
                and o.order_no like '%{$params['order_no']}%'
                order by o.created_at desc";

        return new Response(200, $this->fetch($sql), '');
    }

    protected function getDeptProcessType () {
        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        switch ($this->token['dept_id']) {
            case $injection:
                return $this->injectionProcessCode;
            case $painting:
                return $this->paintingProcessCode;
            case $assemble:
                return $this->assembleProcessCode;
            default:
                return new Response(403, [], '출력 권한이 없습니다.');
        }
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select po.id, a.start_date, a.end_date, b.asset_name, o.order_no, b.product_name,
                   b.product_qty, a.defect_qty, round((a.defect_qty/b.product_qty)*100,1) as defect_percent,
                   a.manager, b.display_name, po.code as process_code
                from process_order po
                inner join `order` o
                on po.order_id = o.id
                inner join (
                    select sum(a.qty) as defect_qty, a.process_order_id, c.name as manager, 
                            min(a.created_at) as start_date, max(a.created_at) as end_date
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

    public function tabletShow ($id = null)
    {
        $dept_id = $this->getDeptId();
        $group_id = DefectGroup::$INJECTION;

        $sql = "select d.id, d.name, d.name_en, ifnull(c.qty,0) as qty
                from defect d
                left join (
                    select * from cosmetics_defect_log
                    where dept_id = {$dept_id}
                    and process_order_id = {$id}
                    ) c
                on d.id = c.defect_id
                where d.group_id = {$group_id}
                order by d.id asc";

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
