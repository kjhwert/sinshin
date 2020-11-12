<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new Release();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
       return $model->index($params);
};
