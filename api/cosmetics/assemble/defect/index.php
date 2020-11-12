<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new QrDefectA();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            if($params['type'] === 'tablet') {
                return $model->tabletIndex($params);
            }
            return $model->index($params);
        } else {
            switch ($params['type']) {
                case 'defect' :
                    return $model->showDefect($id);
                case 'tablet' :
                    return $model->tabletShow($id);

                default:$model->show($id);
            }
        }
    case 'POST' : $model->create($params);
        break;
    case 'PUT' : $model->update($id, $params);
        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
