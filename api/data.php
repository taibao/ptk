<?php
/*
  the om_users interface
  author : vitas
  date : 2019-2-21
*/

class data extends Controller{
    public function __construct(){
      #引入硬件类
      include("hardware0.php");
      $this->set_lang('zh_cn'); //set language
    }
    #设备上下线
    public function gw_online()
    {
      if(I("")){
        $post = I("");
        try{
          logOutput("../wef/api/data/".date("Y_m_d")."_onoffline.log",$post);
          if($mac=$post['mac'])
          {
            $response = $this->online($post);
          }else{
            foreach ($post as $key => $value) {
               $response = $this->online($value);
            }
          }
        }catch(Exception $e){
          print_r($e->getMessage());exit;
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    public function online($value){
      if(trim($value['mac'])==""){
        return;
      }
      #网关下挂设备数据表
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      $hardware = new hardware0();
      #下挂设备查询条件
      $map['mac'] = str_replace(":","",$value['mac']);
      if(I('type')=="sta"){
        $onoff_map['mac'] = str_replace(":","",$value['parentMac']);
      }else{
        $onoff_map['mac'] = str_replace(":","",$value['mac']);
      }
      $gateway = $WhiteListModel->findByMap("id,uniacid,areas_id,sn",$onoff_map);
      $msg['status'] = 0;
      $msg['status_text'] = $this->const_arr['error'];
      if(!$gateway['uniacid']){
          $msg['status_text']="该网关未存入白名单";
          ajaxReturn($msg);
      }
      #字段转化
      $data['mac'] = str_replace(":","",$value['mac']);
      $data['parent_mac'] = str_replace(":","",$value['parentMac']);
      if($value['sn']!="")
      {
        $data['sn'] = $value['sn'];
      }
      if($value["online"]=='1'){
        $data['link_status'] = '0';
        $data['last_login_time'] = substr($value['lastOnOfflineTime'],0,-3);
        $data['last_online_time'] = substr($value['lastOnOfflineTime'],0,-3);
      }else if(I("online")=='0'){
        $data['link_status'] = '1';
        $data['last_op_time'] = substr($value['lastOnOfflineTime'],0,-3);
        $data['last_outline_time'] = substr($value['lastOnOfflineTime'],0,-3);
      }
      #写入事件记录
      if(I("type")=="sta"){
        $device_info = $TblDeviceInfoModel->findByMap("parent_mac",$map);
        if($data['link_status']=='1'&&$device_info['parent_mac']!=$onoff_map['mac'])
        {
          return;
        }
        $event['event_code'] = '设备'.$data['mac']."从网关".$data['parent_mac'];
      }else if(I("type")=="gateway"){
        $event['event_code'] = '网关设备:'.$data['mac'];
      }
      $event['parent_mac'] = $data['parent_mac'];
      $event['event_name'] = $data['link_status'];
      $event['event_code'] .= $data['link_status']=='0'?"上线":"下线";
      $event['mac'] = $data['mac'];
      $event['add_time'] = time();
      $TblDeviceOnofflineEventModle = loadModel('TblDeviceOnofflineEvent');
      $TblDeviceOnofflineEventModle->insert($event);
      $data['device_class'] = $value["type"];
      $data['uniacid'] = $gateway['uniacid'];
      if($value["type"]=="sta"){
        if($TblOnofflineGatewayModel->where($onoff_map)->getCount()==0)
        {
          return;
        }
        $msg = $this->gw_sta_insert($map,$data,$gateway);
      }else if($value["type"]=="gateway"){
        $msg = $this->gw_gateway_insert($map,$data,$gateway);
      }
      else if($value["type"]=="edgegateway")
      {
        $msg = $this->gw_edgegateway_insert($map,$data,$gateway);
      }
      $response = $msg;
      return $response;
    }

    public function gw_edgegateway_insert($map,$data,$gateway)
    {
      #网关下挂设备数据表
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      $hardware = new hardware0();

      $data['uniacid'] = $gateway['uniacid'];
      $data['white_id'] = $gateway['id'];
      $data['areas_id'] = $gateway['areas_id'];
      $data['sn'] = $gateway['sn'];
      $data['device_class'] = 'edgegateway';

      if($TblOnofflineGatewayModel->where($map)->getCount()>0){
        $TblOnofflineGatewayModel->where($map)->save($data);
        $WhiteListModel->where($map)->save($data);
        $msg['status'] = 1;
        $msg['status_text'] = '修改成功';
      }else{
        $WhiteListModel->where($map)->save($data);
        $data['create_time'] = time();
        $TblOnofflineGatewayModel->insert($data);
        $msg['status'] = 2;
        $msg['status_text'] = '添加成功';
      }
      return $msg;
    }

    public function gw_sta_insert($map,$data,$gateway)
    {
      #网关下挂设备数据表
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      $hardware = new hardware0();
      if($data['parent_mac']=="")
      {
        return;
      }
      if($WhiteListModel->where($map)->getCount()>0)
      {
        return;
      }
      if($TblDeviceInfoModel->where($map)->getCount()>0){
        $TblDeviceInfoModel->where($map)->save($data);
        $msg['status'] = 1;
        $msg['status_text'] = '修改成功';
      }else{
        $data['create_time'] = time();
        $data['device_class'] = 'sta';
        $data['uniacid'] = $gateway['uniacid'];

        $TblDeviceInfoModel->insert($data);
        $msg['status'] = 2;
        $msg['status_text'] = '添加成功';
      }
      return $msg;
    }

    public function gw_gateway_insert($map,$data,$gateway){
      #网关下挂设备数据表
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      $hardware = new hardware0();

      $data['uniacid'] = $gateway['uniacid'];
      $data['white_id'] = $gateway['id'];
      $data['areas_id'] = $gateway['areas_id'];
      $data['sn'] = $gateway['sn'];

      #统计掉线次数
      $TblDeviceOnofflineEventModel = loadModel('TblDeviceOnofflineEvent');
      $onoff_map['mac'] = $map['mac'];
      $onoff_map['add_time'][] = array(">",strtotime(date("Y-m-d")));
      $onoff_map['add_time'][] = array("<",time());
      $onoff_map['event_name'] = '1'; //下线事件
      $onoff_times = $TblDeviceOnofflineEventModel->where($onoff_map)->getCount();
      $data['dropping_freq'] = $onoff_times;

      if($TblOnofflineGatewayModel->where($map)->getCount()>0){
        $TblOnofflineGatewayModel->where($map)->save($data);
        $WhiteListModel->where($map)->save($data);
        $msg['status'] = 1;
        $msg['status_text'] = '修改成功';
      }else{
        $data['device_class'] = 'gateway';
        $WhiteListModel->where($map)->save($data);
        $data['create_time'] = time();
        $TblOnofflineGatewayModel->insert($data);
        $msg['status'] = 2;
        $msg['status_text'] = '添加成功';
      }
      return $msg;
    }

    #gw_upstream接口
    public function gw_upstream()
    {
      if(I('')){
        logOutput("./api/data/".date("Y_m_d")."_upstream.log",I(""));
        if(trim(I('mac'))==""){
          return;
        }
        try{
          $post = I('');
          if($mac=$post['mac'])
          {
              $response = $this->upstream($post);
          }else{
            foreach ($post as $key => $value) {
              $response = $this->upstream($value);
            }
          }
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    #处理upstream
    public function upstream($post)
    {
      #网关下挂设备数据表
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      $map['mac'] = str_replace(":","",$post['mac']);

      #查找uniacid
      $onoff_map['mac'] = $map['mac'];
      $gateway = $WhiteListModel->findByMap("id,uniacid,areas_id,sn",$onoff_map);
      $msg['status'] = 0;
      $msg['status_text'] = $this->const_arr['error'];
      if(!$gateway['uniacid']){
          $msg['status_text']="该网关未存入白名单";
          ajaxReturn($msg);
      }
      if($TblOnofflineGatewayModel->where($onoff_map)->getCount()==0)
      {
        return;//该网关未上线
      }
      //设备信息
      if(I('type')=='all')
      {
        #字段转化
        $devStatus = $post['values']['devStatus']['data'];
        $ontStatus = $post['values']['ontStatus'];
        if($devStatus){
          foreach ($devStatus as $key => $v){
            $data['mac'] = str_replace(":","",$v["mac"]);
            $data['ip'] = $v['ip'];
            $data['device_name'] = $v['deviceName'];
            $data['dhcp_name'] = $v['dhcpName'];
            $data['online_time'] = $v['onlineTime'];
            if($v['status']=="ONLINE"){
              $data['consult_up'] = $v['upSpeed'];
              $data['consult_down'] = $v['downSpeed'];
            }
            if(stripos($v['connectInterface'],"SSID")!==false)
            {
              $data['link_type'] = "wireless";
            }else {
              $data['link_type'] = "wired";
            }
            $data['connect_interface'] = $v['connectInterface']; //接入类型
            $data['uniacid'] = $gateway['uniacid'];

            $map['mac'] = $data['mac'];
            if($WhiteListModel->where($map)->getCount()>0)
            {
              continue; //判断该设备是否是网关，网关不能存入设备表
            }
            if($device_info = $TblDeviceInfoModel->findByMap("parent_mac",$map)){
              if($device_info['parent_mac']!=$onoff_map['mac']){
                continue;//如果该设备网关不一致，则不修改
              }
              $TblDeviceInfoModel->where($map)->save($data);
              $msg['status'] = 1;
              $msg['status_text'] = '下挂设备修改成功';
            }else{
              $data['parent_mac'] = $onoff_map['mac'];
              $data['create_time'] = time();
              $TblDeviceInfoModel->insert($data);
              $msg['status'] = 2;
              $msg['status_text'] = '下挂设备添加成功';
            }
          }
        }
        #网关信息
         if($ontStatus){
          #添加网关数据
          $data['mac'] =  str_replace(":","",$ontStatus['mac']);
          $data['alias'] = $ontStatus['name'];
          $data['device_type'] = $ontStatus['productClass'];
          $data['manufacturer'] = $ontStatus['vendor'];
          $data['version'] = $ontStatus['firmwareVer'];
          $data['hardware_ver'] = $ontStatus['hardwareVer'];
          $data['online_time'] = $ontStatus['onlineTime'];
          $data['ip'] = $ontStatus['ip'];
          $data['dev_onlinenum'] = $ontStatus['devOnlineNum'];
          $data['dev_allnum'] = $ontStatus['devAllNum'];
          $data['cpu_occupy'] = $ontStatus['cpu'];
          $data['led'] = $ontStatus['led']=='true'?"1":'0';
          $data['PPPOE'] = $ontStatus['pppoe'];
          $data['memory_occupy'] = $ontStatus['ram'];
          $data['transmitPower5G'] = $ontStatus['transmitPower5G'];
          $data['transmitPower24G'] = $ontStatus['transmitPower24G'];
          $data['SupportedRFBand'] = $ontStatus['supportedRFBand'];
          $data['BandCapability'] = $ontStatus['bandCapability'];
          $data['up_velocity'] = $ontStatus['upSpeed'];
          $data['down_velocity'] = $ontStatus['downSpeed'];
          $data['core_addons_version'] = $ontStatus['pluginVersion'];
          $data['wan'] = str_replace('"',"'",json_encode($ontStatus['wan'][0]));
          $data['packet_loss_rate'] = $ontStatus['ping']['frameLossRate'];
          $data['delay_time'] = $ontStatus['ping']['averageDelayMs'];
          $data['uniacid'] = $gateway['uniacid'];
          $data['white_id'] = $gateway['id'];
          $data['areas_id'] = $gateway['areas_id'];
          $data['sn'] = $gateway['sn'];
          $data['uniacid'] = $gateway['uniacid'];

          if($TblOnofflineGatewayModel->findByMap("mac",$onoff_map)){
            $TblOnofflineGatewayModel->where($onoff_map)->save($data);
            $WhiteListModel->where($onoff_map)->save($data);
            $msg['status'] = 1;
            $msg['status_text'] = '修改成功';
          }else{
            $WhiteListModel->where($onoff_map)->save($data);
            $data['create_time'] = time();
            $TblOnofflineGatewayModel->insert($data);
            $msg['status'] = 2;
            $msg['status_text'] = '添加成功';
          }
        }
      }
      else if(I('type')=='BrasData'){
        $values = $post['values'];
        foreach ($values as $key => $value) {
          $value['uniacid'] = $gateway['uniacid'];
          if($value['type']=="onlineFail"){
             $this->onlineFail($value);
           }
           else if($value['type']=="offlineRecord"){
             $this->offlineRecord($value);
           }
           else if($value['type']=="access"){
             $this->access($value);
           }
           else if($value['type']=="oltInfo"){
             $this->oltInfo($value);
           }
        }
      }else if(I('type')=='OltData'){
        $values = $post['values'];
        foreach ($values as $key => $value) {
          $value['uniacid'] = $gateway['uniacid'];
          if($value['type']=="onlineFail"){
             $this->onlineFail($value);
           }
           else if($value['type']=="offlineRecord"){
             $this->offlineRecord($value);
           }
           else if($value['type']=="access"){
             $this->access($value);
           }
           else if($value['type']=="oltInfo"){
             $this->oltInfo($value);
           }
        }
      }
      $msg['status'] = 1;
      $msg['status_text'] = '修改成功';
      $response = $msg;
      return $response;
    }


    public function onlineFail($data)
    {
      $map['hw_bras_mac'] = $data['devMac'];
      $data['hw_bras_mac'] = $data['devMac'];
      $data['hw_user_mac'] = str_replace(":","",$data['hwUserMac']);
      $FailRecordModel = loadModel('TblHwAaaOnlineFailRecord');
      $arr = array();
      foreach ($data as $key => $value) {
        $key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',$key));
        $arr[$key] = $value;
      }
      $FailRecordModel->insert($arr);
    }

    public function offlineRecord($data)
    {
      $map['hw_bras_mac'] = $data['devMac'];
      $data['hw_bras_mac'] = $data['devMac'];
      $data['hw_user_mac'] = str_replace(":","",$data['hwUserMac']);
      $TblHwAaaOfflineRecordModel = loadModel('TblHwAaaOfflineRecord');
      $arr = array();
      foreach ($data as $key => $value) {
        $key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',$key));
        $arr[$key] = $value;
      }
      $TblHwAaaOfflineRecordModel->insert($arr);
    }

    public function access($data)
    {
      $map['hw_user_name'] = $data['hwUserName'];
      $data['hw_user_name'] = $data['hwUserName'];
      $data['hw_user_mac'] = str_replace(":","",$data['hwBrasMac']);
      $data['hw_bras_mac'] = $data['devMac'];
      $TblHwAccessModel = loadModel('TblHwAccess');
      $arr = array();
      foreach ($data as $key => $value) {
        $key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',$key));
        if($key=="onts")
        {
          $arr[$key] = json_encode($value);
        }
        else{
          $arr[$key] = $value;
        }
      }
      if($TblHwAccessModel->Count($map)>0)
      {
        $TblHwAccessModel->where($map)->save($arr);
      }else{
        $TblHwAccessModel->insert($arr);
      }
    }

    public function oltInfo($data)
    {
      $map['hw_olt_mac'] = $data['devMac'];
      $data['hw_olt_mac']=$data['devMac'];
      $oltInfoModel = loadModel('TblHwGponDeviceOltControlInfo');
      $arr = array();
      foreach ($data as $key => $value) {
        $key = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',$key));
        if($key=="onts")
        {
          $arr[$key] = json_encode($value);
        }
        else{
          $arr[$key] = $value;
        }
      }
      if($oltInfoModel->Count($map)>0)
      {
        $oltInfoModel->where($map)->save($arr);
      }else{
        $oltInfoModel->insert($arr);
      }
    }

    #gw_alarm
    public function gw_alarm(){
      if(I('')){
        try{
          #网关下挂设备数据表
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $TblAlarmModel = loadModel("TblAlarm");
          $map['mac'] = str_replace(":","",I("mac"));

          #查找uniacid
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $onoff_map['mac'] = str_replace(":","",I('mac'));
          $gateway = $TblOnofflineGatewayModel->findByMap("uniacid",$onoff_map);

          #字段转化 alias
          $data['mac'] = str_replace(":","",I("mac"));
          $data['code'] = I("code");
          $data['description'] = I('description');
          $data['genTime'] = strtotime(I("genTime"));
          $data['uniacid'] = $gateway['uniacid'];

          $msg['status'] = 0;
          $msg['status_text'] = $this->const_arr['error'];
          //如果存在该网关，添加该网关的告警事件
          if($TblOnofflineGatewayModel->findByMap("mac",$map)){
            $TblAlarmModel->insert($data);
            $msg['status'] = 1;
            $msg['status_text'] = '修改成功';
          }
          $response = $msg;
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

}
