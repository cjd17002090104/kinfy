<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/3
 * Time: 10:34
 */

namespace Kinfy\View;


interface IView
{
    public function show($tpl);
    public function set($name, $value);
}