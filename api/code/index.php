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
                return (new DeptGroup())->index($params);
                break;
            case 'dept' :
                unset($params['type']);
                return (new Dept())->index($params);
                break;
            case 'code' :
                unset($params['type']);
                return (new Code())->index($params);
                break;
        }
        break;
}


