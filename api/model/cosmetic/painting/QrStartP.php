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
                   d.name as product_name, b.process_date, b.box_qty, pc.name as type
                from process_order a
                inner join (select aa.process_order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            where bb.process_status = {$process_start}
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id
                            ) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                left join process_code pc
                on a.process_type = pc.code
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
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.process_order_id) b
                on a.id = b.process_order_id
                inner join `order` c
                on a.order_id = c.id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and c.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    public function show($id = null)
    {
        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select
                    cs.process_date, o.order_no, o.id as order_id, po.id as process_order_id,
                    pm.name as product_name, qc.qty, u.name as manager, pc.name as type,
                    po.code as process_code, @rownum:= @rownum+1 AS RNUM, pm.id as product_id
                from qr_code qc
                     inner join process_order po
                     on qc.process_order_id = po.id
                     inner join `order` o
                     on qc.order_id = o.id
                     inner join change_stts cs
                     on qc.id = cs.qr_id
                     inner join product_master pm
                     on qc.product_id = pm.id
                     left join process_code pc
                     on po.process_type = pc.code
                     inner join `user` u
                     on cs.created_id = u.id,
                     (SELECT @rownum:= 0) AS R
                where po.id = {$id}
                and cs.process_status = {$process_start}
                and qc.dept_id = {$dept_id}
                and cs.dept_id = {$dept_id}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function create(array $data = [])
    {
        if (!$data['id']) {
            return new Response(403, [], 'id 데이터가 존재하지 않습니다.');
        }

        $id = $data['id'];
        $process_start = Code::$PROCESS_START;

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
            "update box set
                lot_id = null
                where qr_id = {$id}
            "
        ];

        $this->setTransaction($sqls);
        return $this->qrShow($id);
    }

    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();
        (new QrBox)->isBox($id);

        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select process_stts from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];

        if ($result['process_stts'] === $process_start) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        $process_warehousing = Code::$PROCESS_WAREHOUSING;
        $process_release = Code::$PROCESS_RELEASE;

        $sql = "select o.order_no, pm.name as product_name, ifnull(a.asset_no, '') as asset_no,
                        qr.id, qr.process_stts, qr.qty, 1 as box_qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                left join asset a
                on qr.asset_id = a.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];
        $result['pre_item'] = false;

        /** @var  $sql
         *  선입선출을 체크한다.
         */
        $sql = "select w.in_date, w.group_id
                    from qr_code qc
                    inner join warehouse w
                    on qc.id = w.qr_id 
                where qc.id = {$id} 
                and qc.dept_id = {$dept_id}
                and qc.process_stts = {$process_warehousing}";

        $pre_item = $this->fetch($sql)[0];

        if (!$pre_item) {
            return new Response(403, [], '입고등록이 되지 않았습니다.');
        }

        $sql = "select count(*) as cnt
                    from qr_code qc
                    inner join warehouse w
                    on qc.id = w.qr_id
                    inner join box b
                    on qc.id = b.qr_id
                where qc.dept_id = {$dept_id}
                and qc.process_stts = {$process_warehousing}
                and w.in_date < '{$pre_item['in_date']}'
                and w.group_id != '{$pre_item['group_id']}'
                ";

        $count = $this->fetch($sql)[0]['cnt'];

        if ($count > 0) {
            $result['pre_item'] = true;
        }

        return new Response(200, $result, '');
    }

    public function qrShow ($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name, a.asset_no,
                        qr.id, qr.process_stts, qr.qty, 1 as box_qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                left join asset a
                on qr.asset_id = a.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];

        return new Response(200, $result);
    }
}
