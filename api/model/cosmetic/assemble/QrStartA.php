<?php

class QrStartA extends QrStartP
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

        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select a.id, b.product_qty, a.order_no,
                   d.name as product_name, b.box_qty, a.id order_id
                from `order` a
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join (
                                select * 
                                from change_stts
                                where dept_id = {$dept_id}
                                and process_status = {$process_start}
                            ) bb
                            on aa.id = bb.qr_id
                            where aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id
                            ) b
                on a.id = b.order_id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id order by {$this->sorting($params['params'])}) as tot,
               (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
                ";

        $results = $this->fetch($sql);
        foreach ($results as $key=>$value) {
            $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                    from (select pc.name as type, pm.name as product_name,
                           count(aa.id) as box_qty, sum(aa.qty) as product_qty, po.code,
                           bb.process_date, u.name as manager
                        from qr_code aa
                        inner join (
                            select *
                                from change_stts
                                where process_status = {$process_start}
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
                        group by aa.process_order_id order by bb.process_date asc ) tot,
                        (SELECT @rownum:= 0) AS R
                        order by RNUM desc
                        ";

            $results[$key]['process_order'] = $this->fetch($sql);
        }

        return new Response(200, $results, '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        return "select count(a.id) cnt
                from `order` a
                inner join (
                    select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                bb.process_date
                            from qr_code aa
                            inner join (
                                select * 
                                from change_stts
                                where dept_id = {$dept_id}
                                and process_status = {$process_start}
                            ) bb
                            on aa.id = bb.qr_id
                            where aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id
                            ) b
                on a.id = b.order_id
                inner join product_master d
                on a.product_code = d.code
                where a.stts = 'ACT' and d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}
                group by a.id";
    }

    public function show($id = null)
    {
        $process_start = Code::$PROCESS_START;
        $dept_id = $this->getDeptId();

        $sql = "select
                    o.order_no, pm.name as product_name, qc.qty, u.name as manager, o.id order_id,
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
                    where dept_id = {$dept_id} and process_status = {$process_start}
                ) cs
                on qc.id = cs.qr_id
                inner join product_master pm
                on qc.product_id = pm.id
                inner join `user` u
                on cs.created_id = u.id,
                (SELECT @rownum:= 0) AS R
                where qc.order_id = {$id}
                order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql));
    }

    public function update($id = null, array $data = [])
    {
        $this->isAvailableUser();
        (new QrBox)->isBox($id);

        $process_start = Code::$PROCESS_START;
        $process_release = Code::$PROCESS_RELEASE;

        $sql = "select process_stts from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];

        if ($result['process_stts'] === $process_start) {
            return new Response(403, [], '이미 처리되었습니다.');
        }

        if ($result['process_stts'] === $process_release) {
            return new Response(403, [], '먼저 입고등록을 해주세요.');
        }

//        $sqls = [
//            "update qr_code set
//                process_stts = {$process_start},
//                updated_id = {$this->token['id']},
//                updated_at = SYSDATE()
//                where id = {$id}
//                ",
//            "insert into change_stts set
//                qr_id = {$id},
//                dept_id = {$this->token['dept_id']},
//                process_status = {$process_start},
//                process_date = SYSDATE(),
//                created_id = {$this->token['id']},
//                created_at = SYSDATE()
//            ",
//        ];
//
//        $this->setTransaction($sqls);
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
