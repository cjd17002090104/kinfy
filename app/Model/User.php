<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/4/17
 * Time: 16:20
 */

namespace App\Model;
require './Model.php';
class User extends Model
{
    protected $table='user';
    protected $primaryKey='id';
}

$user=new User();
$user->where('id','>=','1')->find(1);
