<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/4/16
 * Time: 16:04
 */

namespace Kinfy\Model;

use \PDO;

class Model
{
    //子类指定表
    protected $table;
    protected $primaryKey;
    //实例数据
    private $data = [];
    protected $dbConfig=[
        'PORT'=>'3306',
        'DB_NAME'=>'kinfy',
        'ADMIN'=>'root',
        'PASSWORD'=>''
    ];
    //where条件
    private $where = [];
    private $group = "";
    private $join="";
    private $columns = null;
    private $pdo;

    public function __construct()
    {
        $this->dbLink();
    }

    /**
     * 数据库连接
     */
    public function dbLink()
    {
        try {
            $this->pdo = new PDO("mysql:host=localhost:{$this->dbConfig['PORT']};
             dbname={$this->dbConfig['DB_NAME']}", $this->dbConfig['ADMIN'], $this->dbConfig['PASSWORD']);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//            var_dump($this->pdo);
            echo "Link success";
        } catch (\PDOException $e) {
            echo "failed：" . $e->getMessage();
        }

    }

    /**
     *
     * 条件约束
     *
     * @param $field //字段值
     * @param $operator //运算符
     * @param $value //约束值
     * @return $this  //非出口函数
     */
    public function where($field, $operator, $value,$type=null)
    {
         if($type=strtoupper($type)){
             if($type=='AND'||$type=='OR') {
                 $field="$type $field";
             }
         }
//        //判断where输入参数数量
//        if ($value == null) {
//            $value = $operator;
//            $operator = '=';
//        }
        $this->where[] = "$value";
        $this->where['SQL'][$field] = ['type'=>$type,'scent'=>"$field $operator $value"];
        return $this;
    }

    /**
     * 查询
     * 出口函数
     */
    public function get()
    {
        $columns="*";
        if(isset($this->columns)){
            $columns=implode(',',$this->columns);
        };
        $action = "SELECT $columns FROM";
        $this->DoStmt($action);
    }

    /**
     * @param $table  表名
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function join($table,$field1,$op,$field2){
        $this->join="JOIN $table ON $field1 $op $field2";
        return $this;
    }

    /**
     *  where语句拼接
     */
    public function whereImplode()
    {
        $whereString = 'WHERE' . ' ' . implode(' and ', $this->where['SQL']);
        unset($this->where['SQL']);

        return $whereString;
    }


    /**
     * 选择列
     */
    public function select(...$columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function find($id)
    {
        $columns="*";
        if(isset($this->columns)){
            $columns=implode(',',$this->columns);
        };
        $action = "SELECT {$columns} FROM";
        $this->where = null;
        $this->where($this->primaryKey, $id);
        var_dump($this->DoStmt($action)[0]);

    }

    /**
     * @param $action sql头部
     * @return string
     */
    public function DoStmt($action)
    {
//
        $where=$this->where?$this->whereImplode():"";
        $stmt = $this->pdo->prepare("$action $this->table $this->join $where");
        var_dump("$action $this->table $this->join $where");
        try{
            $stmt->execute($this->where);
        }catch (\SQLiteException $e){
            var_dump($e);
        }

        if (($result = $stmt->fetchAll()) == []) {
            $result = "not found";
        };
        $this->where=[];
        var_dump($result);
        return $result;
    }


    public function getWhere()
    {
        var_dump($this->where);
    }

}
//$model=new Model;
//$model->where('id','>=','1')->get();
