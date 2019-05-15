<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/8
 * Time: 11:07
 */

namespace Kinfy\Model;

use Kinfy\DB\DB;

class Model
{
    //存放当前实例
    protected static $instance = null;
    //存放实例对应的数据表
    protected $table = '';
    //当前数据库对象
    protected $DB = null;
    //实例属性
    public $properties = [];
    //数据库列名
    protected $fields = [];
    //数据库列名别名，键：数据库列名，值：对象属性名
    protected $field2property = [

    ];
    //实例属性名对应的数据库列名
    protected $property2field = [];
    //是否自动大小写和下划线进行转换
    protected $autoCamelCase = true;

    //模型对应数据库的主键
    protected $pk = 'id';

    //主键是否是由数据库自动生成
    protected $autoPk = true;


    /**
     * @return string 生成随机UUID
     */
    protected function genPk()
    {
        return uniqid(md5(microtime(true)), true);
    }

    //camelSnake 转换成 camel_snake
    //camelSNAKE  2 camel_snake
    //camel_SNAKE   camel_snake
    //camel_SNAKeName  camel_snake_name

    /**
     * @param $str
     * @return bool  检测是否是大写
     */
    private function isUpper($str)
    {
        return ord($str) > 64 && ord($str) < 91;
    }

    protected function genFiledProperty()
    {


    }

    /**
     * @param $str
     * @return string 将驼峰转化成下划线
     */
    protected function camel2snake($str)
    {
        $s = '';
        for ($i = 0; $i < strlen($str); $i++) {
            //如果是大写，且不是首字母,且前一个不是大写或者下划线
            if ($i > 0 &&
                $this->isUpper($str[$i]) &&
                $this->isUpper($str[$i - 1]) &&
                $str[$i - 1] != '_'
            ) {
                $s .= '_';
            }
            $s .= $str[$i];
        }
        return strtolower($s);
    }

    /**
     * @param $str
     * @return string 将下划线转化成驼峰
     */
    protected function snake2camel($str)
    {
        $c = '';
        $str_arr = explode('_', $str);
        foreach ($str_arr as $s) {
            //首字母大写
            $c .= ucfirst($s);
        }
        return $c;
    }

    /**
     * save方法会用到，将设定的属性通过camel2snake方法变成下划线格式
     */
    //往数据库添加的时候，属性名为fieldName要转换成field_name
    protected function filterFields()
    {
        if (empty($this->property2field)) {
            //倒置$this->field2property
            $this->property2field = array_flip($this->field2property);
        }
        foreach ($this->properties as $k => $v) {
            //匹配当前所设置的属性，将匹配中的属性传给fields数组
            if (isset($this->property2field[$k])) {
                $k = $this->property2field[$k];
            } else if ($this->autoCamelCase) {
                //未匹配中（没有别名）且开启自动规范的情况下 将属性变成下划线模式
                $k = $this->camel2snake($k);
            }
            $this->fields[$k] = $v;
        }
    }

    //数据库里读取出来的数据，列名为field_name要转换成fieldName
    protected function filterProperties($data)
    {
        //若没有数据列别名，且自动转换关闭
        if (empty($this->field2property) && $this->autoCamelCase) {
            return $data;
        }
        $new_data = [];
        foreach ($data as $k => $v) {
            if (isset($this->field2property[$k])) {
                $k = $this->field2property[$k];
            } else if ($this->autoCamelCase) {
                $k = $this->snake2camel($k);
            }
            $new_data[$k] = $v;
        }
        return $new_data;
    }

    //当读取一个不存在的属性名的时候，自动到当前实例的properties属性数组里获取
    public function __get($name)
    {
        return $this->properties[$name];
    }

    //当这只一个不存在的属性名的时候，自动将值存取当前实例的properties属性
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    //构造函数，初始化当前对应的数据表，初始化当前实例对应的数据库对象
    public function __construct()
    {

        //获取类名与数据库名字绑定
        if (!$this->table) {
            $class = get_class($this);
            $this->table = substr($class, strrpos($class, '\\') + 1);
        }

        if (!$this->DB) {
            $this->DB = new DB();
        }

        $this->DB->table($this->table);
        if($this->pk)
        $this->fields[$this->pk]=null;
    }

    //判断是否是终端函数
    private function isTerminalMethod($name)
    {
        $name = strtolower($name);
        $m = [
            'get',
            'first',
        ];
        return in_array($name, $m);
    }

    //当调用一个不存在的实例方法时则自动调用该魔术方法
    public function __call($name, $arguments)
    {
        $name = strtolower($name);
        $r = $this->DB->{$name}(...$arguments);
        //如果不是结束节点(获取数据)
        if (!$this->isTerminalMethod($name)) {
            return $this;
        }
        if (empty($this->field2property) && !$this->autoCamelCase) {
            return $r;
        }

        //判断是否是
        if (is_array($r)) {
            if ($name == 'get') {
                foreach ($r as &$data) {
                    $data = $this->filterProperties($data);
                }
            } else if ($name == 'first') {
                $r = $this->filterProperties($r);
                foreach ($r as $k => $v) {
                    $this->properties[$k] = $v;
                }
            }
            return $r;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance->{$name}(...$arguments);
    }

    //新增或者更新方法，取决于主键是否有值
    public function save($isAdd = false)
    {
        $this->filterFields();
        //如果强制添加，或者没有主键，则添加，否则更新
        if ($isAdd||!$this->havePk()) {
            return $this->add();
        }else{
            return $this->update();
        }

    }

    //因为当执行静态代码时，自身有一个实例static::$instance
    //返回静态的共用的实例
    public static function getInstance()
    {
        return static::$instance;
    }

    public function havePK()
    {
        return $this->fields[$this->pk] !== null;
    }

    public function add()
    {

        if ($this->autoPk) {
            unset($this->fields[$this->pk]);
        } else if (!$this->havePk()) {
            $this->fields[$this->pk] = $this->genPk();
        }

        return $this->DB->insert($this->fields);
    }

    public function update()
    {
        $k = $this->pk;
        $v = $this->fields[$this->pk];

        unset($this->fields[$this->pk]);
        return $this->DB
            ->where($k,'=',$v)
            ->update($this->fields);
    }

}