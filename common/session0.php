<?php
class SysSession implements SessionHandlerInterface
{
    public static $uniacid="0";
  	public static $openid="";
  	public static $expire=3600;
    public $sessionModel = null;

    public function start($uniacid, $openid, $expire = 3600) {
  		SysSession::$uniacid = $uniacid;
  		SysSession::$openid = $openid;
  		SysSession::$expire = $expire;
  	}

    public function open($save_path, $session_name)
    {
        global $user_arr,$con;
        $arr = $user_arr['database']['default'];
        $servername = $arr['host'];
        $username = $arr['username'];
        $password = $arr['password'];
        $database = $arr['dbname'];
        $port = $arr['port'];
        $con = new mysqli($servername, $username, $password,$database,$port);
        if ($con->connect_error) {
            die("连接失败" . $con->connect_error);
            return false;
        }
        return true;
    }

    public function close()
    {
      global $con;
      $con->close();
      return true;
    }

    public function read($id)
    {
      global $con;
      $sql = "select sid,data from  ims_core_sessions  where sid='$id'";
      $result = $con->query($sql);
      if ($result->num_rows > 0) {
          $row = mysqli_fetch_object($result);
          return $row->data;
      } else {
          return "";
      }
    }

    public function write($id,$data)
    {
      global $con;
      $uniacid = SysSession::$uniacid;
    	$openid  = SysSession::$openid;
    	$expire  = SysSession::$expire;

      $expiretime = time() + $expire;
      $sql = "select data from ims_core_sessions where sid='$id'";
      $result = $con->query($sql);

      if ($result->num_rows == 0) {
          $sql = "insert into ims_core_sessions(sid,data,expiretime,uniacid,openid) value('$id','$data','$expiretime','$uniacid','$openid')";
      } else {
          $sql = "update ims_core_sessions set data='$data' , expiretime='$expiretime', uniacid='$uniacid', openid='$openid' where sid='$id'";
      }
      $result =  $con->query($sql);
      return true;
    }

    public function destroy($id)
    {
      global $con;
      $sql = "delete from ims_core_sessions where sid='$id'";
      $con->query($sql);
      return true;
    }

    public function gc($expiretime)
    {
      global $con;
      $expiretime = time();
      $sql = "delete from ims_core_sessions where  expiretime<'$expiretime'";
      $con->query($sql);
      return true;
    }
}

if(I('openId'))
{
  $search_map['openid'] = I('openId');
  $McMappingFansModel = loadModel("McMappingFans");
  $getUnicaidInfo = $McMappingFansModel->findByMap("uniacid",$search_map);
  SysSession::$uniacid = $getUnicaidInfo['uniacid'];
  SysSession::$openid = I('openId');
  SysSession::$expire = 36000;
}

$handler = new SysSession();
session_set_save_handler($handler, true);
register_shutdown_function('session_write_close');
