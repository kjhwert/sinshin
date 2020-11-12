<?php

class Release extends Model
{
    protected $indexRequired = [
        'year' => 'integer'
    ];

    public function index(array $params = [])
    {
        $this->validate($params, $this->indexRequired);

        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        $sql = "
                select concat(m.month,'월') as month,
                   ifnull(i.qty,0) as injection,
                   ifnull(p.qty,0) as painting,
                   ifnull(a.qty,0) as assemble
                from
                  (SELECT 1 AS month
                    UNION SELECT 2 AS month
                    UNION SELECT 3 AS month
                    UNION SELECT 4 AS month
                    UNION SELECT 5 AS month
                    UNION SELECT 6 AS month
                    UNION SELECT 7 AS month
                    UNION SELECT 8 AS month
                    UNION SELECT 9 AS month
                    UNION SELECT 10 AS month
                    UNION SELECT 11 AS month
                    UNION SELECT 12 AS month) m
                left join (
                      select sum(qc.qty) as qty, MONTH(r.created_at) as month
                            from `release` r
                            inner join qr_code qc
                            on r.qr_id = qc.id
                        where r.dept_id = {$injection}
                        and YEAR(r.in_date) = {$params['year']}
                        group by MONTH(r.in_date)
                      ) i
                on m.month = i.month
                left join (
                      select sum(qc.qty) as qty, MONTH(r.created_at) as month
                            from `release` r
                            inner join qr_code qc
                            on r.qr_id = qc.id
                        where r.dept_id = {$painting}
                        and YEAR(r.in_date) = {$params['year']}
                        group by MONTH(r.in_date)
                      ) p
                on m.month = p.month
                left join (
                      select sum(qc.qty) as qty, MONTH(r.created_at) as month
                            from `release` r
                            inner join qr_code qc
                            on r.qr_id = qc.id
                        where r.dept_id = {$assemble}
                        and YEAR(r.in_date) = {$params['year']}
                        group by MONTH(r.in_date)
                      ) a
                on m.month = a.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }
}
