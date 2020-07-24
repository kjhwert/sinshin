<?php

require_once '../middleware.php';

$req = new Request();
$method = $req->getMethod();

$model = new Domain();

switch ($method) {
    case 'GET' :
        $model->index();
        break;
//    case 'POST' : $model->create($params);
//        break;
//    case 'PUT' : $model->update($id, $params);
//        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
