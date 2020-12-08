<?php

class AssembleMain extends InjectionMain
{
    protected $table = 'process_order';

    protected $productRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    protected function getDeptId ()
    {
        return Dept::$ASSEMBLE;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (
                    select c.order_no, c.ord, c.jaje_code, d.name as product_name, c.id,
                c.order_date, c.request_date, c.qty as process_qty, ifnull(sum(b.qty),0) as product_qty,
                ifnull(ROUND((sum(b.qty)/c.qty)*100, 1),0) as process_percent
                from `order` c
                inner join (select order_id, process_stts, qty
                                from qr_code
                            where process_stts in ({$process_complete}, {$process_stock}) 
                            and stts = 'ACT'
                            and dept_id = {$dept_id}) b
                on c.id = b.order_id
                inner join product_master d
                on c.product_code = d.code
                where c.stts = 'ACT'
                group by c.id order by {$params['params']['sort']} {$params['params']['order']}, c.order_no, d.name) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql), '',$params['paging']);
    }

    protected function paginationQuery(array $params = [])
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from `order` a
                     inner join (select bb.id, bb.order_id, bb.asset_id, bb.process_stts
                                    from process_order aa
                                    inner join qr_code bb
                                    on aa.id = bb.order_id
                                    where aa.stts = 'ACT' 
                                    and bb.stts = 'ACT' 
                                    and bb.process_stts >= {$process_complete}
                                    and bb.dept_id = {$dept_id}
                                    group by aa.id) b
                        on a.id = b.order_id
                     inner join product_master d
                        on a.product_code = d.code
                where a.stts = 'ACT'";
    }

    public function show($id = null)
    {
        $dept_id = $this->getDeptId();
        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select o.order_no, pc.name as type, pm.name as product_name, po.code as process_code,
                       po.request_date, po.order_date, po.qty as process_qty, ifnull(sum(b.qty),0) as product_qty,
                       ifnull(ROUND((sum(b.qty)/po.qty)*100, 1),0) as process_percent,
                       p.work_qty, p.humidity_min, p.humidity_max, p.humidity_average, p.conveyor_speed
                    from process_order po
                    inner join (
                        select process_order_id, process_stts, qty
                        from qr_code
                        where process_stts >= {$process_complete}
                        and stts = 'ACT'
                        and dept_id = {$dept_id}) b
                    on po.id = b.process_order_id
                    inner join `order` o
                    on po.order_id = o.id
                    inner join product_master pm
                    on po.product_code = pm.code
                    left join process_code pc
                    on po.process_type = pc.code
                    left join painting_process_setting p
                    on po.id = p.process_order_id
                where po.id = {$id}
                ";

        return new Response(200, $this->fetch($sql)[0]);
    }

    public function stockIndex (array $params = [])
    {
        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select c.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no,
                   d.name as product_name, b.process_date
                from `order` c
                inner join (select aa.order_id, aa.id, aa.qty, bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where aa.process_stts = {$process_stock} and bb.process_status = {$process_stock} 
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on c.id = b.order_id
                inner join product_master d
                on c.product_code = d.code
                where c.stts = 'ACT' and d.stts = 'ACT'
                group by c.id order by process_date asc) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }
}
