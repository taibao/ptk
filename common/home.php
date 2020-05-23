<?php
    header('Access-Control-Allow-Origin:*');
    header('X-Frame-Options:Deny');
    define('SYS_PATH',$_SERVER['DOCUMENT_ROOT'].'/wef/sys/');
    define('MODLE_PATH',$_SERVER['DOCUMENT_ROOT'].'/wef/mymodel/');
    define('COMMON_PATH',$_SERVER['DOCUMENT_ROOT'].'/wef/common/');
    // #定义常量
    define('CONTROLLER_PATH',$_SERVER['DOCUMENT_ROOT'].'/wef/api/');
    // error_reporting(0);
    global $user_arr;
    if(empty($user_arr)){
      $user_arr = include_once(COMMON_PATH.'conf.php');
    }
    //加载公用文件
    include_once(COMMON_PATH."my.func.php");
    include_once(SYS_PATH.'Controller.php');
    include_once(SYS_PATH.'Model.php');
    include_once(SYS_PATH.'db.php');
    include_once(COMMON_PATH."session0.php"); //保存session到数据库中
    session_start();
    ob_clean();
    include_once(SYS_PATH.'function.php');
    global $user_lang;

    if(empty($user_lang))
    {
      $user_lang = user_lang();
    }

    include_once(SYS_PATH.'url.php');
    include_once(SYS_PATH.'redis.php');
    date_default_timezone_set("PRC");
    header("Content-Type: text/html;charset=utf-8");
    ini_set("memory_limit","2048M");
    function loadModel($modelname){
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
        }else{
          $model = new Model($modelname); //防止model覆盖
          $_model[$model_class]  =  $model;
          return $model;
        }
    }
