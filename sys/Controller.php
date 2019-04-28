<?php

// include_once($_SERVER['DOCUMENT_ROOT'].'framework\class\account\');
//控制器基类
class Controller{
    public $action_path = '';
    protected $layout = '';
    protected $assign = array();
    protected $model = null;
    public $_W = array();
    public $_GPC = array();

    public $get = array();
	  public $url = '';
    public $const_arr = array();
    /*
    构造函数
     */
    public function __construct(){
        switch ($lang=Get_Lang()){
          case 'zh_cn':
            $this->const_arr = include_once(SYS_PATH.'lang/const_zh_cn.php');
            break;
          case 'zh_i':
            $this->const_arr = include_once(SYS_PATH.'lang/const_zh_i.php');
            break;
          case 'English':
            $this->const_arr = include_once(SYS_PATH.'lang/const_en.php');
          break;
          default:
            $this->const_arr = include_once(SYS_PATH.'lang/const_zh_cn.php');
          break;
        }
		    $this->url = new url;
    }

    public function set_lang($lang='zh_cn'){
      switch ($lang){
        case 'zh_cn':
          $this->const_arr = include_once(SYS_PATH.'lang/const_zh_cn.php');
          break;
        case 'zh_i':
          $this->const_arr = include_once(SYS_PATH.'lang/const_zh_i.php');
          break;
        case 'English':
          $this->const_arr = include_once(SYS_PATH.'lang/const_en.php');
        break;
        default:
          $this->const_arr = include_once(SYS_PATH.'lang/const_zh_cn.php');
        break;
      }
    }
    /**
     * 传参 [变量名=>值]
     */
    protected function assign($key,$value){
        $this->assign[$key]=$value;
        extract($this->assign);
    }
    /**
     * 加载视图
     */
    protected function view($view = ''){
        extract($this->assign);//视图需要动态的声明变量，所以这个方法必须放在view方法中完成
        //缓存区执行，在此加载content层的内容
        $content = $this->fetch($view);
        $defineDir = $_SERVER['DOCUMENT_ROOT'].'addons/ncefan_huawei/template/';
        $source = $defineDir."{$view}.html";
        if($source){
            include(VIEW_PATH.'layout/'.$this->layout.'.html');//加载layout层
        }
    }

    public function loadModel($modelname){
        static $_model  =   array();
        $modelname = ucfirst($modelname);//首字母大写
        $model_class = $modelname.'Model';
        if(isset($_model[$model_class]))
            return $_model[$model_class];
        if(file_exists(MODLE_PATH.$model_class.'.php')){
            include_once(MODLE_PATH.$model_class.'.php');
            $model_class = $modelname.'Model';
            $model = new $model_class; //防止model覆盖
            $_model[$model_class]  =  $model;
            return $model;
        }

    }

    /**
     * 返回视图内容
     */
    protected function fetch($content = ''){
        extract($this->assign);
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

	/*
		页面跳转函数
	*/
	protected function redirect($url){
		$host = $_SERVER['HTTP_HOST'];
		if(strripos("http",$url)>0)
		{
			$url = $url;
		}
		else if(count(explode("/",$url))>1)
		{
			$url = 'http://'.$host.$_SERVER['SCRIPT_NAME'].'/'.$url;
		}
		else{

			$path_info = $_SERVER['PATH_INFO'];
			$param = explode("/",$path_info);
			$url =  'http://'.$host.$_SERVER['SCRIPT_NAME'].'/'.$param[1].'/'.$param[2].'/'.$url;
		}
		header('location:'.$url);
		exit;
	}


}
