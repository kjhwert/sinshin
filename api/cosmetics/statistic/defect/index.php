<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new DefectMonitoring();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if ($params['month']) {
            return $model->monthlyIndex($params);
        }
        return $model->yearlyIndex($params);
};
