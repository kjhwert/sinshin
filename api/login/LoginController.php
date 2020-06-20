<?php

class LoginController
{
    protected $model;
    protected $params;

    public function __construct()
    {
        $this->params = (new Request())->getParams();
        $this->model = new Login();
    }

    public function login ()
    {
        $this->validate($this->params);
        $this->model->verification($this->params);
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
