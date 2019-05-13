<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:18
 */
namespace App\Controller;
use App\Model\User;

class UserController extends BaseController
{
    public function index()
    {
        $user=new User();
        $result=$user->insert(['email'=>'123546331']);
    }


}