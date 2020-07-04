<?php


class UserAuth extends Model
{
    protected $table = 'user_auth';
    protected $paging = false;

    public function show($id = null)
    {
        $sql = "select c.menu, c.function from user_auth as a
                    inner join user as b
                    on a.user_uid = b.id
                    left join (
                        select aa.auth_group_id, bb.menu, bb.function from auth_list as aa
                        left join auth_master as bb
                        on aa.auth_id = bb.id
                        where aa.stts = 'ACT' and bb.stts = 'ACT'
                    ) as c
                    on a.auth_group_id = c.auth_group_id
                where a.user_uid = {$id} and a.stts = 'ACT'";

        $result = $this->fetch($sql);

        $auth = [];
        foreach ($result as $item) {
            $tmp = array_values($item);
            if (array_key_exists($tmp[0],$auth)) {
                array_push($auth[$tmp[0]], $tmp[1]);
            } else {
                $auth[$tmp[0]] = [$tmp[1]];
            }
        }

        return $auth;
    }
}
