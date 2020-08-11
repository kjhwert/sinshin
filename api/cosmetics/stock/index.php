<?php

require_once '../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new MaterialStock();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            switch ($params['type']) {
                case 'warehouse':
                    unset($params['type']);
                    $model->warehouseIndex($params);
                    break;
                case 'stock':
                    unset($params['type']);
                    $model->stockIndex($params);
                    break;
                default: return new Response(403, [],'type을 지정해주세요.');
            }
        } else {
            $model->show($id);
        }
        break;
    case 'POST' : $model->create($params);
        break;
    case 'PUT' : $model->update($id, $params);
        break;
    case 'DELETE' : $model->destroy($id);
        break;
}
