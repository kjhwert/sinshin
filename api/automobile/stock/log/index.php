<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new AutoMStockLog();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            if ($params['type'] === "main") {
                return $model->mainIndex();
            }
            return $model->index($params);
        } else {
            $model->show($id, $params);
        }
        break;
//    case 'POST' : $model->create($params);
//        break;
    case 'PUT' : $model->update($id, $params);
        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
