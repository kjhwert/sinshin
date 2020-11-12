<?php

require_once '../../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new ProductionManage();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            switch ($params['type']) {
                case 'injection':
                    return $model->injectionIndex($params);
                case 'painting':
                    return $model->paintingIndex($params);
                case 'assemble':
                    return $model->assembleIndex($params);
            }
        } else {
            $model->show($id);
        }
        break;
//    case 'POST' : $model->create($params);
//        break;
//    case 'PUT' : $model->update($id, $params);
//        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
