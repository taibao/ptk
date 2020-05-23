<?php
	//wef初始化文件
	class app{
		public static $config;
		public static $_instance = null;//入口属性

		public static function run()
		{
			//定义常量
			define('VIEW_PATH','protected/view/');
			define('CONF_PATH','protected/config/');
			define('WIDGET_PATH','protected/widget/');
			//加载公用文件
			include_once(SYS_PATH.'widget.php');
			include_once(SYS_PATH.'function.php');
			include_once(SYS_PATH.'url.php');
			include_once(SYS_PATH.'config.php');
			self::$config = $config;
			define("APP_PATH",self::$config['url']['base_url'].self::$config['url']['app'].'/');//定义app路径
			define("ROOT_PATH",self::$config['url']['base_url']);//定义ROOT路径
			if(!array_key_exists('PATH_INFO',$_SERVER)){
				die('路径错误！');
			}

			$parm_arr = explode('/',substr($_SERVER['PATH_INFO'],1));
			$segment = 0;//参数开始的段数，在action的位置之后
			$get = array();//参数数组
			$i = 0;
			//遍历参数数组
			$path = '';
			foreach($parm_arr as $k=>$v)
			{
				if($segment == 0){
					$controller = $v;
					if(file_exists(CONTROLLER_PATH.$path.$controller.'.php'))
					{
						$action = $parm_arr[$k+1];
						$action_path = $path.$v.'/'.$parm_arr[$k+1];
						#$k+1为action所在的段数
						$segment = $k+2;//参数开始的段数
						continue;
					}
					//没有则代表是文件夹
					$path .= $v.'/'; //admin
				}
				else if($k>=$segment){
					//获取参数(以键/值的顺序组装)
					$parm_arr[$k+1] = !empty($parm_arr[$k+1])?$parm_arr[$k+1]:'';
					if($i%2==0){
						//键
						$get[$v] = $parm_arr[$k+1];
					}
					$i++;
				}
			}
			include_once(CONTROLLER_PATH.$path.$controller.'.php');
			if(!$app=self::getInstance()){
				$app = new $controller;
				self::$_instance = $app;
			}
			global $_W;
			global $_GPC;
			$app->_W = $_W;
			$app->_GPC = $_GPC;
			$app->action_path = $action_path;
			$app->get = $get;
			$app->$action();
		}

		//获取ptk对象
		public static function getInstance(){
			return self::$_instance;
		}
	}
