<?php

class InjectionMain extends Model
{
    protected $table = 'process_order';

    protected $productRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (
                    select ifnull(f.name,'') as asset_name, c.order_no, c.ord, e.code as mold_code, c.jaje_code, d.name as product_name,
                a.order_date, a.request_date, a.qty as process_qty, ifnull(sum(b.qty),0) as product_qty,
                ifnull(ROUND((sum(b.qty)/a.qty)*100, 1),0) as process_percent
         from process_order a
                  left join (select process_order_id, process_stts, qty, asset_id
                                from qr_code
                            where process_stts >= {$process_complete} and stts = 'ACT') b
                            on a.id = b.process_order_id
                  inner join `order` c
                    on a.order_id = c.id
                  inner join product_master d
                    on a.product_code = d.code
                  left join mold_master e
                    on d.mold_id = e.id
                  left join asset f
                    on b.asset_id = f.id
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

        return "select count(a.id) as cnt
                from process_order a
                     left join (select bb.id, bb.process_order_id, bb.asset_id, bb.process_stts
                                    from process_order aa
                                    inner join qr_code bb
                                    on aa.id = bb.process_order_id
                                    where aa.stts = 'ACT' and bb.stts = 'ACT' and bb.process_stts >= {$process_complete}
                                    group by aa.id) b
                        on a.id = b.process_order_id
                     inner join `order` c
                        on a.order_id = c.id
                     inner join product_master d
                        on a.product_code = d.code
                     left join mold_master e
                        on d.mold_id = e.id
                     left join asset f
                        on b.asset_id = f.id
                where a.stts = 'ACT'";
    }

    public function stockIndex (array $params = [])
    {
        $process_stock = Code::$PROCESS_STOCK;
        $injection = Dept::$INJECTION;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no,
                   d.name as product_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.name as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
                            where aa.process_stts = {$process_stock} and bb.process_status = {$process_stock} 
                            and aa.dept_id = {$injection}
                            and aa.stts = 'ACT' and bb.stts = 'ACT') b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join material_master e
                on d.material_id = e.id
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT' and e.stts = 'ACT'
                group by a.id order by process_date asc) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function productIndex (array $params = [])
    {
        $this->validate($params, $this->productRequired);
        $start = Code::$PROCESS_START;
        $complete = Code::$PROCESS_COMPLETE;
        $release = Code::$PROCESS_RELEASE;

        $injection = Dept::$INJECTION;
        $year = $params['year'];
        $month = $params['month'];

        $sql = "select start.qty as start_qty,
                       comp.qty as complete_qty,
                       defect.qty as defect_qty,
                       rel.qty as release_qty
                from
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as start
                        on qc.id = start.qr_id
                    where start.process_status = {$start}
                    and qc.dept_id = {$injection}
                    and start.process_date >= '{$year}-{$month}-01'
                    and start.process_date < '{$year}-{$month}-31 23:59:59') as start,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as comp
                        on qc.id = comp.qr_id
                    where comp.process_status = {$complete}
                    and qc.dept_id = {$injection}
                    and comp.process_date >= '{$year}-{$month}-01'
                    and comp.process_date < '{$year}-{$month}-31 23:59:59') as comp,
                (select ifnull(sum(d.qty),0) as qty
                    from cosmetics_defect_log as d
                    where d.dept_id = {$injection}
                    and d.created_at >= '{$year}-{$month}-01'
                    and d.created_at < '{$year}-{$month}-31 23:59:59') as defect,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                         left join change_stts as rel
                         on qc.id = rel.qr_id
                    where rel.process_status = {$release}
                    and qc.dept_id = {$injection}
                    and rel.process_date >= '{$year}-{$month}-01'
                    and rel.process_date < '{$year}-{$month}-31 23:59:59') as rel
                ";

        return new Response(200, $this->fetch($sql)[0]);
    }
}
