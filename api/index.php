<?php
/*
  the om_users interface
  author : vitas
  date : 2019-2-21
*/

class index extends Controller{
  public function __construct(){
    islogin();
    $this->set_lang('zh_cn'); //set language
  }
    /**
    *功能:获取关注网关列表
    *param: openId (String)
    */
    public function gatewayFollowList(){
      if(I('')){
        try{
          $search_map['open_id'] = I('openId');
          $TblFanOmUserModel = loadModel('TblFanOmUser');
          $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $TblAreasModel = loadModel('TblAreas');

          $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);

          $om_user_care_map['om_user_id'] = $user['om_user_id'];
          $om_user_care_map['care'] = '1';
          $user_care_lists = $TblOmUserCareToGatewayModel->info("mac,name,remark_address",$om_user_care_map);
          $user_to_macs = getcloumns($user_care_lists,"mac");

          $user_to_macs_map['mac'] = array("in",$user_to_macs);
          $user_care_gateway_lists = $TblOnofflineGatewayModel->info("alias,mac,areas_id,install_address,up_velocity,down_velocity,link_status",$user_to_macs_map);

          $off_num =0;
          $block_num =0;

          foreach ($user_care_gateway_lists as $key => $value) {
            //查询区域地址
            // $map['areas_id'] = $value['areas_id'];
            // $areas_id = $this->getchildnum($value['areas_id']);
            // $areas_id = array_reverse(getcloumns($areas_id,"areas_id"));
            // $install_address = '';
            // foreach ($areas_id as $k => $v) {
            //   $area_map['areas_id'] = str_replace('"','',$v);
            //   $area = $TblAreasModel->findByMap("name",$area_map);
            //   $install_address .= $area['name'].' ';
            // }
            // $user_care_gateway_lists[$key]['real_address'] = $install_address;

            foreach ($user_care_lists as $k => $v) {
              if($v['mac']==$value['mac']){
                $user_care_gateway_lists[$key]['name'] = $v['name'];
                $user_care_gateway_lists[$key]['remark_address'] = $v['remark_address'];
              }
            }

            if($value['link_status']=='1'){
              $off_num++;
            }else if($value['link_status']=='2'){
              $block_num++;
            }
          }

          $lists_num['total_num'] = count($user_care_gateway_lists);
          $lists_num['off_num'] = $off_num;
          $lists_num['block_num'] = $block_num;
          $result['lists_num'] = $lists_num;
          $result['lists'] = $user_care_gateway_lists;

          $response = getresponse('success',$result);
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    #找出该编号以上的所有推荐
    public function getchildnum($parent_id,$pre_result=array(),$level=0){
        $TblAreasModel = loadModel('TblAreas');
        if($parent_id){
            $map['areas_id']=$parent_id;
            $feild = "areas_id,parent_id";
            $cu_data = $TblAreasModel->findByMap($feild,$map);
            $pre_result[]=$cu_data;
            $parent_id=$cu_data['parent_id'];
            $pre_result=$this->getchildnum($parent_id,$pre_result,$level+1);
        }
        return $pre_result;
    }

    /**
    *功能:我的详情接口
    *param: openId (String)
    */
    public function userInfoApi()
    {
      if(I('')){
        try{
          $search_map['open_id'] = I('openId');

          $TblFanOmUserModel = loadModel('TblFanOmUser');
          $TblOmUserModel = loadModel('TblOmUser');
          $TblAreasModel = loadModel('TblAreas');
          $McMembersModel = loadModel('McMembers');
          $user = $TblFanOmUserModel->findByMap("om_user_id,uniacid,uid",$search_map);

          $members_map['uid'] = $user['uid'];
          $member = $McMembersModel->findByMap("nickname,avatar,gender",$members_map);

          $om_user_map['om_user_id'] = $user['om_user_id'];
          $om_user = $TblOmUserModel->findByMap('name,phone',$om_user_map);

          $data['name'] = $om_user['name'];
          $data['phone'] = $om_user['phone'];
          $data['avatar'] = $member['avatar'];
          $data['gender'] = $member['gender'];
          $data['score'] = '4.0';
          $data['account'] = $member['nickname'];
          $data['position'] = '装维工程师';

          $AccountWechatsModel = loadModel("AccountWechats");
          $acid_map['uniacid'] = $user['uniacid'];

          $acid = $AccountWechatsModel->findByMap("name",$acid_map);

          $TblAreasToOmUserModel = loadModel('TblAreasToOmUser');
          $araes_to_users =  $TblAreasToOmUserModel->info("areas_id",$om_user_map);

          $areas_id = getcloumns($araes_to_users,"areas_id");
          $areas_map['areas_id'] = array('in',$areas_id);
          $areas = $TblAreasModel->info("name",$areas_map);
          $areas = getcloumns($areas,"name");
          $data['manage_areas'] = $acid['name'];
          $data['concrete_areas'] = $areas;

          $response = getresponse('success',$data);
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    /**
    *功能:用户退出接口
    *param: openId (String)
    */
    public function useLoginOutApi(){
      try{
        if(I('')){
          $search_map['open_id'] = I('openId');
          $TblFanOmUserModel = loadModel('TblFanOmUser');
          $TblOmUserModel = loadModel('TblOmUser');

          $user = $TblFanOmUserModel->findByMap("om_user_id,uniacid,uid",$search_map);
          $user_map['om_user_id'] = $user['om_user_id'];
          $om_user = $TblOmUserModel->findByMap("om_user_id,verify",$user_map);
          $om_user['verify'] = '3'; //设置删除信息状态

          if($TblOmUserModel->save($om_user))
          {
            $response = getresponse('success',$this->const_arr['login_out']);
          }
        }
    }catch(Exception $e){
      $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  /**
  *功能:网关详情
  *param: openId (String)
  *param: mac (String)
  */
  public function device_info(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $AreasModel =  loadModel('TblAreas');
        $TblFanOmUserModel = loadModel('TblFanOmUser');
        $TblDeviceInfoModel = loadModel('TblDeviceInfo');
        $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
        $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');

        #取用户信息
        $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);
        $gateway_remark_map['om_user_id'] = $user['om_user_id'];
        $gateway_remark_map['mac'] = I('mac');

        #取用户网关备注信息
        $gateway_remark = $TblOmUserCareToGatewayModel->findByMap("*",$gateway_remark_map);

        #取网关详情
        $gateway_info_map['mac'] = I('mac');
        $gateway_info = $TblOnofflineGatewayModel->findByMap("*",$gateway_info_map);
        if($gateway_info){
          $gateway_info['link_device_num'] = $gateway_info['dev_onlinenum'];
          $gateway_info['link_device_total_num'] = $gateway_info['dev_allnum'];
          $gateway_info['last_off_time'] = date("Y-m-d H:i:s",$gateway_info['last_off_time']);//离线时间
          $gateway_info['last_login_time'] = date("Y-m-d H:i:s",$gateway_info['last_login_time']);//上线时间
        }
        $area_id = $AreasModel->findByMap("*",array('areas_id'=>$gateway_info['areas_id']));
        $pre_result = $AreasModel->getchildnum($area_id['parent_id'],array(),$level=0);
        foreach ($pre_result as $k => $v) {
            $area_id['name'] = $v['name'].'--'.$area_id['name'];
        }
        $gateway_info['area'] = $area_id['name'];
        #取设备信息
        // $device_map['parent_mac'] = I('mac');
        // $total_num = $TblDeviceInfoModel->where($device_map)->getCount();
        // $device_map['link_status'] = '0';
        // $num = $TblDeviceInfoModel->where($device_map)->getCount();
        $data['gateway_remark'] = $gateway_remark;
        $data['gateway_info'] = $gateway_info;
        $response = getresponse('success',$data);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  /**
  *功能:获取下挂设备列表
  *param: openId (String)
  *param: mac (String)
  */
  public function getLinkDeviceList(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblDeviceInfoModel = loadModel('TblDeviceInfo');
        $TblAreasModel = loadModel('TblAreas');

        #取设备信息
        $device_map['parent_mac'] = I('mac');
        $total_num = $TblDeviceInfoModel->where($device_map)->getCount();
        $lists = $TblDeviceInfoModel->where($device_map)->getList();
        $device_map['link_status'] = '0';
        $num = $TblDeviceInfoModel->where($device_map)->getCount();
        $gateway_info['link_device_num'] = $num;
        $gateway_info['link_device_total_num'] = $total_num;
        if($lists){
          foreach ($lists as $key => $value) {
            $areas_map['areas_id'] = $value['areas_id'];
            $areas = $TblAreasModel->findByMap("description,address,name",$areas_map);
            $lists[$key]['areas'] = $areas;
            $gateway_map['mac'] = $value['parent_mac'];
            $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
            $gateway = $TblOnofflineGatewayModel->findByMap("install_address,alias",$gateway_map);
            $lists[$key]['link_gateway'] = $gateway;
          }
        }
        $result['list'] = $lists;
        $result['link_device_num'] = $num;
        $result['link_device_total_num'] = count($lists);
        $response = getresponse('success',$result);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  /**
  *功能:下挂设备详情
  *param: openId (String)
  *param: mac (String)
  */
  public function getLinkDeviceInfo(){
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
            $data['last_online_time'] = date("Y-m-d H:i:s",$data['last_online_time']);
            $data['last_outline_time'] = date("Y-m-d H:i:s",$data['last_outline_time']);
            $data['bind_time'] = date("Y-m-d H:i:s",$data['bind_time']);
        }

        $response['info'] = $data;
        $response = getresponse('success',$response);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #设备查询接口
  public function search_device(){
    try{
      if(I('search')){
        $search_map['openid'] = $_POST['openId'];
        $McMappingFansModel = loadModel("McMappingFans");
        $TblAreasToOmUserModel = loadModel("TblAreasToOmUser");
        $TblFanOmUserModel = loadmodel("TblFanOmUser");

        $om_user = $TblFanOmUserModel->findByMap("om_user_id",array('open_id'=>$_POST['openId']));
        $om_areas_id = $TblAreasToOmUserModel->info("*",$om_user);
        $om_areas_id = getcloumns($om_areas_id,"areas_id");
        if(!$om_areas_id)
        {
          $om_areas_id=array('-1');
        }
        $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid",$search_map);
        $uniacid = $getUnicaidInfo['uniacid'];

        $search = I('search');
        $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
        $TblAreasModel = loadModel('TblAreas');
        $user_care_gateway_lists = $TblOnofflineGatewayModel->join('left join ims_tbl_areas on ims_tbl_areas.areas_id = ims_tbl_onoffline_gateway.areas_id where uniacid = '.$uniacid.' and (sn like "%'.$search.'%" '.'or mac like "%'.$search.'%" '.'or alias like "%'.$search.'%" or ims_tbl_areas.name like "%'.$search.'%" ) and mac <> "" and ims_tbl_areas.areas_id in ('.join(',',$om_areas_id).')  ')->limit(0,10)->info("alias,ims_tbl_areas.areas_id,mac,up_velocity,down_velocity,install_address,link_status");
        // echo $TblOnofflineGatewayModel->getSql();exit;
        $response = getresponse('success',$user_care_gateway_lists);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #修改网关备注信息
  public function setDeviceRemark(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblFanOmUserModel = loadModel('TblFanOmUser');
        $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');
        $WhiteListModel =  loadModel('TblGatewayWhiteList');

        #取用户信息
        $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);
        $gateway_remark_map['om_user_id'] = $user['om_user_id'];
        $gateway_remark_map['mac'] = I('mac');

        $data = I('');
        $TblOmUserCareToGatewayModel->where($gateway_remark_map)->save($data);
        if(I('install_address')){
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $map['mac'] = I('mac');
          $gateway['mac'] = I('mac');
          $gateway['install_address'] = I('install_address');
          $TblOnofflineGatewayModel->where($map)->save($gateway);
          $WhiteListModel->where($map)->save($gateway);
        }
        $response = getresponse('success',$this->const_arr['success']);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #修改下挂设备备注信息
  public function setLinkDeviceRemark(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblDeviceInfoModel = loadModel('TblDeviceInfo');
        $TblAreasModel = loadModel('TblAreas');

        #取设备信息
        $map['mac'] = I("mac");
        $device_map['mac'] = I('mac');
        $parentMac = $TblDeviceInfoModel->findByMap('parent_mac',$device_map);

        $data = I('');
        $TblDeviceInfoModel->where($map)->save($data);
        $data['mac'] = $parentMac['parent_mac'];
        unset($data['alias']);

        $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
        $gateway = $TblOnofflineGatewayModel->where($map)->save($data);

        $response = getresponse('success',$this->const_arr['success']);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #关注网关接口
  public function useronCareDevice(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblFanOmUserModel = loadModel('TblFanOmUser');
        $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');

        #取用户信息
        $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);
        $gateway_remark_map['om_user_id'] = $user['om_user_id'];
        $gateway_remark_map['mac'] = I('mac');

        $data['mac'] = I('mac');
        $data['om_user_id'] = $user['om_user_id'];
        $data['create_time'] = date('Y-m-d');
        $data['care'] = 1;

        $gateway_remark=$TblOmUserCareToGatewayModel->findByMap("*",$gateway_remark_map);
        #取用户网关备注信息
        if(!$gateway_remark){
          $TblOmUserCareToGatewayModel->insert($data);
          $response = getresponse('success',$this->const_arr['success']);
        }else{
          $TblOmUserCareToGatewayModel->where($gateway_remark_map)->save($data);
          $response = getresponse('success',$this->const_arr['success']);
        }
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #取消关注网关接口
  public function useroffCareDevice(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblFanOmUserModel = loadModel('TblFanOmUser');
        $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');

        #取用户信息
        $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);
        $gateway_remark_map['om_user_id'] = $user['om_user_id'];
        $gateway_remark_map['mac'] = I('mac');
        $gateway_remark_map['care'] = '1';

        #取消用户网关备注信息
        $TblOmUserCareToGatewayModel->where($gateway_remark_map)->delete();
        $response = getresponse('success',$this->const_arr['success']);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #删除下挂设备
  public function deleteDevice(){
    try{
      if(I('')){
        $search_map['open_id'] = I('openId');
        $TblFanOmUserModel = loadModel('TblFanOmUser');
        $TblOmUserCareToGatewayModel = loadModel('TblOmUserCareToGateway');
        $TblDeviceInfoModel = loadModel('TblDeviceInfo');

        $user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);
        $device_map['mac'] = I('mac');
        $device_info = $TblDeviceInfoModel->findByMap("parent_mac",$device_map);

        $gateway_care_map['om_user_id'] = $user['om_user_id'];
        $gateway_care_map['mac'] = $device_info['parent_mac'];

        if($TblOmUserCareToGatewayModel->where($gateway_care_map)->getCount()>0){
          $TblDeviceInfoModel->where($device_map)->delete();
        }

        $response = getresponse('success',$this->const_arr['success']);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }


  #重启网关
  public function gateway_restart(){
    try{
      if(I('')){
        include_once("hardware.php");
        $hardware = new hardware();
        $content['mac'] = I('mac');
        $content['action']['name'] = "restart"; //getOntInfo

        $response = $hardware->gwSetting($content);
        if($response['errorDesc']=="Successed"){
          #写入事件记录
          $TblEventModel = loadModel("TblEvent");
          $event['event_name'] = '1'; //重启事件
          $event['event_code'] = '网关设备:'.I('mac').'重启';
          $event['mac'] = I('mac');
          $event['add_time'] = time();
          $TblEventModel->insert($event);
        }
        $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
        $map['mac'] = I('mac');
        $gateway['mac'] = I('mac');
        $gateway['maintenance_time'] = time();
        $gateway = $TblOnofflineGatewayModel->where($map)->save($gateway);
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #面板灯
  public function config_ont(){
    try{
      if(I('')){
        include_once("hardware.php");
        $hardware = new hardware();
        $content['mac'] = I('mac');
        $content['action']['name'] = "configOnt"; //getOntInfo
        $values = I("values");
        $content['action']['values'] = $values;
        $response = $hardware->gwSetting($content);
        if($response['errorDesc']=="Successed"){
          #写入事件记录
          $TblEventModel = loadModel("TblEvent");
          $event['event_name'] = '5'; //维护事件
          if(!$values['led']){
            $event['event_code'] = '网关设备:'.I('mac').'关闭面板灯';
          }else{
            $event['event_code'] = '网关设备:'.I('mac').'开启面板灯';
          }
          $event['mac'] = I('mac');
          $event['add_time'] = time();
          $TblEventModel->insert($event);

          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $map['mac'] = I('mac');
          $gateway['mac'] = I('mac');
          $gateway['maintenance_time'] = time();
          $gateway['led'] = !$values['led']?'0':'1';
          $gateway = $TblOnofflineGatewayModel->where($map)->save($gateway);
        }

      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }


  #测试返回数据
  public function test(){
    include("hardware.php");
    $hardware = new hardware();
    #获取线上下挂设备详情
    unset($send_data);
    $send_data['mac'] = '446A2ED1B12F';
    $send_data['action']['name'] = 'getOntInfo';
    $result =  $hardware->gwSetting($send_data);
    $values = $result['settingReturn'][0];
    print_r($values);
    exit;
  }

  #查看消息
  public function check_message(){
    try{
      if(I('')){
        $TblMessageModel = loadmodel("TblMessage");
        $mess_data['message_id'] = I("message_id");
        $mess_data['is_read'] = "1";
        $TblMessageModel->save($mess_data);
        $response = getresponse('success');
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  #注销
  public function logout(){
    $model = new Model();
    $model->begin();
    try{
      if(I('')){
        $TblFanOmUserModel = loadmodel("TblFanOmUser");
        $TblOmUserCareToGatewayModel = loadmodel("TblOmUserCareToGateway");
        $TblOmUserModel = loadModel("TblOmUser");
        $TblAreasToOmUserModel = loadModel("TblAreasToOmUser");
        $TblMessageModel = loadModel("TblMessage");
        $TblUserToMessageModel = loadModel("TblUserToMessage");

        $map['open_id'] = I("openId");
        $user = $TblFanOmUserModel->findByMap("om_user_id",$map);
        if(!$user){
          $response = getresponse('error',"user not exist");
          ajaxReturn($response);
        }
        $messages = $TblUserToMessageModel->info("message_id",$user);
        $messages = getcloumns($messages,"message_id");
        $mess_map['message_id'] = array("in",$messages);
        $TblMessageModel->where($mess_map)->delete();
        $TblUserToMessageModel->where($user)->delete();
        $TblAreasToOmUserModel->where($user)->delete();
        if($TblFanOmUserModel->where($map)->delete()){
            $TblOmUserCareToGatewayModel->where($user)->delete();
            $TblOmUserModel->where($user)->delete();
            $model->commit();
            $response = getresponse('success');
          }else{
            $model->rollBack();
            $response = getresponse('error');
          }
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

}
