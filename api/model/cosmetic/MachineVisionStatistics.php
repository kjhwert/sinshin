<?php

class MachineVisionStatistics extends Model
{
    protected $table = 'machine_vision_statistics';
    protected $createRequired = [
        'input_qty' => 'integer',
        'defect_qty' => 'integer',
        'assemble_qty' => 'integer',
        'assemble_defect_qty' => 'integer'
    ];

    protected $dayIndexRequired = [
      'year' => 'integer',
      'month' => 'integer',
      'day' => 'integer'
    ];

    protected $monthIndexRequired = [
      'year' => 'integer',
      'month' => 'integer'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function dayStatisticIndex(array $params = [])
    {
        $this->validate($params, $this->dayIndexRequired);

        $sql = "select created_at
                    from machine_vision_defect_log
                where YEAR(created_at) = {$params['year']}
                and MONTH(created_at) = {$params['month']}
                and DAY(created_at) = {$params['day']}
                group by created_at order by created_at";

        $days = $this->fetch($sql);
        foreach ($days as $key=>$value) {
            $sql = "select d.code, d.name, ifnull(log.qty, 0) qty
                    from machine_vision_defect d
                    left join (
                        select code, sum(qty) qty
                            from machine_vision_defect_log
                        where created_at = '{$value['created_at']}'
                        group by code) log
                    on d.code = log.code";

            $days[$key]['defects'] = $this->fetch($sql);
        }

        return new Response(200, $days, '');
    }

    public function dayAverageIndex(array $params = [])
    {
        $this->validate($params, $this->dayIndexRequired);

        $sql = "select d.input_qty, d.production_qty, d.defect_qty, d.assemble_qty, d.assemble_defect_qty,
                   ifnull(round((d.defect_qty/d.input_qty)*100, 1),0) day_defect_percent,
                   ifnull(round((d.production_qty/d.input_qty)*100, 1),0) day_production_percent,
                   ifnull(round((d.assemble_qty/d.input_qty)*100, 1),0) day_assemble_percent,
                   ifnull(round((d.assemble_defect_qty/d.input_qty)*100, 1),0) day_assemble_defect_percent,
                   ifnull(round((m.defect_qty/m.input_qty)*100, 1),0) month_defect_percent,
                   ifnull(round((m.production_qty/m.input_qty)*100, 1),0) month_production_percent,
                   ifnull(round((m.assemble_qty/m.input_qty)*100, 1),0) month_assemble_percent,
                   ifnull(round((m.assemble_defect_qty/m.input_qty)*100, 1),0) month_assemble_defect_percent,
                   ifnull(round((d.defect_qty/d.input_qty)*100, 1),0)-ifnull(round((m.defect_qty/m.input_qty)*100, 1),0) defect_percent_point,
                   ifnull(round((d.production_qty/d.input_qty)*100, 1),0)-ifnull(round((m.production_qty/m.input_qty)*100, 1),0) production_percent_point,
                   ifnull(round((d.assemble_qty/d.input_qty)*100, 1),0)-ifnull(round((m.assemble_qty/m.input_qty)*100, 1),0) assemble_percent_point,
                   ifnull(round((d.assemble_defect_qty/d.input_qty)*100, 1),0)-ifnull(round((m.assemble_defect_qty/m.input_qty)*100, 1),0) assemble_defect_percent_point
                from
                    (
                    select
                        ifnull(sum(input_qty),0) input_qty,
                        ifnull(sum(production_qty),0) production_qty,
                        ifnull(sum(defect_qty),0) defect_qty,
                        ifnull(sum(assemble_qty),0) assemble_qty,
                        ifnull(sum(assemble_defect_qty),0) assemble_defect_qty
                        from machine_vision_statistics
                        where YEAR(created_at) = {$params['year']}
                          and MONTH(created_at) = {$params['month']}
                    ) m,
                     (select ifnull(sum(input_qty),0) input_qty,
                             ifnull(sum(production_qty),0) production_qty,
                             ifnull(sum(defect_qty),0) defect_qty,
                             ifnull(sum(assemble_qty),0) assemble_qty,
                             ifnull(sum(assemble_defect_qty),0) assemble_defect_qty
                      from machine_vision_statistics
                      where YEAR(created_at) = {$params['year']}
                        and MONTH(created_at) = {$params['month']}
                        and DAY(created_at) = {$params['day']}) d";

        $result = $this->fetch($sql)[0];

        $sql = "select d.code, d.name, ifnull(log.qty,0) qty,
                       #ifnull(day.day_total,0) day_total,
                       #ifnull(day.day_defect,0) day_defect,
                       ifnull(round((day.day_defect/day.day_total)*100,1),0) day_percent,
                       #ifnull(month.month_total,0) month_total,
                       #ifnull(month.month_defect,0) month_defect,
                       ifnull(round((month.month_defect/month.month_total)*100,1),0) month_percent,
                       ifnull(round((day.day_defect/day.day_total)*100,1),0)-ifnull(round((month.month_defect/month.month_total)*100,1),0)
                       percent_point
                    from machine_vision_defect d
                    left join (
                        select sum(qty) qty, code
                        from machine_vision_defect_log
                        where YEAR(created_at) = {$params['year']}
                          and MONTH(created_at) = {$params['month']}
                          and DAY(created_at) = {$params['day']}
                        group by code order by code) log
                    on d.code = log.code
                    left join (
                        select t.qty day_total, d.qty day_defect, d.code
                        from
                            (select ifnull(sum(qty),0) qty
                             from machine_vision_defect_log
                             where YEAR(created_at) = {$params['year']}
                               and MONTH(created_at) = {$params['month']}
                               and DAY(created_at) = {$params['day']}) t,
                            (select ifnull(sum(qty),0) qty, code
                             from machine_vision_defect_log
                             where YEAR(created_at) = {$params['year']}
                               and MONTH(created_at) = {$params['month']}
                               and DAY(created_at) = {$params['day']}
                             group by code order by code) d) day
                    on d.code = day.code
                    left join (
                        select t.qty month_total, d.qty month_defect, d.code
                        from
                            (select ifnull(sum(qty),0) qty
                             from machine_vision_defect_log
                             where YEAR(created_at) = {$params['year']}
                               and MONTH(created_at) = {$params['month']}) t,
                            (select ifnull(sum(qty),0) qty, code
                             from machine_vision_defect_log
                             where YEAR(created_at) = {$params['year']}
                               and MONTH(created_at) = {$params['month']}
                             group by code order by code) d) month
                    on d.code = month.code
                    ";

        $result['defects'] = $this->fetch($sql);

        return new Response(200, $result, '');
    }

    public function monthStatisticIndex(array $params = [])
    {
        $this->validate($params, $this->monthIndexRequired);

        $sql = "
                select td.day, td.month, td.year,
                        ifnull(input_qty, 0) input_qty,
                        ifnull(production_qty, 0) production_qty,
                        ifnull(defect_qty, 0) defect_qty,
                        ifnull(assemble_qty, 0) assemble_qty,
                        ifnull(assemble_defect_qty,0) assemble_defect_qty,
                        ifnull(round((mv.defect_qty/mv.input_qty)*100, 1),0) defect_percent,
                        ifnull(round((mv.production_qty/mv.input_qty)*100, 1),0) production_percent,
                        ifnull(round((mv.assemble_qty/mv.input_qty)*100, 1),0) assemble_percent,
                        ifnull(round((mv.assemble_defect_qty/mv.input_qty)*100, 1),0) assemble_defect_percent
                    from time_dimension td
                    left join (
                        select sum(input_qty) input_qty,
                               sum(production_qty) production_qty,
                               sum(defect_qty) defect_qty,
                               sum(assemble_qty) assemble_qty,
                               sum(assemble_defect_qty) assemble_defect_qty,
                               DAY(created_at) day
                        from machine_vision_statistics
                        where YEAR(created_at) = {$params['year']}
                          and MONTH(created_at) = {$params['month']}
                        group by DAY(created_at)
                        ) mv
                    on td.day = mv.day
                where td.year = {$params['year']} and td.month = {$params['month']}
                ";

        $results = $this->fetch($sql);

        foreach ($results as $key=>$value) {

                $sql = "select d.code, d.name,
                               #day.day_total,
                               ifnull(day.day_defect,0) qty,
                               ifnull(round((day.day_defect/day.day_total)*100, 1),0) defect_percent
                        from machine_vision_defect d
                        left join (
                            select t.qty day_total, d.qty day_defect, d.code
                            from
                                (select ifnull(sum(qty),0) qty
                                 from machine_vision_defect_log
                                 where YEAR(created_at) = {$value['year']}
                                   and MONTH(created_at) = {$value['month']}
                                   and DAY(created_at) = {$value['day']}) t,
                                (select ifnull(sum(qty),0) qty, code
                                 from machine_vision_defect_log
                                 where YEAR(created_at) = {$value['year']}
                                   and MONTH(created_at) = {$value['month']}
                                   and DAY(created_at) = {$value['day']}
                            group by code order by code) d) day
                        on d.code = day.code
                        ";

                $defects = $this->fetch($sql);
                $results[$key]['defects'] = $defects;
        }

        return new Response(200, $results, '');
    }

    public function monthAverageIndex(array $params = [])
    {
        $this->validate($params, $this->monthIndexRequired);

        $sql = "select id from machine_vision_statistics
                    where YEAR(created_at) = {$params['year']}
                      and MONTH(created_at) = {$params['month']}
                    group by DAY(created_at)
                ";

        $dayCount = count($this->fetch($sql));

        $sql = "select round(sum(input_qty)/{$dayCount},1) input_qty,
                       round(sum(production_qty)/{$dayCount},1) production_qty,
                       round(sum(defect_qty)/{$dayCount},1) defect_qty,
                       round(sum(assemble_qty)/{$dayCount},1) assemble_qty,
                       round(sum(assemble_defect_qty)/{$dayCount},1) assemble_defect_qty,
                       round((sum(defect_qty)/sum(input_qty))*100, 1) defect_percent,
                       round((sum(production_qty)/sum(input_qty))*100, 1) production_percent,
                       round((sum(assemble_qty)/sum(input_qty))*100, 1) assemble_percent,
                       round((sum(assemble_defect_qty)/sum(input_qty))*100, 1) assmeble_defect_percent
                from machine_vision_statistics
                where YEAR(created_at) = {$params['year']}
                  and MONTH(created_at) = {$params['month']}";

        $result = $this->fetch($sql)[0];

        $sql = "select d.code, d.name, ifnull(round(s.qty/{$dayCount},1),0) qty, ifnull(round(((s.qty/31)/429.2)*100,1),0) defect_percent
                from machine_vision_defect d
                 left join (
                     select sum(qty) qty, code
                        from machine_vision_defect_log
                        where YEAR(created_at) = {$params['year']}
                        and MONTH(created_at) = {$params['month']}
                     group by code order by code) s
                 on d.code = s.code";

        $result['defects'] = $this->fetch($sql);

        return new Response(200, $result, '');
    }

    public function show($id = null)
    {

    }

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);

        try {
            $this->db->beginTransaction();

            $production_qty = $data['input_qty'] - ($data['defect_qty'] + $data['assemble_qty'] + $data['assemble_defect_qty']);

            $sql = "insert into {$this->table} set 
                        input_qty = {$data['input_qty']},
                        production_qty = {$production_qty},
                        defect_qty = {$data['defect_qty']},
                        assemble_qty = {$data['assemble_qty']},
                        assemble_defect_qty = {$data['assemble_defect_qty']},
                        created_id = 1,
                        created_at = SYSDATE()
                    ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $statistic_id = $this->db->lastInsertId();

            foreach ($data['errors'] as $result) {
                $tmp = (array)$result;

                $key = key($tmp);
                $value = $tmp[$key];

                $sql = "insert into machine_vision_statistics_log set 
                            code = '{$key}',
                            qty = {$value},
                            statistic_id = {$statistic_id},
                            created_id = 1,
                            created_at = SYSDATE()
                        ";

                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            return new Response(403, [],'데이터 입력 중 오류가 발생하였습니다.');
        }

        return new Response(200, [], '등록되었습니다.');
    }

    public function update($id = null, array $data = [])
    {

    }
}
