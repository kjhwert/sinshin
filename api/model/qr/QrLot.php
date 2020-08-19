<?php

class QrLot extends Model
{
    protected $table = 'qr_code';
    protected $createRequired = [
      'print_qty' => 'integer'
    ];

    public function create(array $data = [])
    {
        $print_result = [];

        for($i = 0; $i < $data['print_qty']; $i++) {
            $qr_id = $this->createQrCode();
            $lot_id = $this->createLot($qr_id);

            array_push($print_result, ['qr_id'=>$qr_id, 'lot_id'=>$lot_id]);
        }

        return new Response(200, $print_result);
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
