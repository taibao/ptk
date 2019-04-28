<?php
/*
  get data form java interface
  author : vitas
  date : 2019-2-21
*/

class hardware extends Controller{

    public $api_url = '49.4.69.86';
    public $api_port = '8765';

    /**
     * 获取单个设备信息
     * @param String $url     请求的地址
     * @param Array  $mac      接收的设备id值 测试值：6A8E9EA26A8E
     * @param Array  $header  自定义的header数据
     * @return String
     */
    public function getOnlineSn($mac=''){
      try{
        if(I('')||$mac){
          $id = I('mac')?I('mac'):$mac;
      		$url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/onoffline/id/'.$id;
      		$header[] = 'access-token:'.get_session('token');
      		$response = tocurl($url, $header);
      		$response = json_decode($response, true);
          if($data)return $response;
        }
      }catch(Exception $e){
          $response = getresponse('error');
      }
    	ajaxReturn($response);
    }

    /**
     * 获取详情
     * @param String $url     请求的地址
     * @param String mac      接收mac地址 测试值：446A2ED1B12F
     * @param Array  $header  自定义的header数据
     * @param Array  $content POST的数据
     * @return String
     */
    public function gwSetting($data=array()){
      try{
        if(I('')||$data){
          $mac = I('mac')?I('mac'):$data['mac'];
          $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/'.$mac.'/setting';

          $header[] = 'access-token:'.get_session('token');
          $header[] = 'Content-Type:application/json';

          $content['mac'] = I('mac')?I('mac'):$data['mac'];
          $content['actions'] = json_decode('['.json_encode(I('action')?I('action'):$data['action']).']');
          $content = '['.json_encode($content).']';
          $action = I('action');
          //修改别名
          $response = tocurl($url, $header,'POST',$content);
          $response = json_decode($response, true);

          if(array_key_exists("name",$action)&&$response['errorCode']=="0"){
            if($action['name']=='configOnt'&&$action['values']['gatewayName']){
              $WhiteListModel =  loadModel('TblGatewayWhiteList');
              $OnofflineGatewayModel =  loadModel('TblOnofflineGateway');
              $map['mac'] = I('mac');
              $update_data['mac'] = I('mac');
              $update_data['alias']=urldecode($action['values']['gatewayName']);
              $WhiteListModel->where($map)->save($update_data);
              $OnofflineGatewayModel->where($map)->save($update_data);
            }
          }

          if($data)return $response;
        }
      }catch(Exception $e){
          $response = getresponse('error');
      }
      ajaxReturn($response);
    }

/**
 * 获取所有在线设备
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
public function getOnlineAll(){
  try{
    $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/onoffline/all';
    $header[] = 'access-token:'.get_session('token');
    $response = tocurl($url, $header);
    $response = json_decode($response, true);
    if($data)return $response;
  }catch(Exception $e){
      $response = getresponse('error');
  }
  ajaxReturn($response);
}

/**
 * 获取告警设备
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @param  String $mac     自定义的mac
 * @return Array
 */
public function getAlarmMac($mac=''){
  try{
    if(I('')||$mac){
      $mac = I('mac')?I('mac'):$mac;
      $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/alarm/'.$mac;
      $header[] = 'access-token:'.get_session('token');
      $response = tocurl($url, $header);
      $response = json_decode($response, true);
      if($mac)return $response;
    }
  }catch(Exception $e){
      $response = getresponse('error');
  }
  ajaxReturn($response);
}

/**
 * 获取所有告警设备
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getAlarmAll(){
   try{
     $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/alarm/all';
     $header[] = 'access-token:'.get_session('token');
     $response = tocurl($url, $header);
     $response = json_decode($response, true);
     if($data)return $response;
   }catch(Exception $e){
       $response = getresponse('error');
   }
   ajaxReturn($response);
 }

/**
 * 获取doaction
 * @param String $url     请求的地址
 * @param String mac      接收mac地址 测试值：446A2ED1B12F
 * @param Array  $header  自定义的header数据
 * @param Array  $content POST的数据
 * @return String
 *[{
 *	"mac":"446A2ED1B12F",
 *  	"action":{
 *  		"name":"getConnectDevList",
 *  		"values":""
 *  	}
 *  }]
 */
public function get_doaction($data=array()){
  try{
    if(I('')||$data){
      $mac = I('mac')?I('mac'):$data['mac'];
      $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/'.$mac.'/action/do';

      if(I('action')){
        $action = I('action');
      }

      $header[] = 'access-token:'.get_session('token');
      $header[] = 'Content-Type:application/json';

      $content['mac'] = I('mac')?I('mac'):$data['mac'];
      $content['actions']['name']   = $action['name']?$action['name']:$data['name'];
      switch($content['actions']['name']){
        case 'speedTest':
            $values['ftpHost']="114.116.186.36";
            $values['ftpUserName']="uftp";
            $values['ftpPassword']="Netopen_123";
            $values['ftpPort'] = 21;
            $values['ftpDownloadPath']="/public";
            $values['ftpUploadPath']="/public";
            $values['ftpName'] = "111.log";
            $values['fileSize'] = 5419045;
            $content['actions']['values'] = $values;
        break;
        default:
            $content['actions']['values'] = $action['values']?$action['values']:$data['values'];
        break;
      }
      $content['actions'] = json_decode('['.json_encode($content['actions']).']');
      $content = '['.json_encode($content).']';
      $response = tocurl($url, $header,'POST',$content);
      $response = json_decode($response, true);
      if($data)return $response;
    }
  }catch(Exception $e){
      $response = getresponse('error');
  }
  ajaxReturn($response);
}

/**
 * 获取在线类型
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getOnlineType(){
   try{
     $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/onoffline/type/ap';
     $header[] = 'access-token:'.get_session('token');
     $response = tocurl($url, $header);
     $response = json_decode($response, true);
     if($data)return $response;
   }catch(Exception $e){
       $response = getresponse('error');
   }
   ajaxReturn($response);
 }

 /**
  * 获取在线类型
  * @param  String $url     请求的地址
  * @param  Array  $header  自定义的header数据
  * @return Array
  */
  public function getOnlineChild($mac=""){
    try{
      if(I('')||$mac){
        $mac = I('mac')?I('mac'):$mac;
        $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/onoffline/history/children/'.$mac.'/offline';
        $header[] = 'access-token:'.get_session('token');
        $response = tocurl($url, $header);
        $response = json_decode($response, true);
        if($mac)return $response;
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }


/**
 * 获取上报设备实时信息
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getUpstreamAll(){
   try{
     $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/upstream/all';
     $header[] = 'access-token:'.get_session('token');
     $response = tocurl($url, $header);
     $response = json_decode($response, true);
     if($data)return $response;
   }catch(Exception $e){
       $response = getresponse('error');

   }
   ajaxReturn($response);
 }

 /**
  * 获取mac设备实时信息
  * @param  String $url     请求的地址
  * @param  Array  $header  自定义的header数据
  * @return Array
  */
  public function getUpstreamMac($mac=""){
    try{
      if(I('')||$mac){
        $mac = I('mac')?I('mac'):$mac;
        $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/gateway/upstream/'.$mac;
        $header[] = 'access-token:'.get_session('token');
        $response = tocurl($url, $header);
        $response = json_decode($response, true);
        if($mac)return $response;
      }
    }catch(Exception $e){
        $response = getresponse('error');

    }
    ajaxReturn($response);
  }

  /**
   * 获取所有类型
   * @param  String $url     请求的地址
   * @param  Array  $header  自定义的header数据
   * @return Array
   */
   public function getTypeGetAll(){
     try{
       $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/type/all';
       $header[] = 'access-token:'.get_session('token');
       $response = tocurl($url, $header);
       $response = json_decode($response, true);
       if($data)return $response;
     }catch(Exception $e){
         $response = getresponse('error');

     }
     ajaxReturn($response);
   }

   /**
    * 获取所有域名
    * @param  String $url     请求的地址
    * @param  Array  $header  自定义的header数据
    * @return Array
    */
    public function getDomainAll(){
      try{
        $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/all';
        $header[] = 'access-token:'.get_session('token');
        $response = tocurl($url, $header);
        $response = json_decode($response, true);
        if($data)return $response;
      }catch(Exception $e){
          $response = getresponse('error');

      }
      ajaxReturn($response);
    }

/**
 * 添加域名
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getDomainAdd($data=array()){
   try{
     if(I('')||$data){
       $mac = I('mac')?I('mac'):$data['mac'];
       $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/add';

       $header[] = 'access-token:'.get_session('token');
       $header[] = 'Content-Type:application/json';

       $content['mac'] = I('mac')?I('mac'):$data['mac'];
       $content['action']['name']   = I('action_name')?I('action_name'):$data['action_name'];
       $content['action']['values'] = I('action_values')?I('action_values'):$data['action_values'];
       $content = '['.json_encode($content).']';

       $response = tocurl($url, $header,'POST',$content);
       $response = json_decode($response, true);
       if($data)return $response;
     }
   }catch(Exception $e){
       $response = getresponse('error');
   }
   ajaxReturn($response);
 }


/**
 * 添加域名
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getDomainDel($mac=''){
   try{
     if(I('')||$mac){
       $mac = I('mac')?I('mac'):$mac;
       $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/del/'.$mac;
       $header[] = 'access-token:'.get_session('token');
       $header[] = 'Content-Type:application/json';
       $response = getdelurl($url, $header);
       if($mac)return $response;
     }
   }catch(Exception $e){
       $response = getresponse('error');
   }
   ajaxReturn($response);
 }

/**
 * 网关白名单修改
 * @param  String $url     请求的地址
 * @param  Array  $header  自定义的header数据
 * @return Array
 */
 public function getDomainModify($data=array()){
   try{
     if(I('')||$data){
       $mac = I('mac')?I('mac'):$data['mac'];
       $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/whitelist/add';

       $header[] = 'access-token:'.get_session('token');
       $header[] = 'Content-Type:application/json';

       $content['mac'] = I('mac')?I('mac'):$data['mac'];
       $content['sn']   = I('sn')?I('sn'):$data['sn'];
       $content['type'] = I('type')?I('type'):$data['type'];
       $content['domain'] = I('domain')?I('domain'):$data['domain'];
       $content = '['.json_encode($content).']';

       $response = tocurl($url, $header,'POST',$content);
       $response = json_decode($response, true);
       if($data)return $response;
     }
   }catch(Exception $e){
       $response = getresponse('error');
   }
   ajaxReturn($response);
 }

 /**
  * 网关白名单添加
  * @param  String $url     请求的地址
  * @param  Array  $header  自定义的header数据
  * @return Array
  */
  public function getwhitelistAdd($data=array()){
    try{
      if(I('')||$data){
        $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/whitelist/add';

        $header[] = 'access-token:'.get_session('token');
        $header[] = 'Content-Type:application/json';

        $content['mac'] = I('mac')?I('mac'):$data['mac'];
        $content['sn']   = I('sn')?I('sn'):$data['sn'];
        $content['type'] = I('type')?I('type'):$data['type'];
        $content['domain'] = I('domain')?I('domain'):$data['domain'];
        $content = '['.json_encode($content).']';

        $response = tocurl($url, $header,'POST',$content);
        $response = json_decode($response, true);
        if($data)return $response;
      }
    }catch(Exception $e){
        $response = getresponse('error');
    }
    ajaxReturn($response);
  }

  /**
   * 网关白名单删除
   * @param  String $url     请求的地址
   * @param  Array  $header  自定义的header数据
   * @return Array
   */
   public function whitelistDel($data=array()){
     try{
       if(I('')||$data){
         $mac = I('mac')?I('mac'):$data['mac'];
         $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/whitelist/del';

         $header[] = 'access-token:'.get_session('token');
         $header[] = 'Content-Type:application/json';

         $content['mac'] = I('mac')?I('mac'):$data['mac'];
         $content['sn']   = I('sn')?I('sn'):$data['sn'];
         $content['type'] = I('type')?I('type'):$data['type'];
         $content['domain'] = I('domain')?I('domain'):$data['domain'];

         $response = getdelurl($url, $header,$content);
         if($data)return $response;
       }
     }catch(Exception $e){
         $response = getresponse('error');
     }
     ajaxReturn($response);
   }

   /**
    * 通过域名网关白名单删除
    * @param  String $url     请求的地址
    * @param  Array  $header  自定义的header数据
    * @return Array
    */
    public function whiteDelByDomain($data=array()){
      try{
        if(I('')||$data){
          $mac = I('mac')?I('mac'):$data['mac'];
          $url = 'http://'.$this->api_url.':'.$this->api_port.'/object/domain/whitelist/2';

          $header[] = 'access-token:'.get_session('token');
          $header[] = 'Content-Type:application/json';

          $content['mac'] = I('mac')?I('mac'):$data['mac'];
          $content['sn']   = I('sn')?I('sn'):$data['sn'];
          $content['type'] = I('type')?I('type'):$data['type'];
          $content['domain'] = I('domain')?I('domain'):$data['domain'];

          $response = getdelurl($url, $header,$content);
          if($data)return $response;
        }
      }catch(Exception $e){
          $response = getresponse('error');
      }
      ajaxReturn($response);
    }



}
