<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 11:31
 */

namespace Kinfy\View\engine;


class Blade implements Iengine
{
    public function compiling()
    {
        //layout又叫master支持，母版
        $this->extendsExp();

        //执行调用
        $this->includeExp();

        //执行判断语句
        $this->ifExp();
        $this->elseifExp();
        $this->elseExp();
        $this->endifExp();

        //循环语句
        $this->loopExp();
        $this->endloopExp();

        //变量
        $this->varExp();
    }
}