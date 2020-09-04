<?php

class QrBox extends Model
{
    protected $table = 'box';

    public function isBox ($id = null)
    {
        $sql = "select id from box where qr_id = {$id}";
        $id = $this->fetch($sql)[0]['id'];

        if (!$id) {
            return new Response(403, [], 'Box QR코드를 스캔해주세요.');
        }
    }
}
