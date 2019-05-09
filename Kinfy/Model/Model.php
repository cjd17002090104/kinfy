<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/5/9
 * Time: 13:02
 */

class Model
{
    public $table='';
    private $DB = null;
    public $properties = [];

    public function __get($name)
    {
        return $this->properties[$name];
    }

    public function __set($name,$value)
    {
        $this->properties[$name] = $value;
    }

    public function __construct()
    {
        if(!$this->DB){
            $this->DB=new \Kinfy\DB\DB();
        }
        if(!$this->table){
            $class=get_class($this);
            $this->table =substr($class,strrpos($class,'\\')+1);
        }
    }
}