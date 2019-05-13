<?php
/**
 *
 * 此文件类似于控制器工厂模式
 * 定义所有控制器的全局前后拦截
 * 规定控制器对象自生的前后拦截
 * 从字符串中截取目标控制器和目标方法
 * 灵活实例化控制器并执行方法
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11
 * Time: 10:06
 */

namespace Kinfy\Http;

class Controller
{
    public static $conf=[
        'delimiter' =>'@',
        'namespace' =>'\\App\\Controller\\',
        'global_prefix' => 'before',
        'global_suffix' => 'after',
        'prefix'=>[
        ],
        'suffix'=>[
            'login'=>'after',
            'index'=>'before'
        ],
        'band'=>[
            'login'
        ]
    ];
    public  static  function execMethod($obj,$method,$params=[]){
        if(method_exists($obj,$method)) {
            $obj->{$method}(...$params);
        }
    }

    //工厂模式?灵活生成控制器，并配置前后缀
    public static function run($callback,$params){
        if(is_callable($callback)){
            call_user_func($callback,...$params);
        }else{
            list($class,$method) = explode(self::$conf['delimiter'],$callback);
            $class =self::$conf['namespace'].$class;
            $obj= new $class();

            //如果不是禁止执行的方法，则不执行
            if(in_array($method,self::$conf['band'])){
                die("{$method}方法被禁用");
            }else{
                //先执行全局前置方法
                if(self::$conf['global_prefix']){
                    self::execMethod($obj,self::$conf['global_prefix']);
                }

                //执行前置
                if(!empty(self::$conf['prefix'])){
                    self::execMethod($obj,self::$conf['prefix'][$method].$method,$params);
                }

                $obj->{$method}(...$params);

                //执行后置
                if(!empty(self::$conf['suffix'])){
                    self::execMethod($obj,self::$conf['suffix'][$method].$method,$params);
                }


                //后执行全局后置方法
                if(self::$conf['global_suffix']){
                    self::execMethod($obj,self::$conf['global_suffix']);
                }


            }
        }

    }
}