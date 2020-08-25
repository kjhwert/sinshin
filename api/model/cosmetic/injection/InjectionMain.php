<?php

class InjectionMain extends Model
{
    protected $table = 'process_order';

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (
                    select f.name as asset_name,c.order_no, c.ord, e.code as mold_code, c.jaje_code, d.name as product_name,
                       a.order_date, a.request_date, a.qty as process_qty, sum(b.qty) as product_qty,
                       ROUND((sum(b.qty)/a.qty)*100,1) as process_percent
                    from process_order a
                    inner join qr_code b
                    on a.id = b.process_order_id
                    inner join `order` c
                    on a.order_id = c.id
                    inner join product_master d
                    on a.product_id = d.id
                    left join mold_master e
                    on d.mold_id = e.id
                    left join asset f
                    on b.asset_id = f.id
                where a.stts = 'ACT' and b.stts = 'ACT' and b.process_stts >= {$process_complete}
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
                     inner join (select bb.id, bb.process_order_id, bb.asset_id, bb.process_stts
                                    from process_order aa
                                    inner join qr_code bb
                                    on aa.id = bb.process_order_id
                                    where aa.stts = 'ACT' and bb.stts = 'ACT' and bb.process_stts >= {$process_complete}
                                    group by aa.id) b
                        on a.id = b.process_order_id
                     inner join `order` c
                        on a.order_id = c.id
                     inner join product_master d
                        on a.product_id = d.id
                     left join mold_master e
                        on d.mold_id = e.id
                     left join asset f
                        on b.asset_id = f.id
                where a.stts = 'ACT'";
    }
}
