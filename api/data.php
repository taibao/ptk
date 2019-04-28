<?php
/*
  the om_users interface
  author : vitas
  date : 2019-2-21
*/

class data extends Controller{
    public function __construct(){
      $this->set_lang('zh_cn'); //set language
    }

    #导入下挂设备数据
    public function reportDevices(){
      include("hardware.php");
      $hardware = new hardware();
      $send_data['mac'] = '446A2ED1B12F';
      $send_data['action']['name'] = 'getConnectDevList';
      $send_data['action']['values']['mac']='';
      $result =  $hardware->gwSetting($send_data);
      $result = $result['settingReturn'][0]['data'][0]['DevList'];

      #查找uniacid
      $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
      $onoff_map['mac'] = $send_data['mac'];
      $gateway = $TblOnofflineGatewayModel->findByMap("uniacid",$onoff_map);

      $TblDeviceInfoModel = loadModel('TblDeviceInfo');
      foreach ($result as $key => $value) {
        unset($map);unset($data);
        if($value['Status']=="OFFLINE"){
          $data['link_status'] = "1";
        }else{
          $data['link_status'] = '0';
        }
        $data['last_online_time'] = time() - $value['OfflineTime'] - $value['OnlineTime'];
        $data['last_outline_time'] = time() - $value['OfflineTime'];
        $data['up_total_flow'] = $value['UpTotalFlow'];
        $data['down_total_flow'] = $value['DownTotalFlow'];
        $data['ip'] = $value['IP'];
        $data['block_time'] = time() - $value['BlockTime'];
        $data['dhcp_name'] = $value['DhcpName'];
        $data['online_time'] = $value['OnlineTime'];
        $data['mac'] = str_replace(":","",$value['MAC']);
        $data['storage_access_status'] = $value['StorageAccessStatus'];
        $data['connect_interface'] = $value['ConnectInterface'];
        $data['consult_down'] = $value['DownSpeed'];
        $data['us_stats'] = $value['UsStats'];
        $data['consult_up'] = $value['UpSpeed'];
        $data['ds_stats'] = $value['DsStats'];
        $data['device_name'] = $value['DeviceName'];
        $data['parent_mac'] = $send_data['mac'];
        $data['uniacid'] = $gateway['uniacid'];
        $data['create_time'] = time();

        $map['mac'] = $data['mac'];
        if($TblDeviceInfoModel->getCount($map)>0){
          $TblDeviceInfoModel->save($data);
        }else{
          $TblDeviceInfoModel->insert($data);
        }

      }
    }

    #设备上下线
    public function gw_online()
    {
      if(I("")){
        try{
          #存储上下线文件
          $str_content = json_encode(I(''));
          $file = fopen("./api/data/".date("Y_m_d")."_onoffline.txt", "a");
          fwrite($file,"时间：".date("Y-m-d H:i:s")." 数据：".$str_content." \r\n");
          fclose($file);

          #引入硬件类
          include("hardware.php");
          $hardware = new hardware();
          if(I('mac')==""){
            return;
          }
          #网关下挂设备数据表
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $WhiteListModel =  loadModel('TblGatewayWhiteList');
          $TblDeviceInfoModel = loadModel('TblDeviceInfo');
          $TblDeviceOnofflineEventModle = loadModel('TblDeviceOnofflineEvent');

          $map['mac'] = str_replace(":","",I("mac"));

          #查找uniacid
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          if(I('type')=="sta"){
            $onoff_map['mac'] = str_replace(":","",I('parentMac'));
          }else{
            $onoff_map['mac'] = str_replace(":","",I('mac'));
          }

          $gateway = $TblOnofflineGatewayModel->findByMap("uniacid",$onoff_map);
          $msg['status'] = 0;
          $msg['status_text'] = $this->const_arr['error'];
          if(!$gateway['uniacid']){
              $msg['status_text']="该网关未存入白名单";
              ajaxReturn($msg);
          }
          #字段转化
          $data['alias'] = I('alias');
          $data['mac'] = str_replace(":","",I("mac"));
          $data['parent_mac'] = str_replace(":","",I('parentMac'));
          $data['sn'] = I('sn');
          if(I("online")=='1'){
            $data['link_status'] = '0';
            $data['last_login_time'] = substr(I('lastOnOfflineTime'),0,-3);
          }else if(I("online")=='0'){
            $data['link_status'] = '1';
            $data['last_op_time'] = substr(I('lastOnOfflineTime'),0,-3);
          }
          $data['device_class'] = I("type");
          $data['uniacid'] = $gateway['uniacid'];

          if(I("type")=="sta"){
            if($TblDeviceInfoModel->where($map)->getCount()>0){
              $TblDeviceInfoModel->where($map)->save($data);
              $msg['status'] = 1;
              $msg['status_text'] = '修改成功';
            }else{
              $data['create_time'] = time();

              #获取网关详情
              unset($send_data);
              $send_data['mac'] = $data['parent_mac'];
              $send_data['action']['name'] = 'getConnectDevDetail';
              $send_data['action']['values']['mac']=$data['mac'];
              $result =  $hardware->gwSetting($send_data);
              $v = $result['settingReturn'][0];

              $data['block_time'] = time() - $v['blockTime'];
              $data['device_name'] = $v['deviceName'];
              $data['dhcp_name'] = $v['dhcpName'];
              $data['consult_up'] = $v['upSpeed'];
              $data['consult_down'] = $v['downSpeed'];
              $data['up_total_flow'] = $v['upTotalFlow'];
              $data['down_total_flow'] = $v['downTotalFlow'];
              $data['us_stats'] = $v['usStats'];
              $data['ds_stats'] = $v['dsStats'];
              $data['ip'] = $v['ip'];
              $data['online_time'] = $v['onlineTime'];
              $data['last_online_time'] = time() - $v['offlineTime'] - $v['onlineTime'];
              $data['last_outline_time'] = time() - $v['offlineTime'];
              $data['connect_interface'] = $v['connectInterface'];
              if(stripos($v['connectInterface'],"SSID")!==false)
              {
                $data['link_type'] = "wireless";
              }else {
                $data['link_type'] = "wired";
              }
              $data['down_speed_limit'] = $v['downSpeedLimit'];
              $data['up_speed_limit'] = $v['upSpeedLimit'];

              if(array_key_exists("productCLass",$v))
                $data['device_type'] = $v['productCLass'];
              $data['device_class'] = 'sta';
              $data['uniacid'] = $gateway['uniacid'];

              $TblDeviceInfoModel->insert($data);
              $msg['status'] = 2;
              $msg['status_text'] = '添加成功';
            }

            $event['event_name'] = $data['link_status'];
            $event['event_code'] = '设备'.$data['mac']."从网关".$data['parent_mac'];

          }else if(I("type")=="gateway"){
            if($TblOnofflineGatewayModel->where($map)->getCount()>0){
              $TblOnofflineGatewayModel->where($map)->save($data);
              $WhiteListModel->where($map)->save($data);

              $msg['status'] = 1;
              $msg['status_text'] = '修改成功';
            }else{
              $data['create_time'] = time();

              #获取线上下挂设备详情
              unset($send_data);
              $send_data['mac'] = $data['mac'];
              $send_data['action']['name'] = 'getOntInfo';
              $result =  $hardware->gwSetting($send_data);
              $values = $result['settingReturn'][0];
              #添加网关数据
              $data['alias'] = $values['name'];
              $data['up_velocity'] = $values['upSpeed'];
              $data['down_velocity'] = $values['downSpeed'];
              $data['online_time'] = $values['onlineTime'];
              $data['last_login_time'] = time() - $values['onlineTime'];
              $data['cpu_occupy'] = $values['cpu'];
              $data['memory_occupy'] = $values['ram'];
              $data['down_total_flow'] = $values['downTotalFlow'];
              $data['up_total_flow'] = $values['upTotalFlow'];
              $data['us_stats'] = $values['usStats'];
              $data['ds_stats'] = $values['dsStats'];
              $data['version'] = $values['firmwareVer']; //软件版本
              $data['flash_size'] = $values['flashSize'];
              $data['hardware_ver'] = $values['hardwareVer'];
              $data['ip'] = $values['ip'];
              $data['led'] = $values['led']=='true'?"1":'0';
              $data['main_chip_class'] = $values['mainChipClass'];

              $data['ram_size'] = $values['ramSize'];
              if(array_key_exists("productCLass",$values))
              {
                $data['device_type'] = $values['productCLass'];
              }
              if(array_key_exists("type",$values))
              {
                $data['device_type'] = $values['type'];
              }
              $data['manufacturer'] = $values['vendor'];
              $data['wan'] = str_replace('"',"'",json_encode($values['wan'][0]));
              $data['ap_online_num'] = $values['apOnlineNum'];
              $data['dev_allnum'] = $values['devAllNum'];
              $data['dev_onlinenum'] = $values['devOnlineNum'];
              $data['device_class'] = 'gateway';
              $data['uniacid'] = $gateway['uniacid'];

              $WhiteListModel->insert($data);
              $TblOnofflineGatewayModel->insert($data);

              $msg['status'] = 2;
              $msg['status_text'] = '添加成功';
            }

            $event['event_name'] = $data['link_status'];
            $event['event_code'] = '网关设备:'.$data['mac'];
          }

          #写入事件记录
          $event['event_code'] .= $data['link_status']=='0'?"上线":"下线";
          $event['mac'] = $data['mac'];
          $event['parent_mac'] = $data['parent_mac'];
          $event['add_time'] = time();
          $TblDeviceOnofflineEventModle->insert($event);

          $response = $msg;
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    #gw_upstream接口
    public function gw_upstream()
    {
      if(I('')){
        #存储上下线文件
        $str_content = json_encode(I(''));
        $file = fopen("./api/data/".date("Y_m_d")."_upstream.txt", "a");
        fwrite($file,"时间：".date("Y-m-d H:i:s")." 数据：".$str_content." \r\n");
        fclose($file);

        try{
          #网关下挂设备数据表
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $WhiteListModel =  loadModel('TblGatewayWhiteList');
          $TblDeviceInfoModel = loadModel('TblDeviceInfo');
          $map['mac'] = str_replace(":","",I("mac"));

          #字段转化
          $values = I('values');
          #查找uniacid
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          if(I('type')=="sta"){
            $onoff_map['mac'] = str_replace(":","",I('mac'));
          }else{
            $onoff_map['mac'] = str_replace(":","",I('mac'));
          }
          $gateway = $TblOnofflineGatewayModel->findByMap("uniacid",$onoff_map);

          $msg['status'] = 0;
          $msg['status_text'] = $this->const_arr['error'];
          if(!$gateway['uniacid']){
              $msg['status_text']="该网关未存入白名单";
              ajaxReturn($msg);
          }

          if(I("type")=="sta"){
            foreach ($values as $key => $v) {
              if($v['status']=="OFFLINE"){
                continue;
              }
              $data['block_time'] = time() - $v['blockTime'];
              $data['device_name'] = $v['deviceName'];
              $data['dhcp_name'] = $v['dhcpName'];
              $data['consult_up'] = $v['upSpeed'];
              $data['consult_down'] = $v['downSpeed'];
              $data['up_total_flow'] = $v['upTotalFlow'];
              $data['down_total_flow'] = $v['downTotalFlow'];
              $data['us_stats'] = $v['usStats'];
              $data['ds_stats'] = $v['dsStats'];
              $data['ip'] = $v['ip'];
              $data['online_time'] = $v['onlineTime'];
              $data['last_online_time'] = time() - $v['offlineTime'] - $v['onlineTime'];
              $data['last_outline_time'] = time() - $v['offlineTime'];
              $data['down_speed_limit'] = $v['downSpeedLimit'];
              $data['up_speed_limit'] = $v['upSpeedLimit'];
              if(stripos($v['connectInterface'],"SSID")!==false)
              {
                $data['link_type'] = "wireless";
              }else {
                $data['link_type'] = "wired";
              }
              $data['connect_interface'] = $v['connectInterface']; //接入类型
              $data['interface_port'] = $v['connectType']; //接入端口
              $data['mac'] = str_replace(":","",$v["mac"]);
              $data['parent_mac'] = str_replace(":","",$v['parentMac']);
              if(array_key_exists("productCLass",$v))
                $data['device_type'] = $v['productCLass'];
              $data['device_class'] = I("type");
              $data['uniacid'] = $gateway['uniacid'];

              $map['mac'] = $data['mac'];
              if($TblDeviceInfoModel->where($map)->getCount()>0){
                $TblDeviceInfoModel->where($map)->save($data);
                $msg['status'] = 1;
                $msg['status_text'] = '修改成功';
              }else{
                $data['create_time'] = time();
                $TblDeviceInfoModel->insert($data);
                $msg['status'] = 2;
                $msg['status_text'] = '添加成功';
              }
            }

          }else if(I("type")=="gateway"){
            #添加网关数据
            $data['mac'] =  str_replace(":","",I("mac"));
            $data['alias'] = $values['name'];
            $data['up_velocity'] = $values['upSpeed'];
            $data['down_velocity'] = $values['downSpeed'];
            $data['online_time'] = $values['onlineTime'];
            $data['last_login_time'] = time() - $values['onlineTime'];
            $data['cpu_occupy'] = $values['cpu'];
            $data['memory_occupy'] = $values['ram'];
            $data['down_total_flow'] = $values['downTotalFlow'];
            $data['up_total_flow'] = $values['upTotalFlow'];
            $data['us_stats'] = $values['usStats'];
            $data['ds_stats'] = $values['dsStats'];
            $data['version'] = $values['firmwareVer'];
            $data['flash_size'] = $values['flashSize'];
            $data['hardware_ver'] = $values['hardwareVer'];
            $data['ip'] = $values['ip'];
            $data['led'] = $values['led']=='true'?"1":'0';
            $data['main_chip_class'] = $values['mainChipClass'];

            $data['ram_size'] = $values['ramSize'];
            if(array_key_exists("productCLass",$values))
            {
              $data['device_type'] = $values['productCLass'];
            }
            if(array_key_exists("type",$values))
            {
              $data['device_type'] = $values['type'];
            }
            $data['manufacturer'] = $values['vendor'];
            $data['wan'] = str_replace('"',"'",json_encode($values['wan'][0]));
            $data['ap_online_num'] = $values['apOnlineNum'];
            $data['dev_allnum'] = $values['devAllNum'];
            $data['dev_onlinenum'] = $values['devOnlineNum'];
            $data['device_class'] = I('type');
            $data['packet_loss_rate'] = $values['ping']['frameLossRate'];
            $data['delay_time'] = $values['ping']['averageDelayMs'];
            $data['transmitPower5G'] = $values['transmitPower5G'];
            $data['transmitPower24G'] = $values['transmitPower24G'];
            $data['BandCapability'] = $values['bandCapability'];
            $data['PPPOE'] = $values['pppoe'];
            $data['SupportedRFBand'] = $values['supportedRFBand'];

            #统计掉线次数
            $TblDeviceOnofflineEventModel = loadModel('TblDeviceOnofflineEvent');
            $onoff_map['mac'] = $data['mac'];
            $onoff_map['add_time'][] = array(">",strtotime(date("Y-m-d")));
            $onoff_map['add_time'][] = array("<",time());
            $onoff_map['event_name'] = '1'; //下线事件
            $onoff_times = $TblDeviceOnofflineEventModel->where($onoff_map)->getCount();
            $data['dropping_freq'] = $onoff_times;
            $data['uniacid'] = $gateway['uniacid'];

            if($TblOnofflineGatewayModel->findByMap("mac",$map)){
              $TblOnofflineGatewayModel->where($map)->save($data);
              // echo $TblOnofflineGatewayModel->getSql();exit;
              $WhiteListModel->where($map)->save($data);
              $msg['status'] = 1;
              $msg['status_text'] = '修改成功';
            }else{
              $data['create_time'] = time();
              $WhiteListModel->insert($data);
              $TblOnofflineGatewayModel->insert($data);

              $msg['status'] = 2;
              $msg['status_text'] = '添加成功';
            }
          }

          $response = $msg;
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
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

    public function detrepeat()
    {
      $TblDeviceInfoModel = loadModel("TblDeviceInfo");
      $device = $TblDeviceInfoModel->info('id,mac');
      $arr=array();
      //php的数组都是needle在前，arr在后
      foreach ($device as $key => $value) {
        if(!in_array($value['mac'],$arr))
        {
          $arr[] = $value['mac'];
          continue;
        }
        $map['id'] = $value['id'];
        $TblDeviceInfoModel->where($map)->delete();
      }
      echo "删除完成！";
    }

    #写入记录
    // $data = json_encode(I(''));
    // $file = fopen("api/data/".date("Y_m_d")."_upstream.txt", "a");
    // fwrite($file,"时间：".date("Y-m-d H:i:s")." 数据：".$data." \r\n");
    // fclose($file);


    public function reset_link()
    {
      set_time_limit(0);
      $TblDeviceInfoModel = loadModel("TblDeviceInfo");
      $device = $TblDeviceInfoModel->info('id,connect_interface,link_type');
      foreach ($device as $key => $v) {
        if(stripos($v['connect_interface'],"SSID")!==false)
        {
          $v['link_type'] = "wireless";
        }else {
          $v['link_type'] = "wired";
        }
        $TblDeviceInfoModel->save($v);
      }

    }


    public function stomp_test()
    {
        $user = "****";
        $password = "*****";
        $host = "localhost";
        $port = "23434";
        $destination  = '/topic/event';

        try {
          $url = 'tcp://'.$host.":".$port;
          $stomp = new Stomp($url, $user, $password);
          $stomp->subscribe($destination);

          $start = now();
          $count = 0;
          echo "Waiting for messages...\n";
          while(true) {
            $frame = $stomp->readFrame();
            if($frame) {

              if( $frame->command == "MESSAGE" ) {

                  echo $frame->body."<br/>";

              } else {
                echo "Unexpected frame.\n";
                var_dump($frame);
              }
            }
          }
        } catch(StompException $e) {
          echo $e->getMessage();
        }
    }

}
