<?php

class QrRest extends Model
{
    protected $updateRequired = [
        'qty' => 'integer'
    ];

    public function show($id = null)
    {
        $sql = "select o.order_no, pm.name as product_name, mm.name as material_name, mm.id as material_id,
                        a.name as asset_name, qr.id, qr.process_stts, qr.qty, mm.unit, 1 as box_qty
                from qr_code qr
                inner join `order` o
                on qr.order_id = o.id
                inner join product_master pm
                on qr.product_id = pm.id
                inner join material_master mm
                on qr.material_id = mm.id
                inner join asset a
                on qr.asset_id = a.id
                where qr.id = {$id}
                ";

        $result = $this->fetch($sql)[0];

        $process_start = Code::$PROCESS_START;

        if ($result['process_stts'] !== $process_start) {
            return new Response(403, [], '공정 시작 상태가 아닙니다.');
        }

        return new Response(200, $result);
    }

    public function update ($id = null, array $data = [])
    {
        $this->validate($data, $this->updateRequired);
        $this->isAvailableUser();
        $process_start = Code::$PROCESS_START;

        $sql = "select material_id, process_stts, qty from qr_code where id = {$id}";
        $result = $this->fetch($sql)[0];
        $pre_qty = $result['qty'];
        $process_stts = $result['process_stts'];
        $material_id = $result['material_id'];

        if ($process_start !== $process_stts) {
            return new Response(403, [], '공정 시작 중인 QR코드를 스캔해주세요.');
        }

        $sql = "select change_qty, remain_qty from material_stock_log 
                where material_id = {$material_id} order by created_at desc limit 1";

        $result = $this->fetch($sql)[0];

        $change_qty = (int)$data['qty'];
        $after_qty = (int)$pre_qty - $change_qty;
        $remain_qty = (int)$result['remain_qty'];
        $remain_qty += $change_qty;

        $sqls = [
            "update qr_code set
                qty = {$after_qty},
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}
                ",
            "insert into material_stock_log set 
                change_qty = {$change_qty},
                remain_qty = {$remain_qty},
                material_id = {$material_id},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
        ];

        $this->setTransaction($sqls);
    }
}
