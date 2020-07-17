<?php


class AuthMaster extends Model
{
    protected $table = 'auth_master';
    protected $fields = ['id','menu','function'];
    protected $paging = false;
    
    public function create(array $data = [])
    {

    }

    public function update($id = null, array $data = [])
    {

    }

    public function destroy($id = null)
    {

    }
}
