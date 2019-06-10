<?php
/**
 *
 * 单一入口文件
 *
 */

use Kinfy\Http\Router;
use Kinfy\Http\Controller;
use Kinfy\Config\Config;

require_once __DIR__.'./../vendor/autoload.php';
require_once __DIR__.'./../app/router/web.php';

Config::setBaseDir(__DIR__.'./../app/config/');
Router::$onMissMatch=function (){
    die("404");
};

//动态创建不存在的类Facade
spl_autoload_register(function ($name) {
    $provider = Config::get('app.providers.' . $name);

    //如果是根命名空间的，不存在的类，则定义之
    if (
        strpos($name, '\\') === false
        &&
        $provider
    ) {
        //如果定义了接口，则做接口判断
        $provider_interface = Config::get('app.provider_interface.' . $name);
        if ($provider_interface) {
            $interfaces = class_implements($provider);
            if (!isset($interfaces[$provider_interface])) {
                die("{$provider} 必须实现 {$provider_interface} 接口");
            }
        }
        //动态定义类
        eval("
            class {$name} extends \Kinfy\Facade\Facade{
                protected static \$provider = {$provider}::class;
            }
        ");
    }

});

Router::$onMatch=function ($callback,$params){
    Controller::run($callback,$params);
};
Router::dispatch();

