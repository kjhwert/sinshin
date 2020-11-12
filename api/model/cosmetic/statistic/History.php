<?php

class History extends Model
{
    protected $productRequired = [
        'year' => 'integer'
    ];

    protected $defectRequired = [
        'year' => 'integer'
    ];

    protected $productShowRequired = [
        'year' => 'integer'
    ];

    protected $defectShowRequired = [
        'year' => 'integer'
    ];

    public function productIndex(array $params = [])
    {
        $this->validate($params, $this->productRequired);
        $process_complete = Code::$PROCESS_COMPLETE;
        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        $sql = "select concat(m.month,'월') as month,
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
                      select MONTH(cs.process_date) as month, sum(cs.qty) as qty
                      from
                          (select cs.process_date, qc.qty
                           from change_stts cs
                                    inner join dept d
                                               on cs.dept_id = d.id
                                    inner join qr_code qc
                                               on cs.qr_id = qc.id
                           where cs.process_status >= {$process_complete}
                             and YEAR(cs.process_date) =  {$params['year']}
                             and cs.dept_id = {$injection}
                           group by cs.qr_id) cs
                      group by MONTH(cs.process_date)
                      ) i
                on m.month = i.month
                left join (
                      select MONTH(cs.process_date) as month, sum(cs.qty) as qty
                      from
                          (select cs.process_date, qc.qty
                           from change_stts cs
                                    inner join dept d
                                               on cs.dept_id = d.id
                                    inner join qr_code qc
                                               on cs.qr_id = qc.id
                           where cs.process_status >= {$process_complete}
                             and YEAR(cs.process_date) =  {$params['year']}
                             and cs.dept_id = {$painting}
                           group by cs.qr_id) cs
                      group by MONTH(cs.process_date)
                      ) p
                on m.month = p.month
                left join (
                      select MONTH(cs.process_date) as month, sum(cs.qty) as qty
                      from
                          (select cs.process_date, qc.qty
                           from change_stts cs
                                    inner join dept d
                                               on cs.dept_id = d.id
                                    inner join qr_code qc
                                               on cs.qr_id = qc.id
                           where cs.process_status >= {$process_complete}
                             and YEAR(cs.process_date) =  {$params['year']}
                             and cs.dept_id = {$assemble}
                           group by cs.qr_id) cs
                      group by MONTH(cs.process_date)
                      ) a
                on m.month = a.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function defectIndex(array $params = [])
    {
        $this->validate($params, $this->defectRequired);

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
                      select MONTH(cs.created_at) as month, sum(cs.qty) as qty
                      from
                          (select log.created_at, log.qty
                           from cosmetics_defect_log log
                           where YEAR(log.created_at) = {$params['year']}
                             and log.dept_id = {$injection}) cs
                      group by MONTH(cs.created_at)
                      ) i
                on m.month = i.month
                left join (
                      select MONTH(cs.created_at) as month, sum(cs.qty) as qty
                      from
                          (select log.created_at, log.qty
                           from cosmetics_defect_log log
                           where YEAR(log.created_at) = {$params['year']}
                             and log.dept_id = {$painting}) cs
                      group by MONTH(cs.created_at)
                      ) p
                on m.month = p.month
                left join (
                      select MONTH(cs.created_at) as month, sum(cs.qty) as qty
                      from
                          (select log.created_at, log.qty
                           from cosmetics_defect_log log
                           where YEAR(log.created_at) = {$params['year']}
                             and log.dept_id = {$assemble}) cs
                      group by MONTH(cs.created_at)
                      ) a
                on m.month = a.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function productShow($id = null, array $params = [])
    {
        $this->validate($params, $this->productShowRequired);

        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select concat(m.month,'월') as month,
                       ifnull(i.qty,0) qty
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
                      select MONTH(cs.process_date) as month, sum(cs.qty) as qty
                        from
                            (select cs.process_date, qc.qty
                             from change_stts cs
                                  inner join dept d
                                     on cs.dept_id = d.id
                                  inner join qr_code qc
                                     on cs.qr_id = qc.id
                             where cs.process_status >= {$process_complete}
                                and YEAR(cs.process_date) =  {$params['year']}
                                and qc.product_id = {$id}
                             group by cs.qr_id) cs
                        group by MONTH(cs.process_date)
                      ) i
                on m.month = i.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function defectShow($id = null, array $params = [])
    {
        $this->validate($params, $this->defectShowRequired);

        $sql = "
                select concat(m.month,'월') as month,
                   ifnull(i.qty,0) as qty
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
                      select MONTH(cs.created_at) as month, sum(cs.qty) as qty
                      from
                          (select log.created_at, log.qty
                           from cosmetics_defect_log log
                           where YEAR(log.created_at) = {$params['year']}
                             and log.product_id = {$id}) cs
                      group by MONTH(cs.created_at)
                      ) i
                on m.month = i.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }
}
