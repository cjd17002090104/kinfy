<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 10:11
 */

namespace Kinfy\View;

use Kinfy\Config\Config;
class View implements IView
{
    //模板引擎所用的编译器
    public $compiler = null;
    //模板引擎资源所在的根目录,以/结尾
    public $base_dir = '';
    //模板引擎缓存文件夹（存放资源文件）(可清空)
    public $cache_dir = '';
    //模板默认主题
    public $theme = '';

    //模板文件后缀
    public $suffix = '';
    //模板自动更新
    public $auto_refresh = true;
    //模板数据
    public $data = [];

    //构造函数
    public function __construct($engine = null)
    {
        //如果存在引擎，则初始化编译器
        if ($engine) {
            $this->compiler = new $engine();
        } else {
            $engine = Config::get('view.engine');
            $this->compiler = new $engine;
        }

        if (!$this->base_dir) {
            $this->base_dir = Config::get('view.base_dir');
        }

        if (!$this->cache_dir) {
            $this->cache_dir = Config::get('view.cache_dir');
        }

        if (!$this->theme) {
            $this->theme = Config::get('view.theme');
        }

        if (!$this->suffix) {
            $this->suffix = Config::get('view.suffix');
        }

    }
    /**
     * @return string  /返回主题资源文件根目录
     */
    public function themeBaseDir()
    {
        return $this->base_dir . $this->theme . '/';
    }

    /**
     * @return string /返回主题缓存文件根目录
     */
    public function themeCacheDir()
    {
        return $this->cache_dir . $this->theme . '/';
    }

    /**
     * @param $name /模板文件名字
     * @return string /根据名称返回模板资源文件
     */
    public function tplFile($name)
    {
        return $this->themeBaseDir() . $name . $this->suffix;
    }

    /**
     * @param $name /缓存文件名字
     * @return string /根据名称返回模板缓存文件
     */
    public function tplCache($name)
    {
        return $this->themeCacheDir() . $name . 'php';
    }

    /**
     * @param $name
     * @param $value /通过引擎解析变量，之后再将之存到data数组中
     */
    public function set($name, $value)
    {

        $this->data[$name] = $value;
    }



    /**
     * @param $tpl
     * /模板显示
     */
    public function show($tpl)
    {
        //将data的变量提取出来
        extract($this->data);
        //拼接缓存文件如index 则转换成 E:/kinfy/Cache/default/index.tpl.php
        $tpl_cache = $this->tplCache($tpl);
        //强制更新
        if ($this->auto_refresh || !file_exists($tpl_cache)) {
            $this->compiling($tpl);
        }
        include $tpl_cache;
    }

    /**
     * @param $tpl  /模板编译
     */
    public function compiling($tpl)
    {
        $c = $this->compiler;
        $c->base_dir = $this->themeBaseDir();
        $c->suffix = $this->suffix;
        //模板读取
        //取出模板内容
        $c->template = file_get_contents($this->tplFile($tpl));


        //模板编译
        $c->compiling();


        //写入模板前，判断是否有对应主题的缓存文件夹，如无，则创建之
        $this->mkTplCacheDir($tpl);

        //将模板文件读入缓存文件
        //若没有缓存文件则会生成一个缓存文件
        file_put_contents($this->tplCache($tpl), $c->template);
    }

    //创建缓存文件所对应的目录，逐级创建
    protected function mkTplCacheDir($tpl)
    {
        //生成缓存文件夹
        $tpl_cache_path = $this->theme . '/' . $tpl;

        $path_arr = explode('/', $tpl_cache_path);
        $dir = $this->cache_dir;
        //default/article/index.tpl.php
        for ($i = 0; $i < count($path_arr) - 1; $i++) {
            $dir .= $path_arr[$i].'/';

            if (!is_dir($dir)) {
                //新建文件夹
                mkdir($dir);
            }
        }
    }
}
