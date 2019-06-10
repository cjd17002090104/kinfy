<?php
/**
 *
 * 用于存放路由
 * 此文件被index use
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 14:57
 */
use Kinfy\Http\Router;
use App\Model\Model;

Router::get('/login','UserController@index');
Router::get('/user','UserController@insert');
Router::group(['middleware'=>'123'],function (){
        Router::get('/123','UserController@index');
});
