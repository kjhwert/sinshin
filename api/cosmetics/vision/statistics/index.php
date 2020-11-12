<?php

require_once '../../../middleware.php';

$req = new Request();
$params = $req->getParams();
$method = $req->getMethod();

$model = new MachineVisionStatistics();
$id = $req->getParamsValue($model->primaryKey);

switch ($method) {
    case 'GET' :
        if (!$req->hasId($model->primaryKey)) {
            switch ($params['date']) {
                case 'month':
                    if ($params['type'] === "statistic") {
                        return $model->monthStatisticIndex($params);
                    }
                    if ($params['type'] === "average") {
                        return $model->monthAverageIndex($params);
                    }
                    break;
                case 'day':
                    if ($params['type'] === "statistic") {
                        return $model->dayStatisticIndex($params);
                    }
                    if ($params['type'] === "average") {
                        return $model->dayAverageIndex($params);
                    }
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
