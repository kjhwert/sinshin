<?php

class QrReleaseP extends QrRelease
{
    protected function getDeptId ()
    {
        return Dept::$PAINTING;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_release = Code::$PROCESS_RELEASE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no,
                   d.name as product_name, b.process_date, b.to_name, b.manager
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                    bb.process_date, dd.name as to_name, ee.name as manager
                            from qr_code aa
                            inner join (select * 
                                        from change_stts
                                        where process_status = {$process_release} 
                                        and dept_id = {$dept_id}
                                        order by created_at desc limit 18446744073709551615 ) bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            inner join `user` ee
                            on aa.created_id = ee.id
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id order by {$this->sorting($params['params'])}) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }
}
