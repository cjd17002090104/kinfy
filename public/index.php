<?php
/**
 *
 * 单一入口文件
 *
 */
require_once __DIR__.'./../vendor/autoload.php';
use Kinfy\Http\Router;
use Kinfy\Http\Controller;
require_once __DIR__.'./../app/router/web.php';

Router::$onMissMatch=function (){
    die("404");
};
Router::$onMatch=function ($callback,$params){
    Controller::run($callback,$params);
};
Router::dispatch();

