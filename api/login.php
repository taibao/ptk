<?php
class login extends Controller{

    public function WeixinAuthUrl() {
        global $user_arr;
        $appid = I('appid');
        set_session('uniacid',$appid,'36000');
        $url = $user_arr['wx_auth']['url'].'/login/WeixinGetCode?appid='.$appid;
        $this->rewriteAuthUrl($url,1, $appid);
    }

    public function rewriteAuthUrl($url, $type=1,$appid) {
        $redirect_uri = urlencode($url);
        $scope = null;
        if($type == 1) {
            $scope = 'snsapi_base';
        }else{
            $scope = 'snsapi_userinfo';
        }
        $getWechats = $this->getWechatsInfo($appid);
        $this->getAccessToken($getWechats['key'],$getWechats['secret']); //保存基础token
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'
            .'appid='.$getWechats['key'].'&redirect_uri='.$redirect_uri
            .'&response_type=code&scope='.$scope.'&state=123#wechat_redirect';
        header("location:" . $url);
    }

    public function getWechatsInfo($appid) {
        $getWechats = array();
        if ($appid) {
            $map['uniacid'] = $appid;
            $AccountWechatsModel = loadModel('AccountWechats');
            $getWechats = $AccountWechatsModel->where($map)->find();
        }
        return $getWechats;
    }

    public function WeixinGetCode() {
        global $user_arr;
        $code = I('code');
        $appid = I('appid');
        if (!empty($code)) {
            $this->getWebAccessToken($code,$appid);
        } else {
            $url = $user_arr['wx_auth']['url'].'/login/WeixinGetCode?appid='.$appid;
            $this->rewriteAuthUrl($url,1,$appid);
        }
    }

    public function getWebAccessToken($code,$appid){
        global $user_arr;
        if(!get_session("weiqin_token_data".$appid)){
          $getWechats = $this->getWechatsInfo($appid);
          $url ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$getWechats['key'].'&secret='.$getWechats['secret'].'&code='.$code.'&grant_type=authorization_code';
          $getInfo = tocurl($url);
          $response = json_decode($getInfo,true);
          $token_data['access_token'] = $response['access_token'];
          $token_data['expires_in'] = $response['expires_in'];
          $token_data['refresh_token'] = $response['refresh_token'];
          $token_data['openid'] = $response['openid'];
          $token_data['scope'] = $response['scope'];
          set_session('weiqin_token_data'.$appid,$token_data,$token_data['expires_in']);
        }else{
          $response =  get_session('weiqin_token_data'.$appid);
        }
        $redirectUrl = $user_arr['wx_auth']['base_url'].'/web/nceApp/index.html?openId='.$response['openid'];
        header('location:'.$redirectUrl);
    }

    #获取接口基础access-token
    public function getAccessToken($appid='',$appSecret=''){
            $uniacid = get_session('uniacid');
          if(!$token_data=get_session('base_token_data'.$uniacid)){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appSecret;
            $getInfo = tocurl($url);
            $response = json_decode($getInfo,true);
            $token_data['access_token'] = $response['access_token'];
            $token_data['expires_in'] = $response['expires_in'];
            $token_data['appid'] = $appid;
            $token_data['secret'] = $appSecret;
            set_session('base_token_data'.$uniacid,$token_data,$token_data['expires_in']);
          }
    }

    //获取token
    public function getJsapiTicket($appid=''){
      $appid = get_session('uniacid');
      if(!$data = get_session("base_token_data".$appid)){
    		$response = getresponse('error',"用户尚未登录");
      }else{
        if(!$ticket_data=get_session("weiqin_ticket".$appid)){
          $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$data['access_token'].'&type=jsapi';
          $getInfo = tocurl($url);
          $response = json_decode($getInfo,true);
          $ticket_data['ticket'] = $response['ticket'];
          $ticket_data['expires_in'] = $response['expires_in'];
          set_session('weiqin_ticket'.$appid,$ticket_data,$ticket_data['expires_in']);
        }
        $time = time();
        $noncestr = $this->getRandomStr();
        $str = '';
        $str .= 'jsapi_ticket='.$ticket_data['ticket'];
        $str .= '&noncestr='.$noncestr;
        $str .= '&timestamp='.$time;
        $str .= '&url='.I('url');

        $result['appid'] = $data['appid'];
        $result['timestamp'] = $time;
        $result['nonceStr'] = $noncestr;
        $result['signature'] = sha1($str); //生成接口签名
        $response = getresponse('success',$result);
      }
      ajaxReturn($response);
   }

   //生成随机字符串
   public function getRandomStr(){
     $strs="QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
     $name=substr(str_shuffle($strs),mt_rand(0,strlen($strs)-11),16);
     return $name;
   }

  //返回用户提交信息
  public function update_user_info()
  {
    islogin();
    try{
        if(I(''))
        {
          $TblFanOmUserModel = loadmodel("TblFanOmUser");
          $TblOmUserModel = loadModel("TblOmUser");
          $TblAreasToOmUserModel = loadModel("TblAreasToOmUser");
          $map['open_id'] = I("openId");
          $user_map = $TblFanOmUserModel->findByMap("om_user_id",$map);
          if($user_map)
          {
            $user = $TblOmUserModel->findByMap("om_user_id,name,phone,country_code",$user_map);
            $user_areas = $TblAreasToOmUserModel->info("*",$user_map);
            $user_areas = array_column($user_areas,"areas_id");
            $user['areas'] = $user_areas;
            $response = getresponse('success',$user);
          }else{
            $response['status'] = false;
            $response['errorCode'] = "00005";
            $response['errDesc']  = "该用户不存在";
          }
        }
    }
    catch(Exception $e)
    {
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  //用于修改用户信息
  public function submit_user_change()
  {
    islogin();
    try{
        if(I(''))
        {
          $TblFanOmUserModel = loadmodel("TblFanOmUser");
          $TblOmUserModel = loadModel("TblOmUser");
          $TblAreasToOmUserModel = loadModel("TblAreasToOmUser");
          $TblAreasModel = loadModel("TblAreas");
          $McMappingFansModel = loadModel("McMappingFans");

          $search_map['openid'] = I('openId');
          $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid,uid",$search_map);
          $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
          $fan_user_data['uid'] = $getUnicaidInfo['uid'];
          $FanOmUser = $TblFanOmUserModel->findByMap("*",$fan_user_data);
          $user_map['om_user_id'] = $FanOmUser['om_user_id'];
          $user = $TblOmUserModel->findByMap("*",$user_map);

          if($user['verify']!='1'){
            #添加
            $om_user_data['name'] = $_POST['omUserName'];
            $om_user_data['phone'] = $_POST['phone'];
            $om_user_data['country_code'] = $_POST['country_code'];
            $om_user_data['create_time'] = time();
            $TblOmUserModel->where($user_map)->save($om_user_data);
            $TblOmUserModel->where($user_map)->save(array("verify"=>'0',"reject_remark"=>""));
            $TblAreasToOmUserModel->where($user_map)->delete();
            $areas_list = $_POST['areasidList'];
            foreach ($areas_list as $key => $value)
            {
                  $areas_om_user['om_user_id'] = $user_map['om_user_id'];
                  $areas_om_user['areas_id'] = $value;
                  $TblAreasToOmUserModel->insert($areas_om_user);
                  $getAllChild = $TblAreasModel->info('*', array('parent_id'=>$value));
                  foreach ($getAllChild as $k => $v) {
                      $areas_child_data['om_user_id'] = $user_map['om_user_id'];
                      $areas_child_data['areas_id'] = $v['areas_id'];
                      $TblAreasToOmUserModel->insert($areas_child_data);
                  }
              }
            }
          $response = getresponse('success',$user);
        }
    }
    catch(Exception $e)
    {
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  //创建用户
  public function AccountCreation()
  {
    islogin();
    $response['type'] = 'setting';
    if( $_POST['openId'] && $_POST['omUserName'] && $_POST['phone'] && $_POST['areasidList']) {
    try{
        if(I(''))
        {
          $model = new Model();
          $model->begin();

          $TblFanOmUserModel = loadmodel("TblFanOmUser");
          $TblOmUserModel = loadModel("TblOmUser");
          $TblAreasToOmUserModel = loadModel("TblAreasToOmUser");
          $TblAreasModel = loadModel("TblAreas");
          $McMappingFansModel = loadModel("McMappingFans");

          $search_map['openid'] = I('openId');
          $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid,uid",$search_map);
          $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
          $fan_user_data['uid'] = $getUnicaidInfo['uid'];
          $FanOmUser = $TblFanOmUserModel->findByMap("*",$fan_user_data);

          if(!$FanOmUser)
          {
              if (array_key_exists($_POST['openId'],$_SESSION)&&$_SESSION[$_POST['openId']]['data']['message_verify'] == $_POST['verify'])
              {
                  #添加
                  $om_user_data['name'] = $_POST['omUserName'];
                  $om_user_data['phone'] = $_POST['phone'];
                  $om_user_data['country_code'] = $_POST['country_code'];
                  $om_user_data['create_time'] = time();
                  $om_user_id =  $TblOmUserModel->insert($om_user_data);
                  if ($om_user_id) {
                      $fan_user_data['om_user_id'] = $om_user_id;
                      $fan_user_data['open_id'] = $_POST['openId'];
                      $TblFanOmUserModel->insert($fan_user_data);
                      $areas_list = $_POST['areasidList'];
                      if(!empty($areas_list)){
                        foreach ($areas_list as $key => $value) {
                            $areas_om_user['om_user_id'] = $om_user_id;
                            $areas_om_user['areas_id'] = $value;
                            $TblAreasToOmUserModel->insert($areas_om_user);
                            $getAllChild = $TblAreasModel->info('*',array('parent_id'=>$value));
                            foreach ($getAllChild as $key => $value) {
                                $areas_child_data['om_user_id'] = $om_user_id;
                                $areas_child_data['areas_id'] = $value['areas_id'];
                                $TblAreasToOmUserModel->insert($areas_child_data);
                            }
                        }
                      }
                      $model->commit();
                      $data['phone'] = $_POST['phone'];
                      $data['uniacid'] = $getUnicaidInfo['uniacid'];
                      $data['om_user_id'] = $om_user_id;
                      $data['omUserName'] = $_POST['omUserName'];
                      $response = getresponse('success',$data);
                  }else{
                    $model->rollBack();
                    $response['errorCode'] = '0001';
                    $response['errorDesc'] = '创建失败';
                  }
              } else {
                  $response['errorCode'] = '0001';
                  $response['errorDesc'] = '验证码错误';
              }
            }
            else
            {
                $response['errorCode'] = '0002';
                $response['errorDesc'] = '用户已经注册';
            }
        }
    }
    catch(Exception $e)
    {
      $response['errorCode'] = '0001';
      $response['errorDesc'] = '创建失败';
    }

  } else {
      $response['errorCode'] = '0001';
      $msg = '';
      if (!$_POST['openId']) {
          $msg .= '参数错误';
      }
      if (!$_POST['omUserName']) {
          $msg .= '装维人员为空';
      }
      if (!$_POST['phone']) {
          $msg .= '手机号码为空';
      }
      if (!$_POST['areasidList']) {
          $msg .= '区域地址未选';
      }
      $response['errorDesc'] = '创建失败，'.$msg;
  }

  ajaxReturn($response);
}

//用于修改用户信息
public function AreasInfo()
{
  islogin();
  try{
      if(I(''))
      {
        $TblAreasModel = loadModel("TblAreas");
        $McMappingFansModel = loadModel("McMappingFans");
        $TblWxappAreasModel = loadModel("TblWxappAreas");

        $search_map['openid'] = I('openId');
        $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid,uid",$search_map);

        $wxapp_map['uniacid']  = $getUnicaidInfo['uniacid'];
        $areas_id = $TblWxappAreasModel->info("areas_id",$wxapp_map);
        $areas_id = getcloumns($areas_id,"areas_id");
        $areas_id = join(",",$areas_id);
        $areas = array();
        if($areas_id!="")
        {
          $areas = $TblAreasModel->info('areas_id,name,parent_id','name is not null and parent_id = 0 and areas_id in ('.$areas_id.")");
          $this->areas = $TblAreasModel->info("*");
          foreach ($areas as $key => $value) {
            $sub_areas = $this->getchild($value['areas_id'],array());
            if($sub_areas){
              $areas[$key]['sub_areas'] = $sub_areas;
            }
          }
        }
        $response = getresponse('success',$areas);
      }
  }
  catch(Exception $e)
  {
      $response = getresponse('error');
  }
  ajaxReturn($response);
}

#递归找出该编号下的区域地址1
// public function getchild($id,$child_result,$level=1){
//     $temp = $id;
//     $TblAreasModel = loadModel("TblAreas");
//     $feild = array("areas_id","name","parent_id");
//     $sub_result = $this->getinfo($feild,$id);
//     foreach ($sub_result as $k => $v){
//         if($temp==$v['parent_id']){
//             $id = $v['areas_id'];
//             if($v['name']!=''){
//                 unset($arr);
//                 $arr = $v;
//                 $arr['level'] = $level;
//                 $child_result[] = $arr;
//             }
//             $child_result = $this->getchild($id,$child_result,$level+1);
//         }
//     }
//     return $child_result;
// }

#递归找出该编号下的区域地址2
public function getchild($id,$child_result,$level=1){
    $temp = $id;
    $TblAreasModel = loadModel("TblAreas");
    $feild = array("areas_id","name","parent_id");
    $sub_result = $this->getinfo($feild,$id);
    foreach ($sub_result as $k => $v){
        if($temp==$v['parent_id']){
            $id = $v['areas_id'];
            $map['parent_id'] = $v['areas_id'];
            if($TblAreasModel->where($map)->getCount()>0){
              $v['sub_areas'] = $this->getchild($id,$child_result,$level+1);
            }
            $child_result[] = $v;
        }
    }
    return $child_result;
}

public function getinfo($feild,$id){
    $arr = array();
    foreach ($this->areas as $value) {
        if($value['parent_id']!=$id){
            continue;
        }
        $data = array();
        foreach ($feild as $v2) {
            $data[$v2] = $value[$v2];
        }
        $arr[] = $data;
    }
    return $arr;
}

public function setlang(){
  $msg['status']='false';
  global $user_arr;
  $arr = array_keys($user_arr['lang_arr']);
  if(in_array(I('lang'),$arr))
  {
    set_session("user_lang",I("lang"),360000);
    $msg['status']='true';
    $msg['lang']=I('lang');
  }
  ajaxReturn($msg);
}

public function send_weixin_message()
{
    global $user_arr;
    $mac = I('mac');
    $type=I('type');//0/1
    $TblOmUserCareToGatewayModel = loadModel("TblOmUserCareToGateway");
    $TblFanOmUserModel = loadModel("TblFanOmUser");
    $TblGatewayWhiteListModel = loadModel('TblGatewayWhiteList');
    $map['mac']  = $mac;
    $gateway = $TblGatewayWhiteListModel->findByMap("install_address,areas_id,uniacid",$map);
    $appid = $gateway['uniacid'];
    #查找区域
    $TblAreasModel = loadModel("TblAreas");
    $areas_map['areas_id'] = $gateway['areas_id'];
    $areas = $TblAreasModel->findByMap("*",$areas_map);
    unset($pre_result);
    $pre_result = $TblAreasModel->getchildnum($areas['parent_id'],array(),$level=0);
    foreach ($pre_result as $k => $v) {
        $areas['name'] = $v['name'].'--'.$areas['name'];
    }
    if($type=="0")
    {
        $templateid=$user_arr['template_id']['online'];//上线
        $message['message']='您负责的网关'.$mac.'已上线，区域：'.$areas['name'].'，安装地址：'.$gateway['install_address'];
    }else{
        $templateid=$user_arr['template_id']['offline'];//下线
        $message['message']='您负责的网关'.$mac.'已下线，区域：'.$areas['name'].'，安装地址：'.$gateway['install_address'].'，请及时前往处理。';
    }

    $getWechats =  $this->getWechatsInfo($gateway['uniacid']);
    if(empty($_SESSION['base_token_data'.$appid]))
    {
        $this->getAccessToken($getWechats['key'],$getWechats['secret']);
    }
    #插入消息到消息中心
    $TblMessageModel = loadModel('TblMessage');
    $TblUserToMessageModel = loadModel('TblUserToMessage');
    #发送微信消息
    unset($map);
    $map['uniacid'] = $gateway['uniacid'];
    $om_user_cares = $TblFanOmUserModel->info("om_user_id,open_id",$map);
    $om_users = array_column($om_user_cares,"om_user_id");
    foreach($om_users as $v)
    {
        #消息中心
        $message['type'] = '3';
        $message['create_time'] = date("Y-m-d H:i:s");
        $message_id = $TblMessageModel->insert($message);
        $user_to_message['message_id'] = $message_id;
        $user_to_message['om_user_id'] = $v;
        $TblUserToMessageModel->insert($user_to_message);
    }
    $openids =array_column($om_user_cares,"open_id");
    if(!$openids)
    {
        $msg['status']='00005';
        $msg['err_desc']="没有装维人员关注该网关";
        ajaxReturn($msg);
    }
    $data = get_session("base_token_data".$appid);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
    $header[] = 'Content-Type:application/json';
    $url .="?access_token=".$data['access_token'];

    foreach ($openids as $v)
    {
        #微信消息
        $address = $gateway['install_address'];
        $openid = $v;
        $content ='{
           "touser":"'.$openid.'",
           "template_id":"'.$templateid.'",
           "url":"'.$user_arr['wx_auth']['base_url'].'/web/nceApp/html/message_center.html?openId='.$openid.'",
           "data":{
                    "mac": {
            "value":"'.$mac.'",
                       "color":"#01AAED"
                   },
                   "address":{
            "value":"'.$address.'",
                		"color":"#01AAED"
                   },
                   "area":{
            "value":"'.$areas['name'].'",
                		"color":"#01AAED"
                   }
           }
       }';
        $getInfo = tocurl($url, $header,'POST',$content);
        $response = json_decode($getInfo,true);
    }
    ajaxReturn($response);
}

    #返回进度
    public function rebackjindu()
    {
//        if(I('progress'))
//        {
//            $msg['status']='true';
//            $redis = new redisDb();
//            $progress = I('progress');
        $redis = new redisDb();
        $data = $redis->getStr("admin.rdprogress");
        $msg['jindu'] = $data;
        $msg['status']=false;


//            if(isset($data))
//            {
//                $msg['jindu'] = $data;
//                if($data=='100')
//                {
//                    $redis->delStr($progress);
//                }
//            }else{
//                $msg['jindu'] = '0';
//            }
//        }else{
//            $msg['status']=false;
//            $msg['jindu'] = '用户未登录！';
//        }
        ajaxReturn($msg);
    }
}
