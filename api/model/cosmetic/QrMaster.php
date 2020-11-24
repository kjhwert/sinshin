<?php

class QrMaster extends Model
{
    protected $table = 'qr_master';

    public function index(array $params = [])
    {
        $sql = "select qm.name, qm.type, qm.is_outsourcing,
                        if(qm.dept_id = 4, '도장팀/조립팀', ifnull(d.name, '')) dept_name, 
                        qm.created_at, @rownum:= @rownum+1 AS RNUM
                    from {$this->table} qm
                    left join dept d
                    on qm.dept_id = d.id,
                    (SELECT @rownum:= 0) AS R
                    where qm.stts = 'ACT'
                    order by RNUM desc";

        return new Response(200, $this->fetch($sql), '');
    }
}
