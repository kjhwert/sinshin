<?php

class PaintingMain extends InjectionMain
{
    protected $table = 'process_order';

    protected $productRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (
                    select c.order_no, c.ord, c.jaje_code, d.name as product_name, a.id,
                a.order_date, a.request_date, a.qty as process_qty, ifnull(sum(b.qty),0) as product_qty,
                ifnull(ROUND((sum(b.qty)/a.qty)*100, 1),0) as process_percent, e.name as type,
                ifnull(p.work_qty,'') as work_qty, ifnull(p.humidity_max,'') as humidity_max, 
                ifnull(p.humidity_min,'') as humidity_min, ifnull(p.humidity_average,'') as humidity_average, 
                ifnull(p.conveyor_speed, '') as conveyor_speed
                from process_order a
                  inner join (select process_order_id, process_stts, qty
                                from qr_code
                            where process_stts >= {$process_complete} 
                            and stts = 'ACT'
                            and dept_id = {$dept_id}) b
                            on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join process_code e
                on a.process_type = e.code
                left join painting_process_setting p
                on a.id = p.process_order_id
                where a.stts = 'ACT'
                group by a.id order by {$params['params']['sort']} {$params['params']['order']}) as tot,
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
                from process_order a
                     inner join (select bb.id, bb.process_order_id, bb.asset_id, bb.process_stts
                                    from process_order aa
                                    inner join qr_code bb
                                    on aa.id = bb.process_order_id
                                    where aa.stts = 'ACT' 
                                    and bb.stts = 'ACT' 
                                    and bb.process_stts >= {$process_complete}
                                    and bb.dept_id = {$dept_id}
                                    group by aa.id) b
                        on a.id = b.process_order_id
                     inner join `order` c
                        on a.order_id = c.id
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
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no,
                   d.name as product_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where aa.process_stts = {$process_stock} and bb.process_status = {$process_stock} 
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                group by a.id order by process_date asc) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }
}
