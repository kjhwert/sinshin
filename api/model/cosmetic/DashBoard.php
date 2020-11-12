<?php

class DashBoard extends Model
{
    public function weekIndex()
    {
        $now = strtotime("Now");
        $before = strtotime("-7 days");

        $now = date("Y-m-d H:i:s", $now);
        $before = date("Y-m-d", $before);

        $injection = Dept::$INJECTION;
        $painting = Dept::$PAINTING;
        $assemble = Dept::$ASSEMBLE;

        $process_complete = Code::$PROCESS_COMPLETE;

        $sql = "select td.year, td.month, td.day, 
                    ifnull(i.qty,0) injection, 
                    ifnull(p.qty,0) painting, 
                    ifnull(a.qty,0) assemble
                from time_dimension td
                left join (
                    select sum(qty) qty, DAY(cs.created_at) day, MONTH(cs.created_at) month
                    from qr_code qc
                             inner join (
                        select * from change_stts
                        where process_status >= {$process_complete}
                          and dept_id = {$injection}
                          and created_at >= '{$before}'
                        group by qr_id ) cs
                        on qc.id = cs.qr_id
                    group by DAY(cs.created_at)
                    ) i
                on td.day = i.day
                left join (
                    select sum(qty) qty, DAY(cs.created_at) day, MONTH(cs.created_at) month
                    from qr_code qc
                             inner join (
                        select * from change_stts
                        where process_status >= {$process_complete}
                          and dept_id = {$painting}
                          and created_at >= '{$before}'
                        group by qr_id ) cs
                        on qc.id = cs.qr_id
                    group by DAY(cs.created_at)
                ) p
                on td.day = p.day
                left join (
                    select sum(qty) qty, DAY(cs.created_at) day, MONTH(cs.created_at) month
                    from qr_code qc
                             inner join (
                        select * from change_stts
                        where process_status >= {$process_complete}
                          and dept_id = {$assemble}
                          and created_at >= '{$before}'
                        group by qr_id ) cs
                    on qc.id = cs.qr_id
                    group by DAY(cs.created_at)
                ) a
                on td.day = a.day
            where td.db_date > '{$before}' and td.db_date <= '{$now}'
            order by td.month, td.day";

        $data = $this->fetch($sql);
        $result = [];

        foreach ($data as $key=>$value) {
            $result['date'][$key] = ['day' => $value['day']];
            $result['injection'][$key] = ['qty' => $value['injection']];
            $result['painting'][$key] = ['qty' => $value['painting']];
            $result['assemble'][$key] = ['qty' => $value['assemble']];
        }

        /**
         *  그래프를 그리기 위해서는 8row 가 필요하기 때문에 마지막 데이터를 복사한다.
         */
        $result['injection'][7] = $result['injection'][6];
        $result['painting'][7] = $result['painting'][6];
        $result['assemble'][7] = $result['assemble'][6];

        return new Response(200, $result, '');
    }

    public function defectIndex()
    {
        $now = strtotime("Now");
        $now = date("Y-m-d", $now);

        $depts = [
            [
                'dept' => 'injection',
                'code' => Dept::$INJECTION
            ],
            [
                'dept' => 'painting',
                'code' => Dept::$PAINTING
            ],
            [
                'dept' => 'assemble',
                'code' => Dept::$ASSEMBLE
            ],
        ];

        $result = [];
        foreach ($depts as $dept) {

            $sql = "select ifnull(sum(qc.qty),0) qty
                    from qr_code qc
                    inner join (
                        select *
                        from change_stts
                        where dept_id = {$dept['code']}
                          and created_at >= '{$now}' and created_at < '{$now} 23:59:59'
                        group by qr_id
                        ) cs
                    on cs.qr_id = qc.id";
            $product_qty = $this->fetch($sql)[0]['qty'];

            $sql = "select ifnull(sum(qty),0) qty
                        from cosmetics_defect_log
                    where dept_id = {$dept['code']}
                    and created_at >= '{$now}' and created_at < '{$now} 23:59:59'";

            $defect_qty = $this->fetch($sql)[0]['qty'];

            $result[$dept['dept']] = [
                [
                    "country" => "양품률",
                    "litres" => $product_qty
                ],
                [
                    "country" => "불량률",
                    "litres" => $defect_qty
                ]
            ];
        }

        return new Response(200, $result, '');
    }
}
