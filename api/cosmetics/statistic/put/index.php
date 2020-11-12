<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new Put();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        switch ($params['type']) {
            case 'product':
                return $model->productIndex($params);
            case 'material':
                return $model->materialIndex($params);
            default: return (new ErrorHandler())->badRequest();
        }
};
