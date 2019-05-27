<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:18
 */

namespace App\Controller;

use App\Model\User;
use Kinfy\View\View;

class UserController extends BaseController
{
    public function index()
    {
        $view = new View();
        $view->set('title',
            [
                ['icon-location2', 'Brooklyn, NY 10036, United States'],
                ['icon-phone2', '+1-123-456-7890'],
                ['icon-mail', 'info@probootstrap.com']
            ]
        );
        $view->show('school');

    }


}