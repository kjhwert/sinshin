<?php

class DefectMonitoring extends Model
{
    protected $indexRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    public function index(array $params = [])
    {
        $this->validate($params, $this->indexRequired);

        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        $sql = "
                select sum(log.qty) as qty, d.name as defect_name
                    from cosmetics_defect_log log
                    inner join defect d
                    on log.defect_id = d.id
                where YEAR(log.created_at) = {$params['year']}
                and MONTH(log.created_at) = {$params['month']}
                {$this->searchDay($params['day'])}
                and log.dept_id = {$injection}
                group by log.defect_id
                ";

        $injection_defect = $this->fetch($sql);
        if (count($injection_defect) === 0) {
            $result['injection'] = [
                [
                    "defect_name" => '없음',
                    'qty' => 0
                ]
            ];
        } else {
            $result['injection'] = $this->fetch($sql);
        }

        $sql = "
                select sum(log.qty) as qty, d.name as defect_name
                    from cosmetics_defect_log log
                    inner join defect d
                    on log.defect_id = d.id
                where YEAR(log.created_at) = {$params['year']}
                and MONTH(log.created_at) = {$params['month']}
                {$this->searchDay($params['day'])}
                and log.dept_id = {$painting}
                group by log.defect_id
                ";

        $painting_defect = $this->fetch($sql);
        if (count($painting_defect) === 0) {
            $result['painting'] = [
                [
                    "defect_name" => '없음',
                    'qty' => 0
                ]
            ];
        } else {
            $result['painting'] = $this->fetch($sql);
        }

        $sql = "
                select sum(log.qty) as qty, d.name as defect_name
                    from cosmetics_defect_log log
                    inner join defect d
                    on log.defect_id = d.id
                where YEAR(log.created_at) = {$params['year']}
                and MONTH(log.created_at) = {$params['month']}
                {$this->searchDay($params['day'])}
                and log.dept_id = {$assemble}
                group by log.defect_id
                ";

        $assemble_defect = $this->fetch($sql);
        if (count($assemble_defect) === 0) {
            $result['assemble'] = [
                [
                    "defect_name" => '없음',
                    'qty' => 0
                ]
            ];
        } else {
            $result['assemble'] = $this->fetch($sql);
        }

        return new Response(200, $result, '');
    }

    protected function searchDay ($day = null)
    {
        if (!$day) {
            return "";
        }

        return "and DAY(log.created_at) = {$day}";
    }
}
