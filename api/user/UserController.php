<?php

class UserController extends Controller
{
    protected $model;
    protected $types = [
        "user_id" => "string",
        "user_pw" => "string",
        "name" => "string"
    ];

    protected function getModel()
    {
        $this->model = new User();
    }
}
