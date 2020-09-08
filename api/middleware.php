<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json;charset=utf-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

spl_autoload_register(function ($class) {
    $dirs = [
        '/',
        '/model/',
        '/controller/',
        '/model/automobile/',
        '/model/cosmetic/',
        '/model/qr/',
        '/model/cosmetic/injection/',
        '/model/cosmetic/painting/',
        '/model/cosmetic/assemble/',
    ];

    array_map(function ($dir) use ($class) {
        if (file_exists(__DIR__."{$dir}{$class}.php")) {
            require_once __DIR__."{$dir}{$class}.php";
        }
    }, $dirs);
});
