<?php

require_once "model/Model.php";

class User extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $searchable = ['user_id'];
    protected $fields = ['user_id'];
}