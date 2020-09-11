<?php

class QrStartP extends QrStart
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

        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.product_qty, c.order_no, c.jaje_code,
                   d.name as product_name, b.process_date
                from process_order a
                inner join (select aa.process_order_id, aa.id, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_start}
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id
                            ) b
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

    protected function paginationQuery (array $params = [])
    {
        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        return "select count(a.id) as cnt
                from process_order a 
                inner join (select aa.process_order_id, aa.id, aa.qty, bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_start}
                            and aa.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT' and e.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_start = Code::$PROCESS_START;

        $sql = "select
                    cs.process_date, o.order_no,
                       MIN(cs.process_date) as start_date, MAX(cs.process_date) as end_date,
                    pm.name as product_name, o.jaje_code, sum(qc.qty) as qty,
                    po.code as process_code
                from qr_code qc
                     inner join process_order po
                        on qc.process_order_id = po.id
                     inner join `order` o
                        on qc.order_id = o.id
                     inner join change_stts cs
                        on qc.id = cs.qr_id
                     inner join product_master pm
                        on qc.product_id = pm.id
                where po.id = {$id} and qc.process_stts = {$process_start} and cs.process_status = {$process_start}
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();
        $this->isDeptProcess($id);

        $process_start = Code::$PROCESS_START;

        $sql = "select process_stts from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];

        if ($result['process_stts'] === $process_start) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        $sqls = [
            "update qr_code set
                process_stts = {$process_start},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
                ",
            "insert into change_stts set
                qr_id = {$id},
                dept_id = {$this->token['dept_id']},
                process_status = {$process_start},
                process_date = SYSDATE(),
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
        ];

        $this->setTransaction($sqls);
        return $this->qrShow($id);
    }

    public function qrShow ($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name,
                        qr.id, qr.process_stts, qr.qty, 1 as box_qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];

        return new Response(200, $result);
    }
}
