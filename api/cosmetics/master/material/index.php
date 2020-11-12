<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new MaterialMaster();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            if ($params['page'] && $params['perPage']) {
                return $model->pagingIndex($params);
            }
            return $model->index($params);
        } else {
            return $model->show($id);
        }
    case 'POST' :
        $model->create($params);
        break;
//    case 'PUT' : $model->update($id, $params);
//        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
