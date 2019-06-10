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
    //模板对应的父模板路径数组
    protected $tpl_parents = [];
    //模板定界符
    public $tag = ['{', '}'];
    //要处理的模板文件
    public $template = '';
    //当前解析的模板名称
    public $tpl = '';
    //模板文件后缀
    public $suffix = '';
    //母版占位符对应的文字
    private $tag_body = '';

    public function compiling()
    {
//      //layout又叫master支持，母版
        $this->extendsExp();
//

        //执行调用
        $this->includeExp();

//        执行判断语句
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

    /**
     * @param null $content /网页内容（若无则为父模板，之后则是判断子模板）
     * @param null $parent
     * @return null|string|string[]
     */
    public function includeExp($content = null, $parent = null)
    {
        $exp = 'include\s+(.*?)';
        $pattern = "/{$this->tag[0]}\s*{$exp}\s*{$this->tag[1]}/is";
        //匹配子模板名称，并且调用readTpl方法检测是否重复,如果没有include则会返回false
        if ($content) {
            //先找出匹配内容，再调用闭包函数将返回值替换匹配内容,$matches为匹配的内容
            return preg_replace_callback(
                $pattern,
                function ($matches) use ($parent) {
                    return $this->includeTpl($matches[1], $parent);
                },
                $content
            );
        } else {
            $this->template = preg_replace_callback(
                $pattern,
                function ($matches) use ($parent) {
                    return $this->includeTpl($matches[1], $parent);
                },
                $this->template
            );
        }
        //递归返回
        return $content;
    }

    /**
     * @param $sub /子模板
     * @param $parent
     * @return bool|null|string|string[]
     */
    protected function includeTpl($sub, $parent)
    {
        //判断是否是第一次嵌套include
        if (isset($this->tpl_parents[$parent])) {
            //复制上一份include记录
            $parent_path = $this->tpl_parents[$parent];
            //将自身存入上一份记录
            $parent_path[] = $parent;
            //检查模板是否死循环调用
            if (in_array($sub, $parent_path)) {
                die("{$parent} 调用 {$sub} 文件调用产生死循环");
            } else {
                //以子模板为键，当前记录为值，存入记录数组
                $this->tpl_parents[$sub] = $parent_path;
    
            }
        } else {
            //第一次添加记录  例 tpl_parents=['head'=>[null]]
            $this->tpl_parents[$sub] = [$parent];
        }

        return $this->readTpl($sub);
    }

    protected function readTpl($tpl)
    {
        //读取子模板
        $file = "{$this->base_dir}{$tpl}{$this->suffix}";

        if (!file_exists($file)) {
            die("{$file}文件不存在，或者不可读取");
        }
        //读取子模板内容
        $content = file_get_contents($file);
        //如果内容为空则跳出
        if ($content == "") {
            return false;
        }
        return $this->includeExp($content, $tpl);
    }

    //extends 母版标签
    public function extendsExp()
    {
        $exp = 'extends\s+(.*?)';
        $pattern = "/{$this->tag[0]}\s*{$exp}\s*{$this->tag[1]}/is";
        $ismatch = preg_match($pattern, $this->template, $matches);
        //当匹配中时
        if ($ismatch) {
            //读取母板
            $master = $this->readTpl($matches[1]);
            //读取母板接口
            $this->getTagBody($master);

            $exp_ph = '@(.*?)';
            $pattern = "/{$this->tag[0]}\s*{$exp_ph}\s*{$this->tag[1]}/is";
            //匹配模板接口，将之替换成相应内容
            $this->template = preg_replace_callback($pattern, [$this, 'replaceTag'], $master);

        }
    }

    /**
     * @param $matches
     * @return string 取出母版接口内容
     */
    private function replaceTag($matches)
    {
        $tag = $matches[1];
        return isset($this->tag_body[$tag]) ? $this->tag_body[$tag] : '';
    }

    /**
     * @param $master /母版
     */
    public function getTagBody($master)
    {
        //匹配的接口格式 例子 {@index}
        $exp = '@(.*?)';
        $pattern = "/{$this->tag[0]}\s*{$exp}\s*{$this->tag[1]}/is";
        preg_match_all($pattern, $master, $matches);
        $this->tag_body = [];
        //若母版匹配中接口时，循环每个接口名称，再将之作为pattern去匹配当前访问模板的内容
        foreach ($matches[1] as $ph) {
            //$ph=news_body   {news_body}(.*?){/news_body}
            $pattern_ph = "/{$this->tag[0]}\s*({$ph})\s*{$this->tag[1]}(.*?){$this->tag[0]}\s*\/{$ph}\s*{$this->tag[1]}/is";
            //            "/{$this->tag[0]}\s*({$ph})\s*{$this->tag[1]}(.*?){$this->tag[0]}\s*/{$ph}\s*{$this->tag[1]}/is"
            $ismatched = preg_match($pattern_ph, $this->template, $matches_ph);
            //若匹配中时将匹配内容赋值给tag_body 键为接口名称 值为匹配内容
            if ($ismatched) {
                $this->tag_body[$matches_ph[1]] = $matches_ph[2];
            }
        }
    }
}