<?php

require_once '../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new AutoMProcess();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if($params['type'] === "defect") {
            unset($params['type']);
            return $model->defect_index($params);
        }

        if (!$req->hasId($model->primaryKey)) {
            $model->index($params);
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
