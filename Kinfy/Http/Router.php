<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:59
 */
namespace Kinfy\Http;

class Router{

    //当路由未匹配的时候执行的回调函数，默认为空
    public static $nodFound='';
    public static $delimiter='@';
    public static $namespace='';

    //存放当前注册的所有路由规则
    //第一个键用来存放方法，第二个数组键用来存放路由名，值为回调函数
    public static $routes=[];

    public static function __callStatic($name,$args)
    {
        //转换大写
        $name=strtoupper($name);
        if(count($args)>2){
            if ($name=='MATCH'&&is_array($args[0])&&count($args)>=3){

                foreach ($args[0] as $request_type){
                    $request_type=strtoupper($request_type);
                    self::$routes[$request_type][$args[1]]=$args[2];
                }
            }else {

                self::$routes[$name][$args[0]] = $args[1];
            }
        }
    }
    private static function path($path){
        return '/'.trim($path,'/');
    }

//
//    //将指定的路由规则和处理函数一一对应起来，放置在GET数组下面
//    public static function GET($pattern,$callback){
//        //获取请求方法Get
//        self::$routes['GET'][$pattern]=$callback;
//    }
//    //将指定的路由规则和处理函数一一对应起来，放置在POST数组下面
//    public static function POST($pattern,$callback){
//        //获取请求方法POST
//        self::$routes['POST'][$pattern]=$callback;
//    }
//
//

    //执行$routes数组里的转发规则
    public static function dispatch(){
        //获取请求方法GET,POST,PUT
        $request_type=strtoupper($_SERVER['REQUEST_METHOD']);
        //获取请求地址
        $pattern=strtoupper($_SERVER['REQUEST_URI']);
        //如果请求的存在[GET][/abc]
        $is_matched=false;
        if(isset(self::$routes['ANY'][$pattern])){
            $request_type='ANY';
            $is_matched=true;
        }else if(isset(self::$routes[$request_type][$pattern])){
            $is_matched=true;
        }

        if($is_matched)
        {
            $callback=self::$routes[$request_type][$pattern];
            if(is_callable($callback)){
                call_user_func($callback);
            }else{
                list($class,$method)=explode('@',$callback);
                $class='App\\Controller\\'.$class;
                $obj=new $class();
                $obj->{$method};
            }
        }else{
            if(is_callable(self::$nodFound)){
                call_user_func(self::$nodFound);
            }else {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
        }
    }
}