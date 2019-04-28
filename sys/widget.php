<?php
	abstract class Widget{

		abstract public function init();
		abstract public function run();
		static public $obj =null;

		final public static function begin($widget,$data=array()){
			$widget_class = ucfirst($widget).'Widget';
			if(!file_exists(WIDGET_PATH.$widget_class.'.php')){
				return false;
			}
			include_once(WIDGET_PATH.$widget_class.'.php');
			static::$obj = new $widget_class;
			foreach($data as $k=>$v){
				static::$obj->$k = $v;
			}
			static::$obj->init();
			return static::$obj;
		}

		final static public function end(){
			echo iconv("gb2312","utf-8",static::$obj->run());
		}

		final static public function w($widget,$data=array()){
			static::begin($widget,$data);
			static::end();
		}

	}
