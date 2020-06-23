<?php


class CodeController extends Controller
{
    public function __construct()
    {
        $this->tokenValidation();

        $req = new Request();
        $this->params = $req->getParams();

        switch ($this->params['type']) {
            case 'deptGroup' : $this->model = new DeptGroup();
                break;
            case 'dept' : $this->model = new Dept();
                break;
            case 'code' : $this->model = new Code();
                break;
        }

        unset($this->params['type']);
        $this->model->index($this->params);
    }
}
