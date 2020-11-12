<?php

class QrPutA extends QrPutP
{
    protected $searchableText = 'd.name';
    protected $searchableDate = 'b.process_date';

    protected function getDeptId ()
    {
        return Dept::$ASSEMBLE;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $dept_id = $this->getDeptId();

        $sql = "
            select tot.*, @rownum:= @rownum+1 AS RNUM 
            from (
                select b.box_qty, b.product_qty, o.order_no,
                       d.name as product_name, o.id
                from `order` o
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                            bb.process_date
                    from qr_code aa
                    inner join (
                        select * from change_stts
                            where process_status = {$process_warehousing}
                            and dept_id = {$dept_id}
                        order by created_at desc LIMIT 18446744073709551615
                     ) bb
                    on aa.id = bb.qr_id
                    where aa.stts = 'ACT' and bb.stts = 'ACT'
                    group by aa.order_id ) b
                on o.id = b.order_id
                inner join product_master d
                on o.product_code = d.code
                where o.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                order by {$this->sorting($params['params'])}) as tot,
            (SELECT @rownum:= 0) AS R
            order by RNUM desc
            limit {$page},{$perPage}";

        $results = $this->fetch($sql);

        foreach ($results as $key=>$value) {
            $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                    from (select pc.name as type, pm.name as product_name,
                           count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                           bb.process_date, cm.name as customer_name, u.name as manager,
                           po.code
                    from qr_code aa
                    inner join warehouse w
                    on aa.id = w.qr_id
                    inner join customer_master cm
                    on w.from_id = cm.id
                    inner join (
                        select *
                            from change_stts
                            where process_status = {$process_warehousing}
                            and dept_id = {$dept_id}
                        order by created_at desc limit 18446744073709551615
                    ) bb
                    on aa.id = bb.qr_id
                    inner join `user` u
                    on bb.created_id = u.id
                    inner join product_master pm
                    on aa.product_id = pm.id
                    inner join process_order po
                    on aa.process_order_id = po.id
                    left join process_code pc
                    on po.process_type = pc.code
                    where aa.stts = 'ACT'
                      and bb.stts = 'ACT'
                      and aa.order_id = {$value['id']}
                    group by aa.process_order_id) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                    ";

            $results[$key]['process_order'] = $this->fetch($sql);
        }

        return new Response(200, $results, '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $dept_id = $this->getDeptId();

        return "select count(o.id) cnt
                from `order` o
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                            bb.process_date
                    from qr_code aa
                    inner join (
                        select * from change_stts
                            where process_status = {$process_warehousing}
                            and dept_id = {$dept_id}
                        order by created_at desc LIMIT 18446744073709551615
                     ) bb
                    on aa.id = bb.qr_id
                    where aa.stts = 'ACT' and bb.stts = 'ACT'
                    group by aa.order_id ) b
                on o.id = b.order_id
                where o.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $dept_id = $this->getDeptId();

        $sql = "select
                    cs.process_date as put_date, o.order_no,
                    pm.name as product_name, qc.qty, wh.from_name, u.name as manager,
                    ifnull(cs.process_date, '외주') as process_date, pc.name as type,
                    po.code as process_code, @rownum:= @rownum+1 AS RNUM
                from qr_code qc
                inner join process_order po
                on qc.process_order_id = po.id
                left join process_code pc
                on po.process_type = pc.code
                inner join `order` o
                on qc.order_id = o.id
                inner join (
                    select * from change_stts
                    where dept_id = {$dept_id} and process_status = {$process_warehousing}
                ) cs
                on qc.id = cs.qr_id
                inner join product_master pm
                on qc.product_id = pm.id
                inner join `user` u
                on cs.created_id = u.id
                inner join (
                    select wh.qr_id, cm.name as from_name
                        from warehouse wh
                    inner join customer_master cm
                    on wh.from_id = cm.id
                ) wh
                on qc.id = wh.qr_id,
                (SELECT @rownum:= 0) AS R
                where qc.order_id = {$id}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }
}
