<?php


class AuthList extends Model
{
    protected $table = 'auth_list';
    protected $fields = ['id', 'auth_id', 'auth_group_id'];
    protected $paging = false;
}
