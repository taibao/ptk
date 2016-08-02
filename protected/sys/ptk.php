<?php
	//ptk初始化文件
	class PTK{
		public static function run()
		{
			//定义常量
			define('CONTROLLER_PATH','protected/controller/');
			define('VIEW_PATH','protected/view/');
			define('MODLE_PATH','protected/model/');
			//加载公用文件
			include(SYS_PATH.'Controller.php');
			$parm_arr = explode('/',substr($_SERVER['PATH_INFO'],1));
			$path = '';
			foreach($parm_arr as $k=>$v)
			{
				$controller = ucfirst($v).'Controller';
				if(file_exists(CONTROLLER_PATH.$path.$controller.'.php'))
				{
					$action = 'action'.ucfirst($parm_arr[$k+1]);
					$action_path = $path.$v.'/'.$parm_arr[$k+1];
					break;
				}
				$path .= $v.'/';
			}
			include_once(CONTROLLER_PATH.$path.$controller.'.php');
			$ptk = new $controller;
			$ptk->action_path = $action_path;
			$ptk->$action();
		}
	}
