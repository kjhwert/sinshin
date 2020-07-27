<?php

class AutoMProcess extends Model
{
    protected $table = 'automobile_process';
    protected $searchableText = 'b.customer_code';
    protected $searchableDate = 'a.created_at';
    protected $createRequired = [
        'input' => 'integer',
        'carrier' => 'string',
        'product_id' => 'integer',
        'rack' => 'integer',
        'charger' => 'string',
        'day_night' => 'string',
        'lot_no' => 'string',
        'mfr_date' => 'string',
        'input_date' => 'string',
        'comp_date' => 'string'
    ];

    protected $updateRequired = [
        'input' => 'integer',
        'output' => 'integer',
        'day_night' => 'string',
     ];

    protected $reversedSort = true;
    protected $sort = [
        'date' => 'a.created_at',
        'product' => 'b.name'
    ];

    /** 생산현황 index
     * @param array $params
     * @return Response
     */
    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = (int)$params["perPage"];
        $page = ((int)$params["page"] * $perPage);

        $sql = "
                select @rownum:= @rownum+1 AS RNUM, tot.* from (select a.id, a.lot_no, b.name as product_name, a.input, b.customer_code, b.supply_code,
                   a.output, ifnull(c.defect, 0) as defect, ifnull(sum(a.size_loss + a.trust_loss),0) as loss,
                   ifnull((a.input - sum(a.size_loss + a.trust_loss + c.defect + a.output)),0) as drop_qty,
                   a.charger, a.created_at, b.customer, b.supplier, a.mfr_date,
                   concat(b.brand_code,'/',b.car_code) as car_code, a.memo,
                   (case
                        when a.type = 1 then 'immutable'
                        when a.type = 0 then 'mutable'
                       end
                       ) as type,
                   ifnull(truncate((a.output/a.input)*100,1),0) as output_percent,
                   ifnull(truncate((sum(a.size_loss + a.trust_loss)/a.input)*100,1),0) as loss_percent,
                   ifnull(truncate((c.defect/a.input)*100,1),0) as defect_percent
                from automobile_process a
                     inner join automobile_master b
                                on a.product_id = b.id
                     left join (
                select sum(qty) as defect, process_id
                from automobile_defect_log
                where stts = 'ACT' group by process_id
                ) c
                               on a.id = c.process_id
                where a.stts = 'ACT' and b.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id
                order by a.type = 0 asc, {$this->sorting($params['params'])} ) as tot,
                (SELECT @rownum:= 0) AS R order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    public function defect_index (array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = (int)$params["perPage"];
        $page = ((int)$params["page"] * $perPage);

        $sql = "
                select a.id, a.lot_no, b.name as product_name, a.input, b.customer_code, b.supply_code,
                       a.output, ifnull(c.defect, 0) as defect, ifnull(sum(a.size_loss + a.trust_loss),0) as loss,
                       a.charger, a.created_at, b.customer, b.supplier, a.mfr_date,
                       ifnull((a.input - sum(a.size_loss + a.trust_loss + c.defect + a.output)),0) as drop_qty,
                       concat(b.brand_code,'/',b.car_code) as car_code,
                       ifnull(truncate((a.output/a.input)*100,1),0) as output_percent,
                       ifnull(truncate((sum(a.size_loss + a.trust_loss)/a.input)*100,1),0) as loss_percent,
                       ifnull(truncate((c.defect/a.input)*100,1),0) as defect_percent,
                       @rownum:= @rownum+1 AS RNUM
                from automobile_process a
                inner join automobile_master b
                            on a.product_id = b.id
                inner join (
                    select sum(qty) as defect, process_id
                    from automobile_defect_log
                    where stts = 'ACT' group by process_id
                ) c
                on a.id = c.process_id,
                (SELECT @rownum:= 0) AS R
                where a.stts = 'ACT' and b.stts = 'ACT'
                {$this->searchText($params['params'])} {$this->searchDate($params['params'])}
                group by a.id
                order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery(array $params = [])
    {
        return "select count(a.id) cnt
                from automobile_process a
                inner join automobile_master b
                on a.product_id = b.id
               ";
    }

    public function show($id = null)
    {
        $sql = "select a.product_id, b.name as product_name, a.lot_no, a.charger,
                   b.customer, b.supplier, a.input_date, a.comp_date, a.carrier,
                   a.package_manager, a.package_date, a.output_count, a.remain_count, a.as_part,
                   a.mfr_date, a.rack, a.input, a.output, a.day_night, a.trust_loss, a.size_loss, a.memo,
                   b.customer_code,
                   (case
                        when a.type = 1 then 'immutable'
                        when a.type = 0 then 'mutable'
                    end    
                    ) as type
                from automobile_process a
                inner join automobile_master b
                on a.product_id = b.id
                where a.stts = 'ACT' and b.stts = 'ACT' and a.id = {$id}";

        $result = $this->fetch($sql)[0];

        $sql = "select defect_id, qty from automobile_defect_log where process_id = {$id}";
        $data = $this->fetch($sql);

        $defects = [];
        $total_defect = 0;

        foreach ($data as $defect) {
            $total_defect += $defect['qty'];

            array_push($defects, [
                'id' => $defect['defect_id'],
                'qty' => $defect['qty']
            ]);
        }

        $result['total_defect'] = $total_defect;
        $result['defects'] = $defects;

        return new Response(200, $result, '');
    }

    public function create(array $data = [])
    {
        $data = $this->validate($data, $this->createRequired);
        $this->hasEnoughStock($data);

        $sql = "insert into {$this->table}
                set {$this->dataToString($data)},
                created_id = {$this->token['id']},
                created_at = SYSDATE()
            ";

        return new Response(200, $this->fetch($sql), '등록되었습니다.');
    }

    public function update($id = null, array $data = [])
    {
        $data = $this->validate($data, $this->updateRequired);
        $this->hasEnoughStock($data);
        $this->isProceeded($id);
        $this->isExceedInput($data);

        $sql = "select remain_qty from automobile_stock_log
                where product_id = {$data['product_id']}
                order by created_at desc";
        $remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];

        $input = -$data['input'];
        $remain = $remain_qty + $input;

        $sql = "select remain_qty from automobile_release_log
                where product_id = {$data['product_id']}
                order by created_at desc";

        $release_remain_qty = (int)$this->fetch($sql)[0]['remain_qty'];
        $output = $data['output'] + $release_remain_qty;

        $querys = [
            "update automobile_process set
                {$this->dataToString($data)},
                type = 1,
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
            where id = {$id}",
            "insert into automobile_stock_log set
                product_id = {$data['product_id']},
                change_qty = {$input},
                remain_qty = {$remain},
                created_id = {$this->token['id']},
                created_at = SYSDATE()",
            "insert into automobile_release_log set
                product_id = {$data['product_id']},
                change_qty = {$data['output']},
                remain_qty = {$output},
                created_id = {$this->token['id']},
                created_at = SYSDATE()"
        ];

        $defects = json_decode($data['defect']);

        foreach ($defects as $defect) {
            $d = (array)$defect;

            if (!$d['id'] || !$d['qty']) {
                continue;
            }

            $defect_id = $d['id'];
            $qty = $d['qty'];

            $sql = "insert into automobile_defect_log set
                        process_id = {$id},
                        defect_id = {$defect_id},
                        qty = {$qty},
                        created_id = {$this->token['id']},
                        created_at = SYSDATE()
                    ";

            array_push($querys, $sql);
        }

        return $this->setTransaction($querys);
    }

    public function updateMemo ($id, array $data = [])
    {
        $sql = "update {$this->table} set
                memo = '{$data['memo']}',
                updated_id = {$this->token['id']},
                updated_at = SYSDATE()
                where id = {$id}";

        return new Response(200, $this->fetch($sql),'수정되었습니다.');
    }

    public function destroy($id = null)
    {
        $sql = "select type from automobile_process where {$this->primaryKey} = {$id}";
        $type = $this->fetch($sql)[0]['type'];

        if ($type === 1) {
            return new Response(403, [], '완료된 공정은 삭제할 수 없습니다.');
        }

        $sql = "update {$this->table} set stts = 'DELETE' where {$this->primaryKey} = {$id}";
        return new Response(200, $this->fetch($sql), '삭제되었습니다.');
    }

    protected function isExceedInput (array $data = [])
    {
        $input = $data['input'];
        $output = $data['output'] + $data['trust_loss'] + $data['size_loss'];
        $defects = json_decode($data['defect']);

        foreach ($defects as $defect) {
            $d = (array)$defect;

            $output += $d['qty'];
        }

        if ($output > $input) {
            return new Response(403, [], '투입량을 초과할 수 없습니다.');
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

    protected function hasEnoughStock (array $data = [])
    {
        $sql = "select remain_qty from automobile_stock_log where product_id = {$data['product_id']} order by created_at desc limit 1";
        $remain_qty = $this->fetch($sql)[0]['remain_qty'];

        if ($data['input'] > $remain_qty) {
            return new Response(403, [], '재고가 충분하지 않습니다.');
        }
    }

    protected function dataToString (array $data = [])
    {
        unset($data['defect']);
        $filter = array_filter($data, function ($val, $key) {
            if(!$val || is_object($val) || is_array($val)) {
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
