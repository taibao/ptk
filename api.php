<?php
	include_once('common/home.php');
	include_once("common/my.func.php");
	// #定义常量
	define('CONTROLLER_PATH','./api/');
	#执行初始化方法
	include_once(SYS_PATH.'app.php');
	if(!get_session("token")) getAuth();
	APP::run();

	#获取token
	function getAuth(){
		$param['username'] = 'admin';
		$param['password'] = 'admin';
		$url = "http://114.116.186.36:8765/api/jwt/auth";
		$response = tocurl($url,array(),"POST",$param);
		$response = json_decode($response,true);
		set_session('token',$response['token'],$response['expiresIn']);
	}
