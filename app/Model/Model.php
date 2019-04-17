<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/4/16
 * Time: 16:04
 */

namespace App\Model;

class Model
{
    //子类指定表
    protected $table;
    protected $primaryKey;
    //实例数据
    private $data = [];
    private $whereString = '';
    protected $dbConfig;
    //where条件
    private $where = [];
    private $group = "";
    private $pdo;
    //执行操作的头部
    private $action;
    private $stmt;

    public function __construct()
    {
        $this->dbConfig = require_once('../../config/database.php');
        $this->dbLink();
    }

    /**
     * 数据库连接
     */
    public function dbLink()
    {
        try {
            $this->pdo = new \PDO("mysql:host=localhost:{$this->dbConfig['PORT']};
             dbname={$this->dbConfig['DB_NAME']}", $this->dbConfig['ADMIN'], $this->dbConfig['PASSWORD']);
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
    public function where($field, $operator, $value = null)
    {
        //判断where输入参数数量
        if ($value == null) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[$field] = "$value";
        $this->where['SQL'][$field] = "$field $operator :$field";
        return $this;
    }

    /**
     * 查询
     * 出口函数
     */
    public function get()
    {
        //where 条件拼接
        $this->action = 'select * from';
        $this->stmtPrepare();

        try {
            $this->stmt->execute($this->where);
            $result = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
            var_dump($result);
        } catch (\Error $e) {
            echo $e;
        };
        $this->whereClear();
    }

    /**
     *  where语句拼接
     */
    public function whereImplode()
    {
        $this->whereString = 'WHERE' . ' ' . implode(' and ', $this->where['SQL']);
        unset($this->where['SQL']);
    }

    /**
     * where语句初始化
     */
    public function whereClear()
    {
        $this->where = [];
        $this->whereString = "";
    }

    public function find($id)
    {
        $this->action = 'select * from';
        $this->where = null;
        $this->where($this->primaryKey, $id);
        $this->stmtPrepare();
        $this->stmt->execute($this->where);

        try {
            $this->stmt->execute($this->where);
            $result = $this->stmt->fetch(\PDO::FETCH_ASSOC);
            var_dump($result);
        } catch (\Error $e) {
            echo $e;
        };
    }

    /**
     * 预处理
     */
    public function stmtPrepare()
    {
//         var_dump($this->pdo);
        $this->whereImplode();
        $this->stmt = $this->pdo->prepare($this->action . ' ' . $this->table . ' ' . $this->whereString);

    }


    public function getWhere()
    {
        var_dump($this->where);
    }

}
//$model=new Model;
//$model->where('id','>=','1')->get();
