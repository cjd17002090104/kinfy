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
        $user = new User();
        $user->id='32';
        $user->email = "123456789123456";
        $user->save();

    }


}