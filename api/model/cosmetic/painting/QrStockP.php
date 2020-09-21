<?php

class QrStockP extends QrStock
{
    protected $sort = [
        'date' => 'process_date',
        'product' => 'product_name'
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

        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.box_qty, b.product_qty, c.order_no,
                   d.name as product_name, b.process_date, e.name as type
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_stock}
                            and bb.dept_id = {$dept_id}
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join process_code e
                on a.process_type = e.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id order by {$this->sorting($params['params'])}) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty,
                                    bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_stock} 
                            and aa.dept_id = {$dept_id}
                            and bb.dept_id = {$dept_id}
                            {$this->searchAsset($params)}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }
}
