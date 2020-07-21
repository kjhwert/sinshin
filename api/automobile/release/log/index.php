<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new AutoMReleaseLog();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            if ($params['type'] === "release_list") {
                unset($params['type']);
                return $model->release_list($params);
            }
            return $model->index($params);
        } else {
            if ($params['type'] === "releasable") {
                unset($params['type']);
                return $model->releasable($id, $params);
            }
            if($params['type'] === "memo") {
                unset($params['type']);
                return $model->showMemo($id, $params);
            }
            return $model->show($id, $params);
        }
        break;
    case 'POST' : $model->create($params);
        break;
    case 'PUT' : $model->update($id, $params);
        break;
//    case 'DELETE' : $model->destroy($id);
//        break;
}
