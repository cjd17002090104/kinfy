<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/4/17
 * Time: 16:20
 */

namespace App\Model;
use Kinfy\Model\Model;

class User extends Model
{
    protected $table='user';
    protected $pk='id';
    protected $field2property=['id'=>'Id'];
}


