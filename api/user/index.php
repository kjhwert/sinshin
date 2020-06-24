<?php

require_once '../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$user = new User();
$id = $req->getParamsValue($user->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($user->primaryKey)) {
            echo $user->index($params);
        } else {
            echo $user->show($id);
        }
        break;
    case 'POST' : echo $user->create($params);
        break;
    case 'PUT' : echo $user->update($id, $params);
        break;
    case 'DELETE' : echo $user->destroy($id);
        break;
}
