<?php

class PaintingProcessSetting extends Model
{
    protected $table = 'painting_process_setting';

    protected $createRequired = [
        'process_order_id' => 'integer',
        'work_qty' => 'integer',
        'humidity_max' => 'integer',
        'humidity_min' => 'integer',
        'humidity_average' => 'integer',
        'conveyor_speed' => 'integer'
    ];

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);

        $sql = "select id from {$this->table} where process_order_id = {$data['process_order_id']}";
        $id = $this->fetch($sql)[0]['id'];

        if ($id) {
            return parent::update($id, $data);
        } else {
            return parent::create($data);
        }
    }
}
