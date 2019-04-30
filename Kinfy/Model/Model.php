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
    protected $dbConfig = [
        'PORT' => '3306',
        'DB_NAME' => 'kinfy',
        'ADMIN' => 'root',
        'PASSWORD' => ''
    ];
    //where条件
    private $where = [];
    private $group = "";
    private $values = [];
    private $join = "";
    private $fields = null;
    private $pdo;
    private $insertKey = [];

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
    public function where($field, $operator, $value, $type = null)
    {
        $total_type = null;
        if ($type = strtoupper($type)) {
            if ($type == 'AND' || $type == 'OR') {
                $total_type = $type;
            }
        }
//        //判断where输入参数数量
//        if ($value == null) {
//            $value = $operator;
//            $operator = '=';
//        }
        $this->values[] = "$value";
        $this->where[] = ['type' => $total_type, 'sentence' => "$field $operator ?"];
        return $this;
    }

    /**
     * 查询
     * 出口函数
     */
    public function get()
    {
        return $this->DoStmt('SELECT')->fetchAll();
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

    public function join($table, $field1, $op, $field2)
    {
        $this->join = "JOIN $table ON $field1 $op $field2";
        return $this;
    }

    /**
     *  where语句拼接
     */
    public function whereImplode()
    {
        $whereString = "WHERE";
        foreach ($this->where as $k => $v) {
            $whereString .= " {$v['type']} {$v['sentence']}";
        }
        return $whereString;
    }


    /**
     * 选择列
     */
    public function select(...$fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function find($id)
    {
        $columns = "*";
        if (isset($this->fields)) {
            $columns = implode(',', $this->fields);
        };
        $action = "SELECT {$columns} FROM";
        $this->where($this->primaryKey,'=', $id);
        var_dump($this->DoStmt('SELECT')[0]);

    }

    /**
     * @param $action SQL头部
     * @return string
     */
    public function DoStmt($action,$SQLvalues=null)
    {
        $fields=isset($this->fields)? $this->fields : '*';

        switch ($action) {
            case 'SELECT':
                $where = $this->where ? $this->whereImplode() : "";
                $sql="SELECT {$fields} FROM {$this->table} {$this->join} {$where}";
                break;
            case 'INSERT':
                $sql="INSERT INTO {$this->table} ({$fields}) VALUES {$SQLvalues}";
                break;
        }
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute($this->values);

        //抛出异常
        if($err=$stmt->errorInfo()[2]){
            var_dump($err);
            die;
        }

        return $stmt;
    }


    public function getWhere()
    {
        var_dump($this->where);
    }


    /**
     * @param $values
     * @param bool $force_align 是否排序（适用于字段值相同的多数据插入）
     */
    public function batchInsert($values, $force_align = true)
    {
        foreach ($values as $value) {
            //循环数组,将每个元素的键排序
            ksort($value);
            foreach ($value as $v) {
                $this->values[] = $v;
            }
        }

        //取出需要插入的字段名
        $this->fields = implode(',', array_keys($value));

        //预处理value（？）拼接
        $ph = array_pad($SQLvalues = [], sizeof($value), '?');
        $ph = implode(",", $ph);

        $SQLvalues = "";
        for ($i = 1; $i <= sizeof($values); $i++) {
            $SQLvalues .= "({$ph}),";
        }

        $SQLvalues=trim($SQLvalues, ",");

        $this->DoStmt('INSERT', $SQLvalues);
    }

}
//[a-zA-Z][a-zA-Z_0-9]{7,9}
//cjd_123456
//$model=new Model;
//$model->where('id','>=','1')->get();
