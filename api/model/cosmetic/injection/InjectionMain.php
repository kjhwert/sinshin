<?php

class InjectionMain extends Model
{
    protected $table = 'process_order';
    protected $reversedSort = true;

    protected $productRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    protected $sort = [
        'order_date' => 'order_date',
        'process_percent' => 'process_percent',
        'asset' => 'asset_name'
    ];

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
                    select ifnull(f.asset_no,'') as asset_name, c.order_no, a.ord, ifnull(e.code,'') as mold_code, c.jaje_code, d.name as product_name,
                a.order_date, a.request_date, a.qty as process_qty, ifnull(b.qty,0) as product_qty, f.display_name,
                ifnull(ROUND((b.qty/a.qty)*100, 1),0) as process_percent
                from process_order a
                  inner join (select process_order_id, process_stts, sum(qty) as qty, asset_id
                                from qr_code
                            where process_stts in ({$process_complete}, {$process_stock}) 
                            and stts = 'ACT'
                            and dept_id = {$dept_id} 
                            group by process_order_id) b
                    on a.id = b.process_order_id
                  inner join `order` c
                    on a.order_id = c.id
                  inner join product_master d
                    on a.product_code = d.code
                  left join mold_master e
                    on d.mold_id = e.id
                  left join asset f
                    on a.asset_id = f.id
                 where a.stts = 'ACT'
                group by a.id order by {$this->sorting($params['params'])}, c.order_no, d.name) as tot,
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
                                    from qr_code bb
                                    where bb.stts = 'ACT' 
                                    and bb.process_stts >= {$process_complete}
                                    and bb.dept_id = {$dept_id}) b
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
                and a.order_status = 'P' 
                and a.process_type = 'M'
                and a.ord = 1";
    }

    public function stockIndex (array $params = [])
    {
        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, count(b.id) as box_qty, sum(b.qty) as product_qty, c.order_no,
                   d.name as product_name, b.asset_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date, cc.asset_no as asset_name
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join asset cc
                            on aa.asset_id = cc.id
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

    public function productIndex (array $params = [])
    {
        $this->validate($params, $this->productRequired);
        $put = Code::$PROCESS_WAREHOUSING;
        $start = Code::$PROCESS_START;
        $complete = Code::$PROCESS_COMPLETE;
        $release = Code::$PROCESS_RELEASE;
        $stock = Code::$PROCESS_STOCK;

        $dept_id = $this->getDeptId();
        $year = $params['year'];
        $month = $params['month'];

        $sql = "select 
                       put.qty as put_qty,
                       start.qty as start_qty,
                       comp.qty as complete_qty,
                       stock.qty as stock_qty,
                       defect.qty as defect_qty,
                       rel.qty as release_qty
                from
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as put
                        on qc.id = put.qr_id
                    where put.process_status = {$put}
                    and put.dept_id = {$dept_id}
                    and put.process_date >= '{$year}-{$month}-01'
                    and put.process_date < '{$year}-{$month}-31 23:59:59') as put,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as start
                        on qc.id = start.qr_id
                    where start.process_status = {$start}
                    and start.dept_id = {$dept_id}
                    and start.process_date >= '{$year}-{$month}-01'
                    and start.process_date < '{$year}-{$month}-31 23:59:59') as start,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as comp
                        on qc.id = comp.qr_id
                    where comp.process_status = {$complete}
                    and comp.dept_id = {$dept_id}
                    and comp.process_date >= '{$year}-{$month}-01'
                    and comp.process_date < '{$year}-{$month}-31 23:59:59') as comp,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                        left join change_stts as comp
                        on qc.id = comp.qr_id
                    where comp.process_status = {$stock}
                    and comp.dept_id = {$dept_id}
                    and comp.process_date >= '{$year}-{$month}-01'
                    and comp.process_date < '{$year}-{$month}-31 23:59:59') as stock,
                (select ifnull(sum(d.qty),0) as qty
                    from cosmetics_defect_log as d
                    where d.dept_id = {$dept_id}
                    and d.created_at >= '{$year}-{$month}-01'
                    and d.created_at < '{$year}-{$month}-31 23:59:59') as defect,
                (select ifnull(sum(qc.qty),0) as qty
                    from qr_code as qc
                         left join change_stts as rel
                         on qc.id = rel.qr_id
                    where rel.process_status = {$release}
                    and rel.dept_id = {$dept_id}
                    and rel.process_date >= '{$year}-{$month}-01'
                    and rel.process_date < '{$year}-{$month}-31 23:59:59') as rel
                ";

        return new Response(200, $this->fetch($sql)[0]);
    }
}
