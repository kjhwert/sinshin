<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new History();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            switch ($params['type']) {
                case 'product':
                    return $model->productIndex($params);
                case 'defect':
                    return $model->defectIndex($params);
                default: return (new ErrorHandler())->badRequest();
            }
        } else {
            switch ($params['type']) {
                case 'product' :
                    return $model->productShow($id, $params);
                case 'defect':
                    return $model->defectShow($id, $params);
                default: return (new ErrorHandler())->badRequest();
            }
        }
};
