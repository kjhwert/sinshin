<?php

class QrDefectP extends QrDefect
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }

    protected $searchableDate = 'a.created_at';

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
                   ifnull(p.product_qty,0) as product_qty, o.order_no, p.product_name,
                   ifnull(d.start_date,'') as start_date, ifnull(d.end_date, '') as end_date, ifnull(d.user_name,'') as manager,
                   pc.name as type
                from process_order a
                inner join `order` o
                on a.order_id = o.id
                left join (
                    select sum(a.qty) as defect_qty, a.process_order_id, a.created_at, c.name as user_name, 
                            min(a.created_at) as start_date, max(a.created_at) as end_date
                    from cosmetics_defect_log a
                         inner join defect b
                         on a.defect_id = b.id
                         inner join user c
                         on a.created_id = c.id
                    where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' and a.dept_id = {$dept_id}
                    {$this->searchDate($params['params'])}
                    group by a.process_order_id ) as d
                on a.id = d.process_order_id
                inner join (
                    select sum(a.qty) as product_qty, a.process_order_id,
                           d.name as product_name
                    from qr_code a
                      inner join change_stts b
                      on a.id = b.qr_id
                      inner join product_master d
                      on a.product_id = d.id
                      where b.process_status = {$process_complete}
                      and b.dept_id = {$dept_id}
                      and a.stts = 'ACT' and b.stts = 'ACT' and d.stts = 'ACT'
                    group by a.process_order_id) as p
                on a.id = p.process_order_id
                left join process_code pc
                on a.process_type = pc.code
                where a.stts = 'ACT' and o.stts = 'ACT'
                {$this->searchText($params['params'])}
                order by {$this->sorting($params['params'])}, o.order_no) as tot,
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
                        {$this->searchDate($params)}
                        group by a.process_order_id) as d
                    on a.id = d.process_order_id
                    inner join (
                        select sum(a.qty) as product_qty, a.process_order_id,
                               d.name as product_name
                        from qr_code a
                          inner join change_stts b
                          on a.id = b.qr_id
                          inner join product_master d
                          on a.product_id = d.id
                          where b.process_status = {$process_complete}
                          and b.dept_id = {$dept_id}
                          and a.stts = 'ACT' and b.stts = 'ACT' and d.stts = 'ACT'
                        group by a.process_order_id) as p
                    on a.id = p.process_order_id
                    where a.stts = 'ACT'
                    {$this->searchText($params)}) as tot
                ";
    }

    public function show($id = null)
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select po.id, a.start_date, a.end_date, o.order_no, b.product_name, e.name as type,
                   b.product_qty, a.defect_qty, round((a.defect_qty/b.product_qty)*100,1) as defect_percent,
                   a.manager, po.code as process_code
                from process_order po
                left join process_code e
                on po.process_type = e.code
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
                    select sum(a.qty) as product_qty, a.process_order_id,
                           d.name as product_name
                    from qr_code a
                         inner join change_stts b
                         on a.id = b.qr_id
                         inner join product_master d
                         on a.product_id = d.id
                    where b.process_status = {$process_complete}
                      and a.stts = 'ACT' and b.stts = 'ACT' and d.stts = 'ACT'
                    group by a.process_order_id) b
                on po.id = b.process_order_id
                where po.id = {$id}";

        return new Response(200, $this->fetch($sql));
    }

    public function tabletIndex (array $params = [])
    {
        if (!$params['order_no']) {
            return (new ErrorHandler())->typeNull('수주번호');
        }

        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select pm.name as product_name, o.order_no, po.id,
                    o.id as order_id, pm.id as product_id, pc.name as type
                    from process_order po
                    inner join `order` o
                    on po.order_id = o.id
                    inner join product_master pm
                    on po.product_code = pm.code
                    inner join (
                        select qc.order_id from qr_code qc
                            inner join change_stts cs
                            on qc.id = cs.qr_id
                            and cs.process_status = {$process_start}
                            and cs.dept_id = {$dept_id}
                        group by qc.order_id
                    ) aa
                    on o.id = aa.order_id
                    inner join process_code pc
                    on po.process_type = pc.code
                where pm.name like '%{$params['product_name']}%'
                and o.order_no like '%{$params['order_no']}%'
                order by o.created_at desc";

        return new Response(200, $this->fetch($sql), '');
    }

    public function tabletShow ($id = null)
    {
        $dept_id = $this->getDeptId();
        $group_id = DefectGroup::$PAINTING;

        $sql = "select d.id, d.name, d.name_en, ifnull(c.qty,0) as qty
                from defect d
                left join (
                    select * from cosmetics_defect_log
                    where dept_id = {$dept_id}
                    and process_order_id = {$id}
                    ) c
                on d.id = c.defect_id
                where d.group_id = {$group_id} and d.stts = 'ACT'
                order by d.id asc";

        return new Response(200, $this->fetch($sql));
    }

    public function showDefect($id = null)
    {
        $sql = "select a.qty, b.name as defect_name
                from cosmetics_defect_log a
                inner join defect b
                on a.defect_id = b.id
                where a.process_order_id = {$id} and b.stts = 'ACT'";

        return new Response(200, $this->fetch($sql));
    }
}
