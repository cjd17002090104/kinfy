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
//Router::get('/',function (){
//   echo "INDEX PAGE";
//   $str = 'askflksfjkl@sdfj.com';
//   $is_matched = preg_match('#[\w\d]+@(\.[a-zA-Z]{2,5})+#',$str,$r);
//   print_r($r);
//});
//
//Router::group('v5',function (){
//    Router::group('api',function (){
//        Router::get('foo',function (){
//            echo 'v5/api/foo';
//        });
//    });
//});
//
//Router::get('user',function (){
//    echo 'user';
//});
Router::get('/login','UserController@index');
Router::get('/user',function (){
    $model=new Model();
    $model->printConfig();
});
