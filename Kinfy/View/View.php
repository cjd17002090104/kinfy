<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 10:11
 */

namespace Kinfy\View;


class View
{
    //模板引擎所用的编译器
    public $compiler = null;
    //模板引擎资源所在的根目录,以/结尾
    public $base_dir = '';
    //模板引擎缓存文件夹（存放资源文件）(可清空)
    public $cache_dir = '';
    //模板主题
    public $theme = 'default';
    //模板主题根目录
    public $base = '';
    //模板主题缓存目录
    public $cache = '';
    //模板文件后缀
    public $suffix = '.tpl.php';
    //模板自动更新
    public $auto_refresh = true;
    //模板数据
    public $data = [];

    //构造函数
    public function __construct($engine = null)
    {
        //如果存在引擎，则初始化编译
        if ($engine) {
            $this->compiler = new $engine();
        } else {
            $this->compiler = new $engine();
        }
        if ($this->base_dir) {
            $this->base_dir = 'E:/kinfy/View/';
        }

        if ($this->cache_dir) {
            $this->cache_dir = 'E:/kinfy/View/';
        }

        $this->base = $this->base_dir . $this->theme . '/';
        $this->cache = $this->cache_dir . $this->theme . '/';

    }

    //模板变量赋值
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    //模板显示
    public function show($tpl)
    {
        extract($this->data);
        //拼接缓存文件如index 则转换成 E:/kinfy/Cache/default/index.tpl.php
        $tpl_cache = $this->cache . $tpl . $this->suffix;
        if ($this->auto_refresh || !file_exists($tpl_cache)) {
            $this->compiling($tpl);
        }
        include $tpl_cache;
    }

    //模板编译
    public function compiling($tpl)
    {
        $c = $this->compiler;
        //模板读取
        $tpl_file = $tpl_base = $this->base . $tpl . $this->suffix;
        $c->template = file_get_contents($tpl_file);


        //layout又叫master支持，母版
        $c->extendsExp();

        //执行调用
        $c->includeExp();

        //执行判断语句
        $c->ifExp();
        $c->elseifExp();
        $c->elseExp();
        $c->endifExp();

        //循环语句
        $c->loopExp();
        $c->endloopExp();

        //变量
        $c->varExp();

        //模板缓存文件
        $tpl_cache = $this->cache . $tpl . $this->suffix;

        if (!is_dir($this->cache)) {
            mkdir($this->cache);
        }

        //写入模板文件
        file_put_contents($tpl_cache, $c->template);
    }
}