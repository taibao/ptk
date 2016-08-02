<?php
//控制器基类
class Controller{
    protected $layout = 'admin';
    protected $assign = array();
    protected $model = null;
    /**
     * 传参 [变量名=>值]
     */
    protected function assign($key,$value){
        $this->assign[$key]=$value;//$this->assign['a']=$b;
        //extract($this->assign);//$a = $b，得到是局部变量
    }
    /**
     * 加载视图
     */
    protected function view($view = ''){
        //解析参数
        extract($this->assign);//视图需要动态的声明变量，所以这个方法必须放在view方法中完成
        //缓存区执行，在此加载content层的内容
        $content = $this->fetch($view);
        include(VIEW_PATH.'layout/'.$this->layout.'.html');//加载layout层
    }
    /**
     * 返回视图内容
     */
    protected function fetch($content = ''){
        echo $this->action_path;
        //缓存区执行，在此加载content层的内容
        ob_start();
        if(empty($content)){
            $content = $this->action_path;//当内容层为空，视为默认加载与本路径相同的视图
        }
        include(VIEW_PATH.$content.'.html');
        $content = ob_get_contents();//内容层内容
        ob_end_clean();
        return $content;
    }
}







