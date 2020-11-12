<?php

require_once '../../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new MachineVisionPaintingCount();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            $model->index($params);
        } else {
            $model->show($id);
        }
        break;
    case 'POST' :
        if ($params['type'] === "input") {
            return $model->inputCreate($params);
        }

        if ($params['type'] === "output") {
            return $model->outputCreate($params);
        }
        break;
//    case 'PUT' : $model->update($id, $params);
//        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
