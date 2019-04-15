<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/11
 * Time: 11:33
 */

namespace App\Controller;


class BaseController
{
    public function before(){
        echo '全局拦截<br>';
    }
}