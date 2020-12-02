<?php

class AssetMaster extends Model
{
    protected $table = 'asset';

    public function index(array $params = [])
    {
        $injection = Dept::$INJECTION;
        $sql = "select id, asset_no from asset where dept_id = {$injection} and type = 'M' order by asset_no";

        return new Response(200, $this->fetch($sql), '');
    }

}
