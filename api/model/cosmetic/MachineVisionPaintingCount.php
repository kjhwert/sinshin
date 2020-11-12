<?php

class MachineVisionPaintingCount extends Model
{
    protected $table = "machine_vision_painting_count_output";

    protected $indexRequired = [
      'year' => 'integer',
      'month' => 'integer',
      'day' => 'integer'
    ];

    protected $inputCreateRequired = [
      'product_code' => 'string',
      'qty' => 'integer'
    ];

    protected $outputCreatedRequired = [
        'product_code' => 'string',
        'qty' => 'integer'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function index(array $params = [])
    {
        $this->validate($params, $this->indexRequired);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                    from (
                        select i.input_date, i.input_qty, o.output_qty, o.output_date,
                           (i.input_qty - o.output_qty) loss_qty, pm.name product_name
                        from product_master pm
                        inner join (
                            select MIN(i.created_at) input_date, sum(qty) input_qty, i.product_code
                            from machine_vision_painting_count_input i
                            where YEAR(i.created_at) = {$params['year']}
                              and MONTH(i.created_at) = {$params['month']}
                              and DAY(i.created_at) = {$params['day']}
                            group by i.product_code
                            ) i
                        on pm.code = i.product_code
                        inner join (
                            select MAX(o.created_at) output_date, sum(qty) output_qty, o.product_code
                            from machine_vision_painting_count_output o
                            where YEAR(o.created_at) = {$params['year']}
                              and MONTH(o.created_at) = {$params['month']}
                              and DAY(o.created_at) = {$params['day']}
                            group by o.product_code
                            ) o
                        on pm.code = o.product_code ) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    protected function paginationQuery(array $params = [])
    {
        return "select count(cnt) cnt 
                    from (
                        select count(id) cnt
                            from machine_vision_painting_count_output
                        group by product_name) tot";
    }

    public function inputCreate(array $data = [])
    {
        $this->validate($data, $this->inputCreateRequired);

        $sql = "insert into machine_vision_painting_count_input set
                    product_code = '{$data['product_code']}',
                    qty = {$data['qty']},
                    created_id = 1,
                    created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록 되었습니다.');
    }

    public function outputCreate(array $data = [])
    {
        $this->validate($data, $this->outputCreatedRequired);

        $sql = "insert into machine_vision_painting_count_output set
                    product_code = '{$data['product_code']}',
                    qty = {$data['qty']},
                    created_id = 1,
                    created_at = SYSDATE()";

        return new Response(200, $this->fetch($sql), '등록 되었습니다.');
    }

    public function update($id = null, array $data = [])
    {
        return parent::update($id, $data); // TODO: Change the autogenerated stub
    }
}
