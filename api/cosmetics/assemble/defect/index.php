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
            return $model->index($params);
        } else {
            if ($params['type'] === 'defect') {
                return $model->showDefect($id);
            }
            return $model->show($id);
        }
    case 'POST' : $model->create($params);
        break;
    case 'PUT' : $model->update($id, $params);
        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
