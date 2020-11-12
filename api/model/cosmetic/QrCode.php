<?php

class QrCode extends Model
{
    protected $table = 'qr_code';
    protected $searchableText = 'qc.id';

    public function index(array $params = [])
    {
        $params = $this->pagination($params);

        $perPage = $params["perPage"];
        $page = ((int)$params["page"] * (int)$perPage);

        $sql = "
                select tot.*, @rownum:= @rownum+1 AS RNUM from (
                    select qc.id, ifnull(o.order_no,'') as order_no,
                       if(l.id is null, 'BOX', 'LOT') as qr_type,
                       ifnull(c.name, '') as process_name, d.name as dept_name, qc.created_at,
                       d.id as dept_id, c.id as process_id, u.name as manager, qc.qr_master_id
                    from qr_code qc
                    left join `order` o
                    on qc.order_id = o.id
                    left join lot l
                    on qc.id = l.qr_id
                    left join code c
                    on qc.process_stts = c.id
                    left join dept d
                    on qc.dept_id = d.id
                    inner join user u
                    on qc.created_id = u.id
                    where qc.stts = 'ACT' {$this->searchText($params['params'])}
                    order by qc.id asc) as tot,
                (SELECT @rownum:= 0) AS R
                order by RNUM desc
                limit {$page},{$perPage}
               ";

        return new Response(200, $this->fetch($sql), '', $params['paging']);
    }

    protected function paginationQuery (array $params = [])
    {
        return "select count({$this->primaryKey}) as cnt 
                from {$this->table} qc
                where qc.stts = 'ACT' {$this->searchText($params)}";
    }

    protected function searchText (array $params = [])
    {
        $search = $params['search'];

        if ($search) {
            if ($this->searchableText === null) {
                return (new ErrorHandler())->typeNull('searchableText');
            }

            return "and {$this->searchableText} = {$search}";
        } else {
            return "";
        }
    }

    public function show($id = null)
    {
        $sql = "select qc.id, ifnull(o.order_no,'') as order_no,
                   if(l.id is null, 'BOX', 'LOT') as qr_type,
                   ifnull(c.name, '') as process_name, d.name as dept_name,
                   ifnull(pm.name, '') as product_name,
                   ifnull(mm.name, '') as material_name, ifnull(mm.code, '') as jaje_code,
                   ifnull(mm.qty, '') as material_qty, qc.qty as product_qty,
                   ifnull(mm.unit, '') as material_unit,
                   ifnull(cs.process_date,'') as process_date, ifnull(cm.name,'') as from_name,
                   ifnull(a.asset_no, '') as asset_name,
                   qc.created_at
                from qr_code qc
                left join lot l
                on qc.id = l.qr_id
                left join `order` o
                on qc.order_id = o.id
                left join process_order po
                on qc.process_order_id = po.id
                left join product_master pm
                on qc.product_id = pm.id
                left join material_master mm
                on qc.material_id = mm.id
                left join customer_master cm
                on qc.from_id = cm.id
                left join asset a
                on qc.asset_id = a.id
                left join dept d
                on qc.dept_id = d.id
                left join code c
                on qc.process_stts = c.id
                left join (
                    select cs.process_date, cs.qr_id, d.name as dept_name, c.name as process_name
                        from change_stts cs
                        inner join dept d
                        on cs.dept_id = d.id
                        inner join code c
                        on cs.process_status = c.id
                    where qr_id = {$id}
                    order by process_date desc limit 1) cs
                on qc.id = cs.qr_id
                where qc.id = {$id}";

        return new Response(200, $this->fetch($sql)[0]);
    }
}
