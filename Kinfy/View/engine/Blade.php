<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 11:31
 */

namespace Kinfy\View\engine;


class Blade implements Iengine
{
    //模板文件根目录
    public $base_dir;
    //模板子模板数组
    protected $sub_tpls = [];
    //模板定界符
    public $tag = ['{', '}'];
    //要处理的模板文件
    public $template = '';
    //模板文件的后缀
    public $suffix = '';

    public function compiling()
    {
//        //layout又叫master支持，母版
//        $this->extendsExp();
//
//        //执行调用
          $this->includeExp();
//
//        //执行判断语句
//        $this->ifExp();
//        $this->elseifExp();
//        $this->elseExp();
//        $this->endifExp();
//
//        //循环语句
          $this->loopExp();
          $this->endloopExp();

        //变量
        $this->varExp();
        //$this->template = '编译开始:' . $this->template . ':编译结束';
    }

    /**
     * @param $find /花括号内部匹配准则
     * @param $replace /替换后的代码
     */
    protected function _replace($find, $replace)
    {
        $parttern = "/{$this->tag[0]}\s*{$find}\s*{$this->tag[1]}/is";
        $this->template = preg_replace(
            $parttern,
            $replace,
            $this->template
        );
    }

    /**
     * 变量寻找&&替换
     */
    public function varExp()
    {
        //提取变量名
        $exp = '(\$[a-zA-Z_][0-9a-zA-Z_\'\"\[\]]*)';
        //判断是否存在变量，若存在就输出来
        $replace = "<?php if(isset(\\1)){echo \\1;}?>";
        $this->_replace($exp, $replace);
    }

    //loop标签
    public function loopExp()
    {
        $exp = '(([a-zA-Z]+)\s*:\s*)?loop\s+(.*?)\s+in\s+(.*?)';
        $replace = "<?php
                        if(is_array(\\4)){
                            \$\\2_COUNT  = count(\\4);
                        }
                        \$\\2_INDEX = 0;
                        foreach( \\4 as \\3){
                            \$\\2_INDEX++;
                    ?>";
        $this->_replace($exp, $replace);
    }

    //end loop
    public function endLoopExp()
    {
        $exp = '\/loop';
        $replace = '<?php } ?>';
        $this->_replace($exp, $replace);
    }

    //include
    public function includeExp($content = null)
    {
        $exp = 'include\s+(.*?)';
        $pattern = "/{$this->tag[0]}\s*{$exp}\s*{$this->tag[1]}/is";
        //匹配子模板名称，并且调用readTpl方法检测是否重复,如果没有include则会返回false
        if($content){
            return preg_replace_callback(
                $pattern,
                [$this, 'includeTpl'],
                $content
            );
        }else{
            $this->template = preg_replace_callback(
                $pattern,
                [$this, 'includeTpl'],
                $this->template
            );
        }

        return $content;
    }

    protected function includeTpl($matches)
    {
        return $this->readTpl($matches[1]);
    }

    private function readTpl($tpl)
    {
        //检查模板是否死循环调用
        if (in_array($tpl, $this->sub_tpls)) {
            die("{$tpl} 文件调用产生死循环");
        } else {
            $this->sub_tpls[] = $tpl;
        }
        //调用子模板
        $file = "{$this->base_dir}/{$tpl}{$this->suffix}";
        if (!file_exists($file)) {
            die("{$file}文件不存在，或者不可读取");
        }
        $content = file_get_contents($file);
        //如果内容为空则跳出
        if($content == ""){
            return false;
        }
        return $this->includeExp($content);
    }
}