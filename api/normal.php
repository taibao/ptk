<?php
/*
  the normal users interface
  author : vitas
  date : 2019-2-21
*/

class normal extends Controller{
    public function __construct(){
      define('ON_BIND', '1');
      define('OFF_BIND', '0');
      define('ON_LINE', '0');
      define('OFF_LINE', '1');
    }

    /**
     * 绑定宽带账号接口
     * @param String $openId  接收的设备openId值
     * @param String $broadband_account    宽带账号
     * @return String $broadband_password  宽带密码
     */
    public function bindBroadAcc()
    {
      if(I('')){
        $model = new Model();
        try{
          $search_map['openid'] = I('openId');
          $McMappingFansModel = loadModel('McMappingFans');
          $user = $McMappingFansModel->findByMap("uid,acid,uniacid,openid",$search_map);

          $model->begin();
          $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
          if($TblFanWeixinUserModel->where($user)->getCount()<1){

            $TblWeixinUserModel = loadModel('TblWeixinUser');
            $weixin_user['broadband_account'] = I('broadband_account');
            $weixin_user['broadband_password'] = I('broadband_password');
            $weixin_user['create_time'] = date("Y-m-d");
            $weixin_user['bind'] = ON_BIND;
            $user_id = $TblWeixinUserModel->insert($weixin_user);

            $user['user_id'] = $user_id;
            $TblFanWeixinUserModel->insert($user);
            $response = getresponse('success',$this->const_arr['success']);
            $model->commit();
          }else{
            $response = getresponse('error',$this->const_arr['broad_account_alread_bind']);
          }
        }catch(Exception $e){
          $model->rollback();
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    /**
     * 绑定上网设备
     * @param String $openId  接收的设备openId值
     * @param String $mac     设备MAC地址
     * @param String $alias   设备别名
     */
    public function bindOnlineDevice()
    {
      if(I('')){
        $model = new Model();
        try{
          include_once("hardware.php");
          $hardware = new hardware();

          #judge the mac if it is online
          if($device=$hardware->getOnlineSn()){
            $search_map['openid'] = I('openId');
            #获得用户编号
            $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
            $user = $TblFanWeixinUserModel->findByMap("user_id",$search_map);
            #判断用户是否绑定设备
            $TblUserToDeviceModel = loadModel("TblUserToDevice");
            $user_device_map['user_id'] = $user['user_id'];
            $user_device_map['mac'] = I("mac");

            if($TblUserToDeviceModel->where($user_device_map)->getCount()<1){
              $model->begin();
              #添加设备
              $TblDeviceInfoModel = loadModel('TblDeviceInfo');
              $device_info['mac'] = $device['mac'];
              $device_info['last_online_time'] = strtotime($device['lastOnlineTime']);
              $device_info['link_status'] = $device['networkup']?ON_LINE:OFF_LINE;
              $device_info['device_type'] = $device['type'];
              $device_info['alias'] = I('alias');

              $TblDeviceInfoModel->insert($device_info);
              #添加用户设备绑定
              $user_to_device['user_id'] = $user['user_id'];
              $user_to_device['mac'] = I("mac");
              $user_to_device['type'] = $device['type'];
              $user_to_device['bind'] = ON_BIND;
              $user_to_device['create_time'] = time();

              $TblUserToDeviceModel->insert($user_to_device);
              $response = getresponse('success',$this->const_arr['success']);
              $model->commit();
            }else{
                $response = getresponse('error',"该mac设备已绑定");
            }
          }else{
            $response = getresponse('error',"该mac设备不存在");
          }
        }catch(Exception $e){
          $model->rollback();
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    /**
     * 解绑宽带账号接口
     * @param String $openId  接收的设备openId值
     * @param String $broadband_account    宽带账号
     * @return String $broadband_password  宽带密码
     */
    public function unbindBroadAcc()
    {
      if(I('')){
        $model = new Model();
        try{
          $search_map['openid'] = I('openId');
          $McMappingFansModel = loadModel('McMappingFans');
          $user = $McMappingFansModel->findByMap("uid,acid,uniacid,openid",$search_map);

          $model->begin();
          $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
          if($user_id = $TblFanWeixinUserModel->findByMap("user_id",$user)){
            $TblFanWeixinUserModel->where($user)->delete();

            $TblUserToDeviceModel = loadModel("TblUserToDevice");
            $TblUserToDeviceModel->where($user_id)->delete();

            $TblWeixinUserModel = loadModel('TblWeixinUser');
            $weixin_user['user_id'] = $user_id['user_id'];
            $weixin_user['broadband_account'] = I('broadband_account');
            $weixin_user['broadband_password'] = I('broadband_password');
            $TblWeixinUserModel->where($weixin_user)->delete();

            $response = getresponse('success',$this->const_arr['success']);
            $model->commit();
          }else{
            $response = getresponse('error',"");
          }
        }catch(Exception $e){
          $model->rollback();
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }



    /**
     * 获取用户首页信息
     * @param String $openId  接收的设备openId值
     */
    public function getUserInfo(){
      if(I('')){
        try{
          include_once("hardware.php");
          $hardware = new hardware();

          #judge the mac if it is online
          if($device=$hardware->getOnlineSn()){
            $search_map['openid'] = I('openId');

            #获得用户昵称
            $McMappingFansModel = loadModel('McMappingFans');
            $username = $McMappingFansModel->findByMap("nickname",$search_map);

            #获得用户编号
            $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
            $user = $TblFanWeixinUserModel->findByMap("user_id",$search_map);

            #获取用户宽带账号
            $TblWeixinUserModel = loadModel('TblWeixinUser');
            $broadband_account = $TblWeixinUserModel->findByMap("broadband_account",$user);

            #获取用户绑定设备
            $TblUserToDeviceModel = loadModel("TblUserToDevice");
            $user_device_map['user_id'] = $user['user_id'];
            $user_device_map['bind'] = ON_BIND;
            $device_lists = $TblUserToDeviceModel->join('join ims_tbl_device_info on ims_tbl_device_info.mac = ims_tbl_user_to_device.mac ')->info("ims_tbl_device_info.*,alias",$user_device_map);

            #获取设备详情
            // $TblDeviceInfoModel = loadModel('TblDeviceInfo');
            // foreach ($device_lists as $key => $value) {
            // }

            $data['username'] = $username['nickname'];
            $data['broadband_account'] = $broadband_account['broadband_account'];
            $data['device_nums'] = count($device_lists);
            $data['device_lists'] = $device_lists;

            $response = getresponse('success',$data);
          }else{
            $response = getresponse('error',"该mac设备不存在");
          }
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }


    /**
     * 获取设备详情页面
     * @param String $openId  接收的设备openId值
     * @param String $mac     接收的设备mac值
     */
     public function getDeviceInfo(){
       try{
         if(I('')){
           $search_map['open_id'] = I('openId');
           $TblDeviceInfoModel = loadModel('TblDeviceInfo');
           $TblAreasModel = loadModel('TblAreas');

           #取设备信息
           $device_map['mac'] = I('mac');
           $data = $TblDeviceInfoModel->findByMap('*',$device_map);

           if($data){
               $areas_map['areas_id'] = $data['areas_id'];
               $areas = $TblAreasModel->findByMap("description,address,name",$areas_map);
               $data['areas'] = $areas;
               $gateway_map['mac'] = $data['parent_mac'];
               $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
               $gateway = $TblOnofflineGatewayModel->findByMap("install_address,alias",$gateway_map);
               $data['link_gateway'] = $gateway;
               $data['last_online_time'] = date("Y-m-d h:i:s",$data['last_online_time']);
               $data['last_outline_time'] = date("Y-m-d h:i:s",$data['last_outline_time']);
               $data['online_time'] = '235233';
               $data['bind_time'] = date("Y-m-d h:i:s",$data['bind_time']);
           }
           $response = getresponse('success',$data);
         }
       }catch(Exception $e){
           $response = getresponse('error');
       }
       ajaxReturn($response);
     }


     /**
      * 修改设备备注信息
      * @param String $openId  接收的设备openId值
      * @param String $mac     接收的设备mac值
      */
     public function setDeviceRemark(){
       try{
         if(I('')){
           $search_map['open_id'] = I('openId');
           $TblDeviceInfoModel = loadModel('TblDeviceInfo');
           $TblAreasModel = loadModel('TblAreas');

           #取设备信息
           $device_map['mac'] = I('mac');
           $parent_mac = $TblDeviceInfoModel->findByMap('parent_mac',$device_map);

           $data = I('');
           $TblDeviceInfoModel->where($device_map)->save($data);
           $data['mac'] = $parent_mac['parent_mac'];
           unset($data['alias']);

           $map['mac'] = $data['mac'];
           $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
           $gateway = $TblOnofflineGatewayModel->where($map)->save($data);

           $response = getresponse('success',$this->const_arr['success']);
         }
       }catch(Exception $e){
           $response = getresponse('error');
       }
       ajaxReturn($response);
     }


}
