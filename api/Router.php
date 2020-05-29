<?php

require_once "controller/User/LoginController.php";
require_once "controller/User/UserController.php";
require_once "Request.php";

$req = new Request();

switch ($req->routePath()) {
    case 'login':
        return (new LoginController())->login($req->getParams());
        break;
    case 'user' :
        return new UserController($req->showId(), $req->getParams(), $req->method());
}
