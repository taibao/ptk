<?php
/*
  the om_users interface
  author : vitas
  date : 2019-2-21
*/

class account extends Controller{
    public function __construct(){
      $this->set_lang('zh_cn'); //set language
    }

    #创建账户催一下
    public function urge_audit()
    {
      $TblOmUserModel = loadModel("TblOmUser");
      $TblFanOmUserModel = loadModel("TblFanOmUser");

      if(I('')){
        try{
          $search_map['open_id'] = I('openId');
          $om_user = $TblFanOmUserModel->findByMap("om_user_id",$search_map);

          $data['om_user_id'] = $om_user['om_user_id'];
          $data['create_time'] = time();

          $TblOmUserModel->save($data);
          $response = getresponse('success');
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }







}
