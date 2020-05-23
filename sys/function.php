<?php

  #获得访客浏览器类型
    function Get_Brows(){
        if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i',$br)) {
                $br = 'MSIE';
            }
            elseif (preg_match('/Firefox/i',$br)) {
                 $br = 'Firefox';
            }
            elseif (preg_match('/Chrome/i',$br)) {
                 $br = 'Chrome';
               }
               elseif (preg_match('/Safari/i',$br)) {
                 $br = 'Safari';
            }
            elseif (preg_match('/Opera/i',$br)) {
                $br = 'Opera';
            }else {
                $br = 'Other';
            }
            return $br;
           }
           else{
               return "unknow";
           }
  }

  #获得访客浏览器语言
    function Get_Lang(){
        if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang,0,8);
            if(preg_match("/zh-cn/i",$lang)){
                 $lang = "zh_cn";
            }
            elseif(preg_match("/zh/i",$lang)){
                 $lang = "zh_i";
            }
            else{
                $lang = "English";
            }
            return $lang;
           }
           else{
               return "unknow";
           }
  }

   #获取访客操作系统
    function Get_Os(){
           if(!empty($_SERVER['HTTP_USER_AGENT'])){
            $OS = $_SERVER['HTTP_USER_AGENT'];
              if (preg_match('/win/i',$OS)) {
                 $OS = 'Windows';
            }
            elseif (preg_match('/mac/i',$OS)) {
                 $OS = 'MAC';
            }
            elseif (preg_match('/linux/i',$OS)) {
                 $OS = 'Linux';
            }
            elseif (preg_match('/unix/i',$OS)) {
                 $OS = 'Unix';
            }
            elseif (preg_match('/bsd/i',$OS)) {
                 $OS = 'BSD';
            }
            else {
                 $OS = 'Other';
            }
            return $OS;
           }
           else{
               return "unknow";
           }
  }

	#获得本地真实IP
	function get_onlineip() {
				$ip_json = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=myip");
				$ip_arr=json_decode(stripslashes($ip_json),1);
				if($ip_arr['code']==0)
				{
						return $ip_arr['data']['ip'];
				}

	 }

  #获得访客真实ip
    function Get_Ip_Addr(){
           if(!empty($_SERVER["HTTP_CLIENT_IP"])){
              $ip = $_SERVER["HTTP_CLIENT_IP"];
           }
           if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //获取代理ip
            $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
           }
           if($ip){
              $ips = array_unshift($ips,$ip);
           }
           $count = count($ips);
        for($i=0;$i<$count;$i++){
             if(!preg_match("/^(10|172\.16|192\.168)\./i",$ips[$i])){//排除局域网ip
                  $ip = $ips[$i];
                  break;
              }
        }
           $tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
           if($tip=="127.0.0.1"){ //获得本地真实IP
              return get_onlineip();
           }
           else{
              return $tip;
           }
  }

  #根据ip获得访客所在地地名
    function Get_Ip_From($ip=''){
          if(empty($ip)){
              $ip = Get_Ip_Addr();
          }
         $ip_json=@file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=".$ip);//根据taobao ip
         $ip_arr=json_decode(stripslashes($ip_json),1);
         if($ip_arr['code']==0)
         {
             return $ip_arr;
         }
         else
         {
             return false;
         }
    }

    function user_lang(){
      $user_lang = array();
      $lang=Get_Lang();
      if(!empty(get_session('user_lang')))
      {
        $lang=get_session('user_lang');
      }else{
        set_session("user_lang",$lang);
      }
      switch ($lang){
        case 'zh_cn':
          $user_lang = include_once($_SERVER['DOCUMENT_ROOT'].'/wef/sys/lang/const_zh_cn.php');
          break;
        case 'zh_i':
          $user_lang = include_once($_SERVER['DOCUMENT_ROOT'].'/wef/sys/lang/const_zh_cn.php');
          break;
        case 'English':
          $user_lang = include_once($_SERVER['DOCUMENT_ROOT'].'/wef/sys/lang/const_en.php');
        break;
        default:
          $user_lang = include_once($_SERVER['DOCUMENT_ROOT'].'/wef/sys/lang/const_zh_cn.php');
        break;
      }
      return $user_lang;
    }
