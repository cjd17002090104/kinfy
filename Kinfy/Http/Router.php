<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:59
 */

namespace Kinfy\Http;


class Router
{

    //当路由未匹配的时候执行的回调函数，默认为空
    public static $notFound = null;
    public static $delimiter = '@';
    public static $namespace = '';
    public static $url_pre = [];

    //存放当前注册的所有的路由规则
    public static $routes = [];
    //存放当前注册的所有正则表达式路由规则
    public static $re_routes = [];

    public static $default_rule = [];

    public function rule($param_name,$pattern){
        self::$default_rule[$param_name] = $pattern;
    }

    public static function group($pre, $callback)
    {
        array_push(self::$url_pre, $pre);

        if (is_callable($callback)) {
            call_user_func($callback);
        }

        array_pop(self::$url_pre);
    }

    //添加一条路由
    private static function addRoute($reqtype, $pattern, $callback, $re_rule = null)
    {
        //var_dump($re_rule);

        $reqtype = strtoupper($reqtype);
        $pattern = self::path(implode('/' , self::$url_pre) .self::path($pattern));
//        $pattern = self::path($pattern);
        $is_regx = strpos($pattern, '{') !== false;

        if (!$is_regx) {
            self::$routes[$reqtype][$pattern] = $callback;
        } else {

            $pattern_raw = $pattern;
            //先找出占位符的名称
            $is_matched = preg_match_all('#{(.*?}#', $pattern, $pnames);
            if($is_matched){
                //占位符默认替换的规则为全部
                foreach ($pnames[1] as $p){
                    $pname = str_replace('?','',$p);

                    $rule = '.+';

                    if(is_array($re_rule) && isset($re_rule[$pname])) {

                        $rule = $re_rule[$pname];

                    }else if(isset(self::$default_rule[$pname])) {
                        $rule = self::$default_rule[$pname];
                    }else if(strpos($pname, '?') !== false){
                        $rule = '.*';
                    }

                    $pattern = str_replace(
                        '{' .$p. '}',
                        '(' .$rule. ')',
                        $pattern
                    );
                }
            }

            $route = [
                'pattern_raw' => $pattern_raw,
                'pattern_re' => '#^'.$pattern.'$#',
                'callback' => $callback
            ];
            self::$re_routes[$reqtype][$pattern_raw] = $route;
        }


    }
    public static function __callStatic($name, $args)
    {
        if(count($args)>=2) {
                self::addRoute($name,...$args);
        }
    }

    public static function match($reqtype_arr,$pattern,$callback)
    {
        foreach ( $reqtype_arr as $reqtype) {
            self::addRoute($reqtype,$pattern,$callback);
        }
    }

    private static function path($path)
    {
        return '/'.trim($path,'/');
    }


    public static function getParams($url_pattern,$url)
    {

    }

    public static function dispatch()
    {
        $routes = self::$routes;
        $re_routes = self::$re_routes;
        //print_r($routes);//die;
        //print_r($re_routes);
        $reqtype = strtoupper($_SERVER['REQUEST_METHOD']);
        $url = isset ($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL']:'/';
        //print_r($_SERVER);

        $is_matched = false;
        $callback = null;
        $params = null;

        if(isset($routes['ANY'][$url])){
            $callback = $routes['ANY'][$url];
            $is_matched = true;
        } else if(isset($routes[$reqtype][$url])){
            $callback = $routes[$reqtype][$url];
            $is_matched = true;
        }else {

            if(isset($re_routes['ANY'])){
                foreach ($routes['ANY'] as $pattern => $route ){

                    $is_matched = preg_match_all($route['pattern_re'], $url,$params);

                    if($is_matched) {
                        $callback = $route['callback'];
                        array_shift($params);
                        break;
                    }
                }

            }

            if(!$is_matched && isset($re_routes[$reqtype])){
                foreach ($re_routes[$reqtype] as $pattern => $route) {
                    $is_matched = preg_match_all($route['pattern_re'], $url, $params);

                    if ($is_matched) {
                        $callback = $route['callback'];
                       array_shift($params);
                        break;
                    }
                }
            }
        }




        if($is_matched){

            if(is_callable($callback)){
                call_user_func($callback, ...$params);
            }else{

                list($class,$method) = explode(self::$delimiter,$callback);
                $class =self::$namespace.$class;
                $obj= new $class();
                $obj->{$method}(...$params);
            }
        }else{
            if(is_callable(self::$notFound)) {
                call_user_func(self::$notFound);
            }else {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
        }
    }
}