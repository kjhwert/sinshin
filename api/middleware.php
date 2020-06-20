<?php
header("Content-Type: application/json;charset=utf-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

spl_autoload_register(function ($class) {
    $dirs = ['/','/model/','/controller/'];

    foreach ($dirs as $dir) {
        if (file_exists(__DIR__."{$dir}{$class}.php")) {
            require_once __DIR__."{$dir}{$class}.php";
        }
    }
});
