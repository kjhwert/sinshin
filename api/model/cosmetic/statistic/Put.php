<?php

class Put extends Model
{
    protected $productIndexRequired = [
        'year' => 'integer'
    ];

    protected $materialIndexRequired = [
        'year' => 'integer'
    ];

    public function productIndex(array $params = [])
    {
        $this->validate($params, $this->productIndexRequired);

        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        $sql = "
                select concat(m.month,'월') as month,
                       ifnull(p.qty, 0) as painting,
                       ifnull(a.qty, 0) as assemble
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
                      select sum(qc.qty) as qty, MONTH(w.created_at) as month
                            from warehouse w
                            inner join qr_code qc
                            on w.qr_id = qc.id
                        where w.dept_id = {$painting}
                        and YEAR(w.created_at) = {$params['year']}
                        group by MONTH(w.created_at)
                      ) p
                on m.month = p.month
                left join (
                      select sum(qc.qty) as qty, MONTH(w.created_at) as month
                            from warehouse w
                            inner join qr_code qc
                            on w.qr_id = qc.id
                        where w.dept_id = {$assemble}
                        and YEAR(w.created_at) = {$params['year']}
                        group by MONTH(w.created_at)
                      ) a
                on m.month = a.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }

    public function materialIndex(array $params = [])
    {
        $this->validate($params, $this->materialIndexRequired);

        $injection = "IN";
        $painting = "CO";

        $sql = "
                select concat(m.month,'월') as month,
                   ifnull(i.qty,0) as injection,
                   ifnull(p.qty,0) as painting
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
                      select MONTH(ms.created_at) as month, ROUND(sum(ms.qty)/mm.qty) qty
                            from material_stock ms
                            inner join material_master mm
                            on ms.material_id = mm.id
                            where mm.type = '{$injection}'
                            and YEAR(ms.created_at) = {$params['year']}
                        group by MONTH(ms.created_at)
                      ) i
                on m.month = i.month
                left join (
                      select MONTH(ms.created_at) as month, ROUND(sum(ms.qty)/mm.qty) as qty
                            from material_stock ms
                            inner join material_master mm
                            on ms.material_id = mm.id
                            where mm.type = '{$painting}'
                            and YEAR(ms.created_at) = {$params['year']}
                        group by MONTH(ms.created_at)
                      ) p
                on m.month = p.month
                order by m.month
                ";

        return new Response(200, $this->fetch($sql), '');
    }
}
