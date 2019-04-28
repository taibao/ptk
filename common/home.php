<?php
    define('SYS_PATH','sys/');
    define('MODLE_PATH','mymodel/');
    //加载公用文件
    include_once(SYS_PATH.'Controller.php');
    include_once(SYS_PATH.'Model.php');
    include_once(SYS_PATH.'db.php');
    include_once(SYS_PATH.'function.php');
    include_once(SYS_PATH.'url.php');
    include_once(SYS_PATH.'redis.php');
    session_start();
    date_default_timezone_set("PRC");
    error_reporting(0);
    /*
    *加载model文件
    *@paramn $modelname 数据表名字（model）
     */
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
