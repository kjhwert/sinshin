<?php

require_once "model/User/Login.php";

class LoginController
{
    protected $userNo;
    protected $userToken;
    protected $model;

    public function __construct()
    {
        $this->model = new Login();
    }

    public function login (array $params = [])
    {
        $this->validate($params);
        $this->model->verification($params);
    }

    protected function validate (array $data = [])
    {
        if (!$data || !array_key_exists('user_id', $data) || gettype($data['user_id']) !== 'string') {
            (new ErrorHandler())->typeNull('user_id');
        }

        if (!array_key_exists('user_pw', $data) || gettype($data['user_pw']) !== 'string') {
            (new ErrorHandler())->typeNull('user_pw');
        }
    }
}
