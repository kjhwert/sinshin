<?php

class Package extends Model
{
    public function __construct()
    {
        $this->token = $this->tokenValidation();
        $this->db = ErpDatabase::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {

    }

    public function show($id = null)
    {
        $sql = "select apiKey from MES_ApiKey where systemName = 'hlab' order by createDate desc limit 1";
        $apiKey = $this->fetch($sql)[0]['apiKey'];

        $url = "http://erp.sinshin.co.kr/___/api/packing_instruction/{$id}?apikey={$apiKey}";

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json",
                'method'  => 'POST',
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        $result = (array)json_decode($result);
        $data = (array)$result['datas'][0];
        
        return new Response(200, $data, '');
    }
}
