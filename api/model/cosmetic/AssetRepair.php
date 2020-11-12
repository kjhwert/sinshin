<?php

class AssetRepair extends Model
{
    protected $table = 'asset_repair_log';
    protected $createRequired = [
        'asset_id' => 'integer',
        'manager' => 'string'
    ];

    protected $assets = "1,12,2,11,348,347,346,20,13,18,19,22,21,345,344,343,342,305,306,307,308,309,310,316,312,313,314";

    public function index(array $params = [])
    {
        $process_complete = Code::$PROCESS_COMPLETE;
        $injection = Dept::$INJECTION;

        $sql = "select a.id, a.asset_no, ifnull(q.order_no, '') order_no,
                   ifnull(q.code, '') code, ifnull(q.process_qty, 0) process_qty,
                   ifnull(q.complete_qty, 0) complete_qty,
                   ifnull(q.product_name, '-') product_name, ifnull(q.process_percent, 0) process_percent,
                   ifnull(r.repair_date, '-') repair_date,
                   ifnull(h.hydraulic_date, '-') hydraulic_date,
                   ifnull(l.lubricant_date, '-') lubricant_date,
                   ifnull(f.filter_date, '-') filter_date,
                   q.mold_code, q.cycle_time, q.shot_cnt, q.cavity
                from asset a
                left join (
                    select o.order_no order_no, po.code code, qc.asset_id asset_id,
                       qc.qty as complete_qty, pm.name as product_name,
                       ROUND((qc.qty/po.qty)*100, 1) as process_percent,
                       po.qty as process_qty, ifnull(m.code,'') as mold_code,
                       ifnull(m.cycle_time, '') as cycle_time,
                       ifnull(m.shot_cnt, '') as shot_cnt,
                       ifnull(m.cavity, '') as cavity
                    from process_order po
                    inner join (
                        select t.* from (
                            select tot.process_order_id, asset_id, process_date, sum(tot.qty) as qty from (
                                select qc.process_order_id, qc.qty, qc.asset_id, cs.process_date
                                    from qr_code qc
                                    inner join (
                                        select * from change_stts
                                        where process_status >= {$process_complete}
                                          and stts = 'ACT'
                                          and dept_id = {$injection}
                                        group by qr_id) cs 
                                        #qr_id가 완료에서 재고, 출고로 넘어가면 데이터가 그대로 나옴.
                                    on qc.id = cs.qr_id
                                order by cs.process_date desc ) tot 
                                #사출기에서 가장 최근에 작업된 데이터를 올려주기 위함
                                group by tot.process_order_id order by tot.process_date desc) t 
                                #group by로 인해 흐트러진 order를 다시 정렬
                            group by asset_id 
                            #asset id를 기준으로 그룹핑
                        ) qc
                    on po.id = qc.process_order_id
                    inner join `order` o
                    on po.order_id = o.id
                    inner join product_master pm
                    on po.product_code = pm.code
                    left join mold_master m
                    on po.mold_id = m.id
                    ) q
                on q.asset_id = a.id
                left join (
                    select a.repair_date, a.asset_id
                        from (
                            select * from asset_repair_log
                            where repair_date is not null
                            order by repair_date desc
                            limit 18446744073709551615
                    ) a
                    group by a.asset_id
                ) r
                on a.id = r.asset_id
                left join (
                    select a.hydraulic_date, a.asset_id
                        from (
                            select * from asset_repair_log
                            where hydraulic_date is not null
                            order by hydraulic_date desc
                            limit 18446744073709551615
                    ) a
                    group by a.asset_id
                ) h
                on a.id = h.asset_id
                left join (
                    select a.lubricant_date, a.asset_id
                        from (
                            select * from asset_repair_log
                            where lubricant_date is not null
                            order by lubricant_date desc
                            limit 18446744073709551615
                    ) a
                    group by a.asset_id
                ) l
                on a.id = l.asset_id
                left join (
                    select a.filter_date, a.asset_id
                        from (
                            select * from asset_repair_log
                            where filter_date is not null
                            order by filter_date desc
                            limit 18446744073709551615
                    ) a
                    group by a.asset_id
                ) f
                on a.id = f.asset_id
                where a.id in ({$this->assets})
                order by a.asset_no";

        $assetResults = $this->fetch($sql);
        $result = (new Woojin())->index();

        for($i = 0; $i < count($assetResults); $i++) {
            $assetResults[$i]['is_processing'] = $result[$i];
        }

        return new Response(200, $assetResults, '');
    }

    public function show($id = null, array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "select tot.*, @rownum:= @rownum+1 AS RNUM
                    from (select log.created_at, ifnull(log.repair_date,'-') repair_date, 
                                    ifnull(log.hydraulic_date,'-') hydraulic_date, 
                                    ifnull(log.lubricant_date,'-') lubricant_date, 
                                    ifnull(log.filter_date,'-') filter_date, 
                                    log.manager, a.asset_no
                                from asset_repair_log log 
                                inner join asset a
                                on log.asset_id = a.id
                                where log.asset_id = {$id} and log.stts = 'ACT' and a.stts = 'ACT'
                            order by log.created_at asc) tot,
                    (SELECT @rownum:= 0) AS R
                    order by RNUM desc
                limit {$page}, {$perPage}";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery(array $params = [])
    {
        return "select count(log.id) cnt 
                    from asset_repair_log log
                    inner join asset a
                    on log.asset_id = a.id
                    where a.id = {$params['id']}";
    }

    public function create(array $data = [])
    {
        $this->validate($data, $this->createRequired);

        $sql = "insert into {$this->table} set
                    {$this->dataToString($data)},
                    created_id = {$this->token['id']},
                    created_at = SYSDATE()
                ";

        return new Response(200, $this->fetch($sql), '등록 되었습니다.');
    }
}
