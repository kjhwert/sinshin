<?php

class QrLot extends Model
{
    protected $table = 'qr_code';
    protected $createRequired = [
      'print_qty' => 'integer'
    ];

    public function isLot (array $data = [])
    {
        $sql = "select id from lot where qr_id = {$data['qr_id']}";
        $id = $this->fetch($sql)[0]['id'];

        if (!$id) {
            return new Response(403, [], 'Lot QR코드를 스캔해주세요.');
        }

        $sql = "select o.order_no, pm.name as product_name, qc.id as qr_id,
                       qc.qty, 1 as box_qty, pm.id as product_id
                    from box as b
                    inner join qr_code qc
                    on b.qr_id = qc.id
                    inner join `order` o
                    on qc.order_id = o.id
                    inner join product_master pm
                    on qc.product_id = pm.id
                where lot_id = {$id}";

        return new Response(200, ['id'=>$id, 'products' => $this->fetch($sql)],'');
    }

    public function create(array $data = [])
    {
        $print_result = [];

        $qr_id = $this->createQrCode();
        $lot_id = $this->createLot($qr_id);

        return new Response(200, ['qr_id'=>$qr_id, 'lot_id'=>$lot_id]);
    }

    protected function createQrCode ()
    {
        $sql = "insert into {$this->table} set
                dept_id = {$this->token['dept_id']}, 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        $this->fetch($sql);
        return $this->db->lastInsertId();
    }

    protected function createLot ($qr_id)
    {
        $sql = "insert into lot set
                qr_id = {$qr_id},
                dept_id = {$this->token['dept_id']}, 
                created_id = {$this->token['id']},
                created_at = SYSDATE()
                ";

        $this->fetch($sql);
        return $this->db->lastInsertId();
    }
}
