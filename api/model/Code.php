<?php


class Code extends Model
{
    protected $fields = ['id','name', 'name_en'];
    protected $table = 'code';

    public static $PROCESS_START = 38;
    public static $PROCESS_COMPLETE = 39;
    public static $PROCESS_STOCK = 40;
    public static $PROCESS_WAREHOUSING = 41;
    public static $PROCESS_RELEASE = 42;
    public static $QRTYPE_BOX = 43;
    public static $QRTYPE_LOT = 44;
    public static $QRTYPE_BAG = 45;

    /**
     * @param array $params
     * @return Response
     */
    public function index (array $params = [])
    {
        $sql = "select {$this->getFields()} from {$this->table} where group_id = {$params['group_id']} and stts = 'ACT'";
        return new Response(200, $this->fetch($sql), '');
    }
}
