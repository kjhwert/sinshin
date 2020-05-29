<?php

require_once "controller/Controller.php";
require_once "model/User/User.php";

class UserController extends Controller
{
    protected $model;
    protected $types = [
        "page" => "number",
        "perPage" => "number",
        "name" => "string",
        "email" => "string",
        "gender" => "boolean",
        "level" => "int"
    ];

    protected function getModel()
    {
        $this->model = new User();
    }
}