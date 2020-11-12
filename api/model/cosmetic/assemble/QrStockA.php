<?php

class QrStockA extends QrStockP
{
    protected $sort = [
        'date' => 'process_date',
        'product' => 'product_name'
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

        $process_stock = Code::$PROCESS_STOCK;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select o.id, b.box_qty, b.product_qty, o.order_no,
                   d.name as product_name, b.process_date
                from `order` o
                inner join (select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_stock}
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id ) b
                on o.id = b.order_id
                inner join product_master d
                on o.product_code = d.code
                where d.stts = 'ACT' and o.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by o.id order by {$this->sorting($params['params'])}) as tot,
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

        return "select count(o.id) cnt
                from `order` o
                inner join (select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_stock}
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id ) b
                on o.id = b.order_id
                inner join product_master d
                on o.product_code = d.code
                where d.stts = 'ACT' and o.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }
}
