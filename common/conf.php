<?php
//用户配置文件
return $user_arr =   array(
  "agent_auth"=>array(
    'username'=>'admin',
    'password'=>'HwYauL35HyTtlp',
    'url'=>'http://114.116.186.36:8765/',//测试
  ),
  "nce_auth"=>array(
    'header'=>'https://114.116.235.74:31943/',//测试平台,op是运维面的
    'username'=>'xiaohuiTenant1',
    'password'=>'Changeme_123',
  ),
  'ftp_server'=>array(
    'ftpHost' => "114.115.145.212",
    'ftpUserName' => "admin",
    'ftpPassword' => "NetOpen_Huawei#@ftp2018",
    'ftpPort' => 21,
    'ftpDownloadPath' => "/public",
    'ftpUploadPath' => "/public",
    'ftpName' => "111.log",
    'fileSize' => 5419045,
  ),
  "database"=>array(
    'default'=>array(
      'type'=>'mysql',
      'host'=>'127.0.0.1',
      'password'=>'',
      'dbname'=>'we7',
      'username'=>'root',
      'charset'=>'utf8',
      'port'=>'3306'
    ),
    'middle'=>array(
      'type'=>'mysql',
      'host'=>'127.0.0.1',
      'password'=>'',
      'dbname'=>'nfcsykddb',
      'username'=>'root',
      'charset'=>'utf8',
      'port'=>'3306'
    ),
    'slave1'=>array(
      'type'=>'mysql',
      'host'=>'192.168.0.154',
      'password'=>'!Yts67ioGszew1',
      'dbname'=>'we7',
      'username'=>'root',
      'charset'=>'utf8',
      'port'=>'3306'
    ),
    'slave2'=>array(
      'type'=>'mysql',
      'host'=>'192.168.0.24',
      'password'=>'!Yts67ioGszew2',
      'dbname'=>'we7',
      'username'=>'root',
      'charset'=>'utf8',
      'port'=>'3306'
    ),
  ),
  "sms"=>array(
    'sendapi'=>'http://114.116.54.147:8762/captcha/phoneCaptcha',
  ),
  "redis"=>array(
      'ip'=>'192.168.0.148',
      'port'=>'6397',
      'passwd'=>'6754tys23ngYpL',
      'database'=>'10',
  ),
  //微信公众号跳转url
  "wx_auth"=>array(
    'base_url'=>'http://ncefan.cn',
    'url'=>'http://ncefan.cn/wef/api.php',
  ),
  "lang_arr"=>array(
    'zh_cn'=>'中文简体',
    // 'zh_i'=>'中文繁體',
    'English'=>'English'
  ),
    "template_id"=>array(
        "online"=>'_0ANB2GissdaaigO-S90l7mEkofeWDgKfuFm2iYamN8',
        "offline"=>'qb8PoImD9dW7jdCDStEfMg_cvB-toP_A2cv3K4jnj-g'
    ),
);
