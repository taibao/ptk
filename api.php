<?php
	$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
	header('Access-Control-Allow-Origin:*');
	header('Content-Type: text/html;charset=utf-8');
    header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
    header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
    header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin, X-Requested-With, Accept, Authorization, x-cookie, Cookie');
	include_once('common/home.php');
	#执行初始化方法
	include_once(SYS_PATH.'app.php');
	if(!get_session("token"))
		getAuth();
	APP::run();
