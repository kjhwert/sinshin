<?php

require_once '../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

switch ($method) {
    case 'GET' :
        switch ($params['type']) {
            case 'dept-group' :
                unset($params['type']);
                echo (new DeptGroup())->index($params);
                break;
            case 'dept' :
                unset($params['type']);
                echo (new Dept())->index($params);
                break;
            case 'code' :
                unset($params['type']);
                echo (new Code())->index($params);
                break;
        }
        break;
    case 'POST' :
        break;
    case 'PUT' :
        break;
    case 'DELETE' :
        break;
}


