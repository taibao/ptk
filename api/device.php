<?php
//设备详情
class device extends Controller{
    public function __construct(){
      islogin();
      $this->set_lang('zh_cn'); //set language
    }
    #定义接口
    public function userInfoApi()
    {
      if(I('')){
        try{
          $this->set_lang('zh_cn'); //set language
          $search_map['open_id'] = I('openId');
          $TblFanOmUserModel = loadModel('TblFanOmUser');
          $TblAreasModel = loadModel('TblAreas');
          $McMembersModel = loadModel('McMembers');
          $TblOmUserModel = loadModel('TblOmUser');
          $user = $TblFanOmUserModel->findByMap("om_user_id,uniacid,uid",$search_map);


          $members_map['uid'] = $user['uid'];
          $member = $McMembersModel->findByMap("nickname,avatar",$members_map)?:die($this->const_arr['error']);

          $om_user_map['om_user_id'] = $user['om_user_id'];
          $om_user = $TblOmUserModel->findByMap('name,phone',$om_user_map)?:die($this->const_arr['error']);

          $data['name'] = $om_user['name'];
          $data['phone'] = $om_user['phone'];
          $data['avatar'] = $member['avatar'];
          $data['score'] = '4.0';
          $data['account'] = $member['nickname'];
          $data['position'] = '装维工程师';

          $AccountWechatsModel = loadModel("AccountWechats");
          $acid_map['uniacid'] = $user['uniacid'];
          $acid = $AccountWechatsModel->findByMap("name",$acid_map)?:die($this->const_arr['error']);

          $TblAreasToOmUserModel = loadModel('TblAreasToOmUser');
          $araes_to_users =  $TblAreasToOmUserModel->info("areas_id",$om_user_map)?:die($this->const_arr['error']);
          $areas_id = getcloumns($araes_to_users,"areas_id");

          $areas_map['areas_id'] = array('in',$areas_id);
          $areas = $TblAreasModel->info("name",$areas_map)?:die($this->const_arr['error']);
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

    public function deviceList() {

    }
  }
