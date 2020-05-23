<?php
/*
  the normal users interface
  author : vitas
  date : 2019-2-21
  #采用restful风格，每个网址代表一种资源（resource）
  https://api.example.com/version/name
*/

class normal extends Controller{
    public function __construct(){
      define('ON_BIND', '1');
      define('OFF_BIND', '0');
      define('ON_LINE', '0');
      define('OFF_LINE', '1');
      islogin();
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
            $weixin_user['create_time'] = date("Y-m-d H:i:s");
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
            $search_map['openid'] = I('openId');
            #获得用户编号
            $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
            $user = $TblFanWeixinUserModel->findByMap("user_id",$search_map);
            #判断用户是否绑定设备
            $TblUserToDeviceModel = loadModel("TblUserToDevice");
            $user_device_map['user_id'] = $user['user_id'];
            $user_device_map['mac'] = str_replace(":","",I("mac"));

            $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
            $TblDeviceInfoModel = loadModel('TblDeviceInfo');
            $device = $TblOnofflineGatewayModel->findByMap("device_type",array('mac'=>str_replace(":","",I("mac"))));
            if($TblUserToDeviceModel->where($user_device_map)->getCount()<1){
              $model->begin();

              #添加用户设备绑定
              $user_to_device['user_id'] = $user['user_id'];
              $user_to_device['mac'] = str_replace(":","",I("mac"));
              $user_to_device['alias_device'] = I('alias');
              if($device)
              {
                $user_to_device['type'] = $device['device_type'];
              }
              $user_to_device['bind'] = ON_BIND;
              $user_to_device['bind_create_time'] = time();
              $TblUserToDeviceModel->insert($user_to_device);

              #修改用户设备表信息
              $device_info_map['mac'] = str_replace(":","",I("mac"));
              $device_info['bind_time'] = time();
              $TblDeviceInfoModel->where($device_info_map)->save($device_info);

              $response = getresponse('success',$this->const_arr['success']);
              $model->commit();
            }else{
                $response = getresponse('error',"该mac设备已绑定");
            }
        }catch(Exception $e){
          $model->rollback();
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    /**
     * 查询宽带账号
     * @param String $openId  接收的设备openId值
     */
     public function bindedAcc()
     {
       if(I('')){
         try{
             $search_map['openid'] = I('openId');
             #获得用户编号
             $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
             $user = $TblFanWeixinUserModel->findByMap("user_id",$search_map);
             if(!$user)
             {
               $response = getresponse('error',"你没有绑定宽带账号");
             }else{
               $TblWeixinUserModel = loadModel('TblWeixinUser');
               $map['user_id'] = $user['user_id'];
               $weixin_user = $TblWeixinUserModel->findByMap("broadband_account,broadband_password",$map);
               $response = getresponse("success",$weixin_user);
             }
         }catch(Exception $e){
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
     * 解绑下挂设备接口
     * @param String $openId  接收的设备openId值
     * @param String $mac     设备mac
     */
    public function unbindDevice()
    {
      if(I('')){
        $model = new Model();
        try{
          $search_map['openid'] = I('openId');
          $model->begin();
          $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
          if($user_id = $TblFanWeixinUserModel->findByMap("user_id",$user)){
            $map['mac'] = I('mac');
            $TblUserToDeviceModel = loadModel("TblUserToDevice");
            $TblUserToDeviceModel->where($map)->delete();
            $response = getresponse('success',$this->const_arr['success']);
            $model->commit();
          }else{
            $response = getresponse('error',"您无权解绑设备");
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
            $field = 'ims_tbl_user_to_device.*,alias,device_name,device_type,parent_mac,interface_type,dhcp_name,link_type,connect_interface,interface_port,last_outline_time,consult_up,consult_down,link_status';
            $device_lists = $TblUserToDeviceModel->join(' left join ims_tbl_device_info on ims_tbl_device_info.mac = ims_tbl_user_to_device.mac ')->info($field,$user_device_map);
            $data['username'] = $username['nickname'];
            $data['broadband_account'] = $broadband_account['broadband_account'];
            $data['device_nums'] = count($device_lists);
            $data['device_lists'] = $device_lists;
            $response = getresponse('success',$data);

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

           $TblUserToDeviceModel = loadModel("TblUserToDevice");
           $device_map['mac'] = str_replace(":","",I("mac"));
           #取设备信息
           $data = $TblDeviceInfoModel->findByMap('*',$device_map);
           $userDevice = $TblUserToDeviceModel->findByMap("alias_device",$device_map);
           $data['alias_device'] = $userDevice['alias_device'];
           if($data){
               $areas_map['areas_id'] = $data['areas_id'];
               $areas = $TblAreasModel->findByMap("description,address,name",$areas_map);

               $data['areas'] = $areas;
               $gateway_map['mac'] = $data['parent_mac'];
               $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
               $gateway = $TblOnofflineGatewayModel->findByMap("install_address,alias",$gateway_map);
               $data['link_gateway'] = $gateway;
               $data['last_online_time'] = date("Y-m-d H:i:s",$data['last_online_time']);
               $data['last_outline_time'] = date("Y-m-d H:i:s",$data['last_outline_time']);
               $data['bind_time'] = date("Y-m-d H:i:s",$data['bind_time']);
           }
           $response = getresponse('success',$data);
         }
       }catch(Exception $e){
           $response = getresponse('error');
       }
       ajaxReturn($response);
     }

     /**
      * 获取
      * @param String $openId  接收的设备openId值
      * @param String $alias_device  接收的设备alias_device值
      */

      public function updateAliasName(){
        try{
          if(I('')){
            $search_map['openid'] = I('openId');
            $TblFanWeixinUserModel = loadModel('TblFanWeixinUser');
            if($user_id = $TblFanWeixinUserModel->findByMap("user_id",$search_map)){
              $map['mac'] = I('mac');
              $map['user_id'] = $user_id['user_id'];
              $TblUserToDeviceModel = loadModel("TblUserToDevice");
              $data = array();
              $data['alias_device'] = I('alias_device');
              $TblUserToDeviceModel->where($map)->save($data);
              $response = getresponse('success');
            }else{
              $response = getresponse('error',"您无权限修改该设备");
            }
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
           $device_map['mac'] = str_replace(":","",I("mac"));
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
