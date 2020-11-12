<?php

require_once '../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new DashBoard();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        switch ($params['type']) {
            case 'week':
                return $model->weekIndex();
            case 'defect':
                return $model->defectIndex();
        }
        break;
//    case 'POST' : $model->create($params);
//        break;
//    case 'PUT' : $model->update($id, $params);
//        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
