<?php

class AutoMStock extends Model
{
    protected $table = 'automobile_stock';
    protected $searchableText = 'b.customer_code';
    protected $searchableDate = 'a.created_at';
    protected $createRequired = [
        'product_id' => 'integer',
        'mfr_date' => 'string',
        'input_date' => 'string',
        'store_qty' => 'integer'
    ];

    protected $updateRequired = [
        'id' => 'integer',
        'mfr_date' => 'string',
        'input_date' => 'string',
        'store_qty' => 'integer',
        'product_id' => 'integer',
        'visual_defect' => 'integer',
        'bing_defect' => 'integer',
        'type' => 'integer'
    ];

    protected $reversedSort = true;
    protected $sort = [
        'date' => 'a.created_at',
        'product' => 'b.name'
    ];

    public static $SUCCESS = 1;
    public static $FAIL = 0;
    public static $UNPROCEED = 2;

    public function index(array $params = [])
    {
        $params = $this->pagination($params);
        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select @rownum:= @rownum+1 AS RNUM, tot.* 
                    from (select a.id, concat(b.brand_code,'/',b.car_code) as car_code, b.customer_code, b.supply_code, b.name as product_name,
                       a.store_qty, sum(a.bing_defect + a.visual_defect) as loss,
                       b.customer, b.supplier, c.name as charger, a.created_at,
                       (case
                            when a.type = 1 then '합격'
                            when a.type = 0 then '불합격'
                            when a.type = 2 then '미처리'
                           end) as type
                    from automobile_stock a
                    inner join automobile_master b
                    on a.product_id = b.id
                    inner join user c
                    on a.created_id = c.id
                where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id having loss is not null
                order by {$this->sorting($params['params'])}
                ) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function show($id = null)
    {
        $sql = "select a.id, b.name as product_name, a.product_id, b.customer, b.supplier,
                b.customer_code, b.supply_code, concat(b.brand_code,'/', b.car_code) as car_code,
                a.store_qty, a.visual_defect, a.bing_defect, a.type, a.mfr_date, a.input_date
                from automobile_stock a
                inner join automobile_master b
                on a.product_id = b.id
                where a.id = {$id} and a.stts = 'ACT' and b.stts = 'ACT'";

        return new Response(200, $this->fetch($sql));
    }

    public function update($id = null, array $data = [])
    {
        $this->isProceeded($id);
        $data = $this->validate($data, $this->updateRequired);
        $this->isNotSuccessType($id, $data);

        $input = ((int)$data['store_qty']) - ((int)$data['visual_defect'] + (int)$data['bing_defect']);

        $sql = "select remain_qty from automobile_stock_log 
                where product_id = {$data['product_id']}
                order by created_at desc limit 1";
        $remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];
        $remain = $input + $remain_qty;

        /** @var  $data
         *  Transaction
         *  1. 재고 히스토리 쌓기
         *  2. 재고 업데이트 하기
         */
        $data = [
            "insert into automobile_stock_log set 
                product_id = {$data['product_id']},
                change_qty = {$input},
                remain_qty = {$remain},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ",
            "update {$this->table} set {$this->dataToString($data)} where {$this->primaryKey} = {$id}"
        ];

        return $this->setTransaction($data);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count(a.id) as cnt 
                from {$this->table} a
                inner join automobile_master b
                    on a.product_id = b.id
                inner join user c
                    on a.created_id = c.id
                where a.stts = 'ACT' and b.stts = 'ACT' and c.stts = 'ACT' 
                {$this->searchText($params)} {$this->searchDate($params)}";
    }

    protected function isNotSuccessType ($id, array $data = [])
    {
        if($data['type'] !== self::$SUCCESS) {
            $sql = "update {$this->table} set {$this->dataToString($data)} where {$this->primaryKey} = {$id}";
            return new Response(200, $this->fetch($sql),'수정되었습니다.');
        }
    }

    protected function isProceeded ($id)
    {
        $sql = "select type from {$this->table} where {$this->primaryKey} = {$id}";
        $type = $this->fetch($sql)[0]['type'];

        if ($type === 1) {
            return new Response(403, [], '이미 처리된 재고입니다.');
        }
    }

    protected function dataToString (array $data = [])
    {
        $filter = array_filter($data, function ($val, $key) {
            if(is_object($val) || is_array($val)) {
                return;
            }

            return $key !== $this->primaryKey;
        },ARRAY_FILTER_USE_BOTH);

        return implode(', ',array_map(function ($key, $value) {
            if (gettype($value) === "integer") {
                return "{$key} = {$value}";
            }

            return "{$key} = \"{$value}\"";
        }, array_keys($filter), $filter));
    }
}
