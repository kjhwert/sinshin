<?php

class DefectMonitoring extends Model
{
    protected $yearlyIndexRequired = [
      'year' => 'integer'
    ];

    protected $monthlyIndexRequired = [
        'year' => 'integer',
        'month' => 'integer'
    ];

    public function yearlyIndex (array $params = [])
    {
        $this->validate($params, $this->yearlyIndexRequired);

        $injection = DefectGroup::$INJECTION;
        $painting = DefectGroup::$PAINTING;
        $assemble = DefectGroup::$ASSEMBLE;

        $sql = "
            select concat(td.month,'월') month, ifnull(i.qty, 0) injection_qty,
                    ifnull(p.qty, 0) painting_qty, ifnull(a.qty, 0) assemble_qty
                from time_dimension td
                left join (
                    select sum(cd.qty) qty, MONTH(cd.created_at) month
                        from cosmetics_defect_log cd
                        inner join defect d
                        on cd.defect_id = d.id
                    where d.group_id = {$injection} and YEAR(cd.created_at) = {$params['year']}
                    group by MONTH(cd.created_at)
                ) i
                on td.month = i.month
                left join (
                    select sum(cd.qty) qty, MONTH(cd.created_at) month
                    from cosmetics_defect_log cd
                             inner join defect d
                                        on cd.defect_id = d.id
                    where d.group_id = {$painting} and YEAR(cd.created_at) = {$params['year']}
                    group by MONTH(cd.created_at)
                ) p
                on td.month = p.month
                left join (
                    select sum(cd.qty) qty, MONTH(cd.created_at) month
                    from cosmetics_defect_log cd
                             inner join defect d
                                        on cd.defect_id = d.id
                    where d.group_id = {$assemble} and YEAR(cd.created_at) = {$params['year']}
                    group by MONTH(cd.created_at)
                ) a
                on td.month = a.month
            group by td.month
        ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function monthlyIndex (array $params = [])
    {
        $this->validate($params, $this->monthlyIndexRequired);

        $injection = DefectGroup::$INJECTION;
        $painting = DefectGroup::$PAINTING;
        $assemble = DefectGroup::$ASSEMBLE;

        $year = $params['year'];
        $thisMonth = (int)$params['month'];
        $preMonth = $thisMonth - 1;

        $injectionSql = "
            select d.id, d.name defect_name,
                    ifnull(pre_month.qty, 0) pre_month_qty,
                    ifnull(this_month.qty, 0) this_month_qty,
                    ifnull(round((ifnull(this_month.qty, 0)/ifnull(pre_month.qty, 0))*100, 1)-100,0) percent
                from defect d
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                        from cosmetics_defect_log cd
                            where YEAR(cd.created_at) = {$year}
                        and MONTH(cd.created_at) = {$preMonth}
                        group by cd.defect_id
                ) pre_month
                on d.id = pre_month.defect_id
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                    from cosmetics_defect_log cd
                    where YEAR(cd.created_at) = {$year}
                      and MONTH(cd.created_at) = {$thisMonth}
                    group by cd.defect_id
                ) this_month
                on d.id = this_month.defect_id
            where d.group_id = {$injection}
        ";
        $paintingSql = "
            select d.id, d.name defect_name,
                    ifnull(pre_month.qty, 0) pre_month_qty,
                    ifnull(this_month.qty, 0) this_month_qty,
                    ifnull(round((ifnull(this_month.qty, 0)/ifnull(pre_month.qty, 0))*100, 1)-100,0) percent
                from defect d
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                        from cosmetics_defect_log cd
                            where YEAR(cd.created_at) = {$year}
                        and MONTH(cd.created_at) = {$preMonth}
                        group by cd.defect_id
                ) pre_month
                on d.id = pre_month.defect_id
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                    from cosmetics_defect_log cd
                    where YEAR(cd.created_at) = {$year}
                      and MONTH(cd.created_at) = {$thisMonth}
                    group by cd.defect_id
                ) this_month
                on d.id = this_month.defect_id
            where d.group_id = {$painting}
        ";
        $assembleSql = "
            select d.id, d.name defect_name,
                    ifnull(pre_month.qty, 0) pre_month_qty,
                    ifnull(this_month.qty, 0) this_month_qty,
                    ifnull(round((ifnull(this_month.qty, 0)/ifnull(pre_month.qty, 0))*100, 1)-100,0) percent
                from defect d
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                        from cosmetics_defect_log cd
                            where YEAR(cd.created_at) = {$year}
                        and MONTH(cd.created_at) = {$preMonth}
                        group by cd.defect_id
                ) pre_month
                on d.id = pre_month.defect_id
                left join (
                    select sum(cd.qty) qty, cd.defect_id
                    from cosmetics_defect_log cd
                    where YEAR(cd.created_at) = {$year}
                      and MONTH(cd.created_at) = {$thisMonth}
                    group by cd.defect_id
                ) this_month
                on d.id = this_month.defect_id
            where d.group_id = {$assemble}
        ";

        $result['injection'] = $this->fetch($injectionSql);
        $result['painting'] = $this->fetch($paintingSql);
        $result['assemble'] = $this->fetch($assembleSql);

        return new Response(200, $result, '');
    }

    /**
     * @param null $id = defect id
     * @param array $params
     * 해당 불량유형의 올해 불량수량을 보여준다.
     */
    public function show($id = null, array $params = [])
    {
        $this->validate($params, $this->showRequired);

        $sql = "
            select td.month, ifnull(d.qty, 0) qty
                from time_dimension td
                left join (
                   select sum(qty) qty, MONTH(created_at) month
                        from cosmetics_defect_log
                        where defect_id = {$id} 
                        group by MONTH(created_at)
                ) d
                on td.month = d.month
            where td.year = {$params['year']} group by td.month
        ";

        return new Response(200, $this->fetch($sql), '');
    }

    protected function searchDay ($day = null)
    {
        if (!$day) {
            return "";
        }

        return "and DAY(log.created_at) = {$day}";
    }
}
