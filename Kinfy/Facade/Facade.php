<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/6/4
 * Time: 12:53
 */

namespace Kinfy\Facade;
class Facade
{
    protected static $provider = '';
    protected static $instance = [];

    //因为当执行静态代码时，自身有一个实例static::$instance
    //返回静态的共用的实例
    public static function getInstance()
    {
        $p = static::$provider;
        if (!isset(static::$instance[$p])) {
            static::$instance[$p] = new $p;
        }
        return static::$instance[$p];
    }

    //当调用不存在的静态方法的时候，自动静态转动态
    public static function __callStatic($name, $arguments)
    {
        return static::getInstance()->{$name}(...$arguments);
    }

}