<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/6/4
 * Time: 12:14
 * config文件用来给外部调用，可返回指定路径下的配置文件
 */

namespace Kinfy\Config;
class Config
{
    //存放整个网站的配置信息
    protected static $conf = [];
    //系统配置文件夹根目录
    protected static $base_dir = '';

    public static function setBaseDir($dir)
    {
        self::$base_dir = $dir;
    }

    /**
     * @param $key /配置的文件名和参数 列:app.provider
     * @return mixed|null
     * 该方法会先将该字段用exploder炸开，然后将第一个元素（app）
     * 与base_dir拼接，再加载base_dir/app.php中的配置到$conf中
     * 再以剩下的字段为键值访问$conf得到相应的配置
     */
    public static function get($key)
    {
        $k = explode('.', $key, 5);
        $f = $k[0];
        if (!isset(self::$conf[$f])) {
            self::$conf[$f] = include self::$base_dir . $f . '.php';
        }

        switch (count($k)) {
            case 1:
                return self::$conf[$k[0]] ?? null;
            case 2:
                return self::$conf[$k[0]][$k[1]] ?? null;
            case 3:
                return self::$conf[$k[0]][$k[1]][$k[2]] ?? null;
            case 4:
                return self::$conf[$k[0]][$k[1]][$k[2]][$k[3]] ?? null;
            case 5:
                return self::$conf[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] ?? null;
        }

        return null;

    }

    public static function set($key, $value)
    {
        $k = explode('.', $key, 5);
        switch (count($k)) {
            case 1:
                self::$conf[$k[0]] = $value;
                break;
            case 2:
                self::$conf[$k[0]][$k[1]] = $value;
                break;
            case 3:
                self::$conf[$k[0]][$k[1]][$k[2]] = $value;
                break;
            case 4:
                self::$conf[$k[0]][$k[1]][$k[2]][$k[3]] = $value;
                break;
            case 5:
                self::$conf[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] = $value;
                break;
        }
    }


}