<?php
/**
* 用户事件接口
*/
class user extends Controller{
  /**
  *功能:用户创建
  *param: openId (String)
  */
  public function OmUserAuthApi()
  {
    if($_POST){
        $openId = $_POST['openId'];
        if ($openId) {
            $response['type'] = 'geting';
            try{
                $search_map['openid'] = $_POST['openId'];
                $TblFanOmUserModel = loadmodel("TblFanOmUser");
                $TblOmUserModel = loadModel("TblOmUser");
                $McMappingFansModel = loadModel("McMappingFans");
                $McMembersModel = loadModel("McMembers");

                $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid,uid",$search_map);
                $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
                $fan_user_data['uid'] = $getUnicaidInfo['uid'];

                $getOmUserImg = $McMembersModel->findByMap("nickname,avatar",$fan_user_data);
                $getFanOmUserInfo = $TblFanOmUserModel->findByMap("om_user_id",$fan_user_data);

                if (empty($getFanOmUserInfo)) {
                    $response['errorCode'] = '0007';
                    $response['errorDesc'] = '用户未注册';
                } else {
                    $search_map_om_user['om_user_id'] = $getFanOmUserInfo['om_user_id'];
                    $getOmUserInfo = $TblOmUserModel->findByMap("verify,reject_remark,om_user_id,first_entry,name",$search_map_om_user); //查询用户信息
                    if ($getOmUserInfo['verify'] == 0 && empty($getOmUserInfo['reject_remark'])) {
                        $response['errorCode'] = '0009';
                        $response['errorDesc'] = '正在审核请稍后';
                    } elseif ($getOmUserInfo['verify'] == 0 && !empty($getOmUserInfo['reject_remark'])) {
                        $response['errorCode'] = '0006';
                        $response['errorDesc'] = $getOmUserInfo['reject_remark'] . '审核未通过';
                    } elseif (($getOmUserInfo['verify'] == 1 && $getOmUserInfo['first_entry'] != 0)) {
                        $response['errorCode'] = '0000';
                        $response['errorDesc'] = '审核通过';
                    } elseif ($getOmUserInfo['verify'] == 3) {
                        $response['errorCode'] = '0010';
                        $response['errorDesc'] = '用户退出再进入';
                    } elseif ($getOmUserInfo['first_entry'] == 0) {

                        $response['errorCode'] = '0008';
                        $response['errorDesc'] = '用户第一次进入';
                    } else {
                        $response['errorCode'] = '0002';
                        $response['errorDesc'] = '审核异常';
                    }
                    $om_user_info = array_merge($getOmUserImg,$getOmUserInfo);
                }
                $om_user_info['openid']=$openId;
                $response['data'] = $om_user_info;

            }catch(Exception $e){
                $response['errorCode']='0005';
                $response['errorDesc']='参数传递出错';
            }
        } else {
            $response['errorCode']='0005';
            $response['errorDesc']='参数不能为空';
        }


    } else {
        $response['errorCode']='0005';
        $response['errorDesc']='提交方式错误';
    }
    ajaxReturn($response,'json');
  }

  /**
  *功能:用户第一次进入
  *param: openId (String)
  */
  public function OmUserFirstEntryApi(){
    if($_POST){
        $openId = $_POST['openId'];
        if ($openId) {
            $response['type'] = 'geting';
            try{
                $TblFanOmUserModel = loadmodel("TblFanOmUser");
                $TblOmUserModel = loadModel("TblOmUser");
                $McMappingFansModel = loadModel("McMappingFans");

                $search_map['openid'] = $_POST['openId'];
                $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid,uid",$search_map);
                $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
                $fan_user_data['uid'] = $getUnicaidInfo['uid'];
                $getOmUserInfo = $TblFanOmUserModel->findByMap("om_user_id",$fan_user_data);

                if (empty($getOmUserInfo)) {
                    $response['errorCode'] = '0007';
                    $response['errorDesc'] = '用户未注册';
                } else {
                    $search_map_om_user['om_user_id'] = $getOmUserInfo['om_user_id'];
                    $getOmUserInfo = $TblOmUserModel->findByMap("verify,reject_remark,om_user_id,first_entry",$search_map_om_user);
                    $omFirstEntry['first_entry'] = $getOmUserInfo['first_entry'] +1;
                    $FirstEntryOmUserId['om_user_id'] = $getOmUserInfo['om_user_id'];
                    $row=$TblOmUserModel->where($FirstEntryOmUserId)->save($omFirstEntry);
                    if ($row) {
                        $response['errorCode']='0000';
                        $response['errorDesc']='用户已经进入系统';
                    } else {
                        $response['errorCode']='0002';
                        $response['errorDesc']='用户进入失败';
                    }
                }
            }catch(Exception $e){
                $response['errorCode']='0005';
                $response['errorDesc']='参数传递出错';
            }
        } else {
            $response['errorCode']='0005';
            $response['errorDesc']='参数不能为空';
        }


    } else {
        $response['errorCode']='0005';
        $response['errorDesc']='提交方式错误';
    }
    ajaxReturn($response,'json');
  }

/**
*功能:用户第一次进入
*param: openId (String)
*/
public function reenter()
{
  if($_POST){
      $openId = $_POST['openId'];
      if ($openId) {
          $response['type'] = 'geting';
          try{
              $TblFanOmUserModel = loadmodel("TblFanOmUser");
              $TblOmUserModel = loadModel("TblOmUser");
              $McMappingFansModel = loadModel("McMappingFans");
              $McMembersModel = loadModel("McMembers");

              $search_map['openid'] = $_POST['openId'];
              $getUnicaidInfo =  $McMappingFansModel->findByMap("uniacid,uid",$search_map);
              $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
              $fan_user_data['uid'] = $getUnicaidInfo['uid'];

              $getOmUserImg = $McMembersModel->findByMap("nickname,avatar",$fan_user_data);
              $getFanOmUserInfo = $TblFanOmUserModel->findByMap("om_user_id",$fan_user_data);
              if (empty($getFanOmUserInfo)) {
                  $response['errorCode'] = '0007';
                  $response['errorDesc'] = '用户未注册';
              } else {
                  $search_map_om_user['om_user_id'] = $getFanOmUserInfo['om_user_id'];
                  $reRegistData['verify'] = 1;
                  $reRegistData['reject_remark'] ='';
                  $reRegistData['first_entry'] = 1;
                  $res=$TblOmUserModel->where($search_map_om_user)->save($reRegistData);
                  if ($res) {
                      $response['errorCode'] = '0000';
                      $response['errorDesc'] = '重新进入成功';
                  } else {
                      $response['errorCode'] = '0002';
                      $response['errorDesc'] = '重新进入失败';
                  }
              }
          }catch(Exception $e){
              $response['errorCode']='0005';
              $response['errorDesc']='参数传递出错';
          }
      } else {
          $response['errorCode']='0005';
          $response['errorDesc']='参数不能为空';
      }

  } else {
      $response['errorCode']='0005';
      $response['errorDesc']='提交方式错误';
  }
  ajaxReturn($response,'json');
}

/**
*功能:用户重新注册
*param: openId (String)
*/
public function reRegistration(){
  if($_POST){
      $openId = $_POST['openId'];
      if ($openId) {
          $response['type'] = 'geting';
          $TblFanOmUserModel = loadmodel("TblFanOmUser");
          $TblOmUserModel = loadModel("TblOmUser");
          $McMappingFansModel = loadModel("McMappingFans");
          $McMembersModel = loadModel("McMembers");
          try{
              $search_map['openid'] = $_POST['openId'];
              $getUnicaidInfo =  $McMappingFansModel->findByMap("uniacid,uid",$search_map);
              $fan_user_data['uniacid'] = $getUnicaidInfo['uniacid'];
              $fan_user_data['uid'] = $getUnicaidInfo['uid'];
              $getOmUserImg = $McMembersModel->findByMap("nickname,avatar",$fan_user_data);
              $getFanOmUserInfo = $TblFanOmUserModel->findByMap("om_user_id",$fan_user_data);

              if (empty($getFanOmUserInfo)) {
                  $response['errorCode'] = '0007';
                  $response['errorDesc'] = '用户未注册';
              } else {
                  $search_map_om_user['om_user_id'] = $getFanOmUserInfo['om_user_id'];
                  $reRegistData['verify'] = 0;
                  $reRegistData['reject_remark'] ='';
                  $reRegistData['first_entry'] = 0;
                  $res=$TblOmUserModel->where($search_map_om_user)->save($reRegistData);
                  if ($res) {
                      $response['errorCode'] = '0000';
                      $response['errorDesc'] = '重新注册成功';
                  } else {
                      $response['errorCode'] = '0002';
                      $response['errorDesc'] = '重新注册成功';
                  }
              }
          }catch(Exception $e){
              $response['errorCode']='0005';
              $response['errorDesc']='参数传递出错';
          }
      } else {
          $response['errorCode']='0005';
          $response['errorDesc']='参数不能为空';
      }


  } else {
      $response['errorCode']='0005';
      $response['errorDesc']='提交方式错误';
  }
  ajaxReturn($response,'json');
}

/**
*功能:装维人员短信发送
*param: openId (String)
*param: countryCode (String) 区号
*param: phone (String) 手机号码
*/
public function SMSManagementApi()
{
  #T02
  #短信发送
  if($_POST){
  	$data['countryCode'] = $_POST['countryCode'];
  	$data['phone'] = $_POST['phone'];
  	$openId = $_POST['openId'];

    $McMappingFansModel = loadModel("McMappingFans");
  	$search_map['openid'] = $openId;
    $getUnicaidInfo =  $McMappingFansModel->findByMap("uniacid,uid",$search_map);
  		if(!empty($getUnicaidInfo)){
  	    if(sendsms($data,$openId)){
  				$response['errorCode']='0000';
  				$response['errorDesc']='发送成功！';
  	    }else{
  			$response['errorCode']='0005';
  			$response['errorDesc']='发送失败';
  	    }
  		}else{
  			$response['errorCode']='0005';
  			$response['errorDesc']='该微信用户不存在';
  		}
  }else{
  	$response['errorCode']='0005';
  	$response['errorDesc']='参数传递出错';
  }
  ajaxReturn($response);
}

}
