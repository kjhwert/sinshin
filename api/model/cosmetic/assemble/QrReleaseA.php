<?php

class QrReleaseA extends QrReleaseP
{
    protected function getDeptId ()
    {
        return Dept::$ASSEMBLE;
    }

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $process_release = Code::$PROCESS_RELEASE;
        $dept_id = $this->getDeptId();

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM 
                from (select o.id, b.box_qty, b.product_qty, o.order_no,
                   d.name as product_name, b.process_date, b.to_name, b.manager
                from `order` o
                inner join (select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                    bb.process_date, dd.name as to_name, ee.name as manager
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            inner join `user` ee
                            on aa.created_id = ee.id
                            where bb.process_status = {$process_release} 
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id) b
                on o.id = b.order_id
                inner join product_master d
                on o.product_code = d.code
                where d.stts = 'ACT'
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
        $process_release = Code::$PROCESS_RELEASE;
        $dept_id = $this->getDeptId();

        return "select count(o.id) as cnt
                from `order` o
                inner join (select aa.order_id, count(aa.id) as box_qty, sum(aa.qty) as product_qty, 
                                    bb.process_date, dd.name as to_name, ee.name as manager
                            from qr_code aa
                            inner join change_stts bb
                            on aa.id = bb.qr_id
                            inner join `release` cc
                            on aa.id = cc.qr_id
                            inner join customer_master dd
                            on cc.to_id = dd.id
                            inner join `user` ee
                            on aa.created_id = ee.id
                            where bb.process_status = {$process_release} 
                            and bb.dept_id = {$dept_id}
                            and aa.stts = 'ACT' and bb.stts = 'ACT'
                            group by aa.order_id) b
                on o.id = b.order_id
                inner join product_master d
                on o.product_code = d.code
                where d.stts = 'ACT'
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    protected function printBox($box_id = null)
    {
        $sql = "update box set lot_id = null where id = {$box_id}";
        $this->fetch($sql);

        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id,
                        cm.name as customer_name, cm.id as customer_id
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                    inner join `order` o
                    on qc.order_id = o.id
                    inner join customer_master cm
                    on o.supply_id = cm.id
                where a.id = {$box_id} and qc.process_stts = {$process_stock}";

        return new Response(200, $this->fetch($sql));
    }

    protected function printLotList($lot_id = null)
    {
        $process_stock = Code::$PROCESS_STOCK;
        $sql = "select 1 as box_qty, qc.qty, qc.id as qr_id, pm.name as product_name, pm.id as product_id,
                        cm.name as customer_name, cm.id as customer_id
                    from box a
                    inner join qr_code qc
                    on a.qr_id = qc.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                    inner join `order` o
                    on qc.order_id = o.id
                    inner join customer_master cm
                    on o.supply_id = cm.id
                where a.lot_id = {$lot_id} and a.process_status = {$process_stock}";

        return new Response(200, $this->fetch($sql));
    }

    /**
     * @param array $data
     * 출고할 제품의 상태를 변경한다.
     */
    public function create(array $data = [])
    {
        $this->isAvailableUser();

        $process_release = Code::$PROCESS_RELEASE;
        $group_id = $this->generateRandomString();

        if (count($data['qr_ids']) === 0) {
            return new Response(403, [], '박스가 없습니다.');
        }

        try {
            $this->db->beginTransaction();

            foreach ($data['qr_ids'] as $qr_id) {

                $this->isDeptProcess($qr_id);
                $sql = "select id from box where qr_id = {$qr_id}";
                $box_id = $this->fetch($sql)[0]['id'];

                $sql = "select order_id from qr_code where id = {$qr_id}";
                $order_id = $this->fetch($sql)[0]['order_id'];

                $sql = "select supply_id from `order` where id = {$order_id}";
                $to_id = $this->fetch($sql)[0]['supply_id'];

                /** Lot일 경우 pass */
                if (!$box_id) {
                    continue;
                }

                $sqls = [
                    "insert into `release` set
                     box_id = {$box_id},
                     qr_id = {$qr_id},
                     group_id = '{$group_id}',
                     to_id = {$to_id},
                     from_id = {$data['from_id']},
                     dept_id = {$this->token['dept_id']},
                     is_outsourcing = 'Y',
                     in_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update qr_code set
                     from_id = {$data['from_id']},
                     process_stts = {$process_release},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$qr_id}
                    ",
                    "insert into change_stts set
                     qr_id = {$qr_id},
                     dept_id = {$this->token['dept_id']},
                     process_status = {$process_release},
                     process_date = SYSDATE(),
                     created_id = {$this->token['id']},
                     created_at = SYSDATE()
                    ",
                    "update box set
                     process_status = {$process_release},
                     updated_id = {$this->token['id']},
                     updated_at = SYSDATE()
                     where id = {$box_id}
                    "
                ];

                foreach ($sqls as $query) {
                    try {
                        $stmt = $this->db->prepare($query);
                        $stmt->execute();
                    } catch (Exception $e) {
                        throw $e;
                    }
                }
            }
            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }

        return new Response(200, [],'출고되었습니다.');
    }
}
