<?php
require_once __DIR__.'./../vendor/autoload.php';
use Kinfy\Http\Router;
require_once __DIR__.'./../app/router/web.php';

Router::$notFound = function(){
//    require_once __DIR__.'./../app/resource/404.html';
//    die;
//
};
Router::dispatch();

