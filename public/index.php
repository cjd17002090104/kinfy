<?php
//$arr=array(1,2,'3');
//$array=[0,1,2,'3'];
//
//print_r($array);
//
//var_dump($array);
//
//if(in_array('abc',$array,true)){
//    echo 'found!';
//}
//foreach($arr as $k=>$v){
//
//}
//explode(",",trim("a,b,c,,,",",|"));
////1.有一个长度10000的数组，里面有很多重复的数据
////要求：找出重复的数据，并统计数据重复的次数
////例：a：10
////    b：5
////2.循环10000000次，每次调用uniqid（）；
////最后判断这10000000随机整数里面重复了多少次？
////谁重复了多少次
////例：
////   1:500
////   2:1000
require_once __DIR__.'/../vendor/autoload.php';
use Kinfy\Http\Router;

require_once __DIR__.'/../app/router/web.php';
\Kinfy\Http\Router::dispatch();
//print_r($_SERVER);

//$ctrl='App\\Controller\\'.$_GET['c'].'Controller';
//$method=$_GET['m'];
//
//$art=new $ctrl();
////$art->{$method}();
//Router::$nodFound=function (){
//
//    require_once __DIR__.'/../app/resource/404.html';
//    die;
//};

Router::$namespace='\\App\\Controller';
Router::dispatch();