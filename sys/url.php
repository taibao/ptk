<?php
	//url初始化文件
	class url{
		/*
		*得到地址
		*@param $param 入口文件的参数
		*/
		public function siteUrl($param = ''){
			$url = PTK::$config['url']['base_url'].PTK::$config['url']['app'].'/'.$param;
			return $url;
		}
	}
