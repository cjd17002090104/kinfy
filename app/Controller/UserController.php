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
        $user->join("article","user.id","=","article.id")
             ->where("user.id","=",1)
             ->where("user.id","=","2","or")
             ->select("user.id","user.name")
             ->get();
    }

    public function after()
    {
        echo'after';
    }
    public function before()
    {
        echo'before';
    }

    public  function add()
    {
        echo 'user add';
    }

    public function login(){
        echo '登录页面！！';
    }

    public function del($id){
        echo $id;
    }

    public  function BeforeDel($id){
        if($id == 1){
            echo '无法删除超管';
        }
    }
}