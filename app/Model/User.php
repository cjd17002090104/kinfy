<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/4/17
 * Time: 16:20
 */

namespace App\Model;
use Kinfy\DB\DB;
class User extends DB
{
    protected $table='user';
    protected $primaryKey='id';
}


