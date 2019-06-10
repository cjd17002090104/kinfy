<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:18
 */

namespace App\Controller;

use App\Model\User;
use View;

class UserController extends BaseController
{
    public function index()
    {

        View::set('title',
            [
                ['icon-location2', 'Brooklyn, NY 10036, United States'],
                ['icon-phone2', '+1-123-456-7890'],
                ['icon-mail', 'info@probootstrap.com']
            ]
        );
        View::show('school');

    }

    public function insert()
    {
        User::insert(['email'=>'123']);
    }


}