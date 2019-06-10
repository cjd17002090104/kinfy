<?php
/**
 * 路由处理文件
 *
 * 用于存放路由和匹配并执行路由对应的方法
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:59
 */

namespace Kinfy\Http;
use Kinfy\Config\Config;
class Router
{

    //分割符
    public static $delimiter = '@';

    //路由前缀
    public static $url_pre = [];

    //存放路由过滤的中间件
    public static $middlewares = [];

    //存放当前注册的所有的路由规则
    public static $routes = [];

    //存放当前注册的带参数路由
    public static $re_routes = [];

    //存放正则参数全局默认规则
    public static $default_rule = [];

    //路由匹配中的回调方法，默认为空
    public static $onMatch = '';

    //未匹配的方法
    public static $onMissMatch = '';


    /**
     * 用于配置全局规则
     *
     * @param $param_name
     * @param $pattern /规则
     */
    public function rule($param_name, $pattern)
    {
        self::$default_rule[$param_name] = $pattern;
    }


    /**
     * 用于解析路由组
     *
     * @param $pre /路由前缀
     * @param $callback
     */
    public static function group($pre , $callback)
    {
        $p = '';
        $m = '';
        //判断是否有路由前缀和中间件
        //group::(['prefix'=>,'middleware'=>])
        if (is_array($pre)) {

            if (isset($pre['prefix']) && $pre['prefix']) {
                $p = $pre['prefix'];
            }

            if (isset($pre['middleware']) && $pre['middleware']) {
                $m = $pre['middleware'];
            }
        }


        $p && array_push(self::$url_pre, $p);
        $m && array_push(self::$middlewares, $m);


        if (is_callable($callback)) {
            //递归，将匿名函数解包，所有将中间键和路由前缀扔给路由;
            call_user_func($callback);

        }

        //清除
        $p && array_pop(self::$url_pre);
        $m && array_pop(self::$middlewares);


    }


    /**
     * 添加路由，降web.php的路由添加到路由数组中
     *
     * @param $reqtype /请求类型
     * @param $pattern /路由格式
     * @param $callback /路由方法
     * @param null $re_rule 路由正则
     */
    private static function addRoute($reqtype, $pattern, $callback, $re_rule = null)
    {
        //var_dump($re_rule);

        $reqtype = strtoupper($reqtype);
        $pattern = self::path(implode('/', self::$url_pre) . self::path($pattern));
        //$pattern = self::path($pattern);

        $route = [
            'callback' => $callback,
            'middlewares' => self::$middlewares
        ];
        //判断是否是带参数路由
        $is_regx = strpos($pattern, '{') !== false;

        if (!$is_regx) {
            self::$routes[$reqtype][$pattern] = $route;
        } else {

            $pattern_raw = $pattern;
            //先找出占位符的名称
            $is_matched = preg_match_all('#{(.*?)}#', $pattern, $pnames);
            if ($is_matched) {
                //占位符默认替换的规则为全部
                foreach ($pnames[1] as $p) {
                    $pname = str_replace('?', '', $p);

                    $rule = '.+';

                    //检测参数是否有正则约束
                    if (is_array($re_rule) && isset($re_rule[$pname])) {

                        $rule = $re_rule[$pname];

                    } else if (isset(self::$default_rule[$pname])) {
                        $rule = self::$default_rule[$pname];
                    } else if (strpos($p, '?') !== false) {
                        $rule = '.*';
                    }


                    //将路由格式替换为正则
                    $pattern = str_replace(
                        '{' . $p . '}',
                        '(' . $rule . ')',
                        $pattern
                    );
                }
            }

            $route = [
                'pattern_raw' => $pattern_raw,
                'pattern_re' => '#^' . $pattern . '$#',
                'callback' => $callback,
                'middlewares' => self::$middlewares
            ];

            //存入参数路由数组
            self::$re_routes[$reqtype][$pattern_raw] = $route;

        }


    }


    /**
     *
     * 当访问该类中没有的方法时调用的魔术函数
     * 此函数会将路由传递给addRoute方法
     *
     * @param $name 方法名(请求类型)
     * @param $args 参数
     */
    public static function __callStatic($name, $args)
    {
        if (count($args) >= 2) {
            self::addRoute($name, ...$args);
        }
    }


    /**
     * 匹配match路由
     *
     * @param $reqtype_arr
     * @param $pattern
     * @param $callback
     */
    public static function match($reqtype_arr, $pattern, $callback)
    {
        foreach ($reqtype_arr as $reqtype) {
            self::addRoute($reqtype, $pattern, $callback);
        }
    }


    /**
     * 用于将路由两端的/去掉
     *
     * @param $path
     * @return string
     */
    private static function path($path)
    {
        return '/' . trim($path, '/');
    }


    public static function getParams($url_pattern, $url)
    {

    }

    /**
     *
     * 用户访问时触发的方法
     * 此方法截取用户访问地址，并将之与路由数组进行匹配
     * 匹配成功，则调用callback
     * 匹配失败，则返回onMissMatch
     */
    public static function dispatch()
    {
        $routes = self::$routes;
        $re_routes = self::$re_routes;
        //print_r($routes);//die;
        //print_r($re_routes);
        $reqtype = strtoupper($_SERVER['REQUEST_METHOD']);
        $url = isset ($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '/';
        //print_r($_SERVER);

        $is_matched = false;
        $callback = null;
        $params = [];
        $middlewares = [];

        //首先判断是否是无参ANY路由
        if (isset($routes['ANY'][$url])) {
            $callback = $routes['ANY'][$url]['callback'];
            $middlewares = $routes['ANY'][$url]['middlewares'];

            $is_matched = true;
        } else if (isset($routes[$reqtype][$url])) {
            $callback = $routes[$reqtype][$url]['callback'];
            $middlewares = $routes[$reqtype][$url]['middlewares'];

            $is_matched = true;
        } else {
            //首先判断是否是有参ANY路由
            if (isset($re_routes['ANY'])) {
                foreach ($routes['ANY'] as $pattern => $route) {

                    $is_matched = preg_match_all($route['pattern_re'], $url, $params);

                    if ($is_matched) {
                        $callback = $route['callback'];
                        $middlewares = $route['middlewares'];
                        array_shift($params);
                        break;
                    }
                }

            }

            if (!$is_matched && isset($re_routes[$reqtype])) {
                foreach ($re_routes[$reqtype] as $pattern => $route) {
                    $is_matched = preg_match_all($route['pattern_re'], $url, $params);

                    if ($is_matched) {
                        $callback = $route['callback'];
                        $middlewares = $route['middlewares'];
                        array_shift($params);
                        break;
                    }
                }
            }

        }


        //匹配成功后，判断回调否是可执行函数
        if ($is_matched) {
            //先做中间件数组扁平化
            //循环中间件
            foreach ($middlewares as $ms) {
                !is_array($ms)&&$ms=[$ms];
                foreach ($ms as $m) {
                    //判断闭包
                    if (is_callable($m)) {
                        call_user_func($m);
                    } else {
                        //获取中间件设置指定的中间件
                        $mclass = Config::get('middleware.' . $m);
                        if (is_array($mclass)) {
                            foreach ($mclass as $mc) {
                                $mobj = new $mc;
                                $mobj->handle();
                            }
                        } else {
                            $mobj = new $mclass;
                            $mobj->handle();
                        }
                    }
                }
            }
            if (is_callable($callback)) {
                call_user_func($callback, ...$params);
            } else if (is_callable(self::$onMatch)) {
                call_user_func(self::$onMatch, $callback, $params);
            }

        } else {

            if (is_callable(self::$onMissMatch)) {
                call_user_func(self::$onMissMatch);
            } else {
                header("HTTP/1.1 404 Not Found");
                exit;
            }
        }

    }
}