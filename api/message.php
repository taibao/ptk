<?php
class message extends Controller{
    /**
     * 用户消息接口
     */
    public function getMessageList()
    {
      if(I('')){
        try{
          $search_map['open_id'] = I('openId');
          $TblFanOmUserModel = loadModel('TblFanOmUser');
          $TblOmUserModel = loadModel('TblOmUser');
          $TblUserToMessageModel = loadModel('TblUserToMessage');
          $TblMessageModel = loadModel('TblMessage');
          $user = $TblFanOmUserModel->findByMap("om_user_id,uniacid,uid",$search_map);
          $systemUniacid['uniacid'] = $user['uniacid'];
          $systemAllUser = $TblFanOmUserModel->where($systemUniacid)->getList();
          if ($systemAllUser) {
              $userList =array();
              foreach($systemAllUser as $key=>$value) {
                    $userList[] = $value['om_user_id'];
              }
              $system_map['is_system'] ='system';
              $system_map['om_user_id'] = array('in', $userList);
              $systemUser = $TblOmUserModel->where($system_map)->find();
              $all_om_user = array();
              if ($user) {
                  $all_om_user[] = $user['om_user_id'];
              }
              if ($systemUser) {
                  $all_om_user[] = $systemUser['om_user_id'];
              }
              if ($all_om_user){
                  $om_user_map['om_user_id'] = array('in',$all_om_user);
                  $get_all_message_by_om_user = $TblUserToMessageModel->where($om_user_map)->getList();
                  $data=array();
                  if ($get_all_message_by_om_user) {
                      $message_list = array();
                      foreach($get_all_message_by_om_user as $key =>$value) {
                          $message_list[] = $value['message_id'];
                      }
                      $get_message_list_map['message_id'] = array('in',$message_list);
                      $get_message_list_map['create_time'][] = array('<',date('Y-m-d',time()+60*60*24));
                      $get_message_list_map['create_time'][] = array('>' ,date('Y-m-d',time()-60*60*24*7));
                      $get_message_list = $TblMessageModel->where($get_message_list_map)->order('message_id desc')->getList();
                      $data['list'] =$get_message_list;
                      $data['nums'] = count($get_message_list);
                  }
                  $response = getresponse('success',$data);
              }
          }else{
            $response = getresponse('error',"用户不存在");
          }
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }
  }
