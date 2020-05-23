<?php
/*
  the om_users interface
  author : vitas
  date : 2019-2-21
*/
class test extends Controller{

  //检测信息是否真实来自微信服务器，若
  public function checkSign($token) {
		$signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
		sort($signkey, SORT_STRING);
		$signString = implode($signkey);
		$signString = sha1($signString);
    file_put_contents('weixin_log.txt', $signString.':'.$_GET['signature'],FILE_APPEND|LOCK_EX);
		return $signString == $_GET['signature'];
	}

    public function checkweixin(){
      if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
	        file_put_contents("1.txt",json_encode($_GET), FILE_APPEND|LOCK_EX);
          file_put_contents('weixin_log.txt', "IP=".$_SERVER['REMOTE_ADDR'].'/',FILE_APPEND|LOCK_EX); //记录访问IP到log日志
          file_put_contents('weixin_log.txt', "QUERY_STRING=".$_SERVER['QUERY_STRING'].'/',FILE_APPEND);//记录请求字符串到log日志
          file_put_contents('weixin_log.txt', 'echostr='.htmlspecialchars($_GET['echostr']).'/',FILE_APPEND); //记录是否获取到echostr参数
          // exit(htmlspecialchars($_GET['echostr']));      //把echostr参数返回给微信开发者后台
      }
      $token = "d7Qzm977PclTpqTTqTg92Q6Onm79gi2i";

      $this->checkSign($token);
      exit($_GET['echostr']);
    }

    public function setdata(){
      if(I('')){
        try{
          $_SESSION['user']['name']="李梦梅";
          $_SESSION['user']['age']="88";
          $data['user'] = $_SESSION['user'];
          $data['session_id'] = session_id();
          $response = getresponse('success',$data);
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }

    public function getdata(){
      if(I('')){
        try{
          $data = $_SESSION['user'];
          $data['session_id'] = session_id();
          $response = getresponse('success',$data);
        }catch(Exception $e){
          $response = getresponse('error');
        }
        ajaxReturn($response);
      }
    }


    public function sort(){
      $arr = [23,12,43,53,2,532,45,67,332];
      // $arr = $this->read(100000);
      $time = time();
      echo "开始排序"."--时间：".date("Y-m-d H:i:s",$time),"<br/>";
      // $arr = asort($arr); //php自带的排序算法非常快 14万个的数据量顶多1秒排完

      // $arr = $this->insert($arr); //插入排序12秒 1万的数据量
      // $arr = $this->babo($arr); //冒泡排序21秒 1万的数据量
      // $arr = $this->select($arr); //选择排序14秒 1万的数据量
      $arr = $this->insert($arr); //快速排序1万的数据量1秒，14万的数据量2秒
      print_r($arr);
      echo "<br/>";
      echo "排序完成"."--时间：".date("Y-m-d H:i:s");
      // print_r($arr);exit;
      // $pos = $this->bt($arr,21);
      // print_r($pos);
    }

    //babo是一种固定的排序
    public function babo($arr=array()){
      $len = count($arr);
      for($i=0;$i<$len;$i++)
      {
        for($j=$i+1;$j<$len;$j++)
        {
          if($arr[$i]>$arr[$j])
          {
            $temp = $arr[$j];
            $arr[$j]=$arr[$i];
            $arr[$i] = $temp;
          }
        }
      }
      return $arr;
    }

    // public function bt($search,$arr){
    //   $min = 0;
    //   $max = count($arr)-1;
    //   while($min<=$max)
    //   {
    //     $mid = floor(($min+$max)/2);
    //     if($arr[$mid]>$search)
    //     {
    //       $max = $mid - 1;
    //     }
    //     else if($arr[$mid]==$search)
    //     {
    //       return $mid;
    //     }
    //     else{
    //       $min = $mid + 1;
    //     }
    //   }
    //   return "该数值不存在！";
    // }

    public function bt($arr,$search)
    {
      $min=0;
      $max=count($arr)-1;
      while($min<=$max)
      {
        $mid = floor(($min+$max)/2);
        if($arr[$mid]>$search)
        {
          $max = $mid -1;
        }
        else if($arr[$mid]==$search)
        {
          return $mid;
        }
        else{
          $min = $mid + 1;
        }
      }
      return "数据不存在";
    }


  	public function btsearch($max,$min)
  	{
  		while($min<=$max) //或for(;$min<=$max;)
  		{
  			$i = floor(($min + $max)/2);
  			if(4.5*$i+10>370)
  			{
  				$max = $i-1;
  			}
  			else if(4.5*$i+10==370)
  			{
  				return $i;
  			}
  			else
  			{
  				$min = $i + 1;
  			}
  		}
  		return '该数值不存在';
  	}


    public function put($content)
    {
      $file = fopen("./api/data/data.txt", "a");
      fwrite($file,$content);
      fclose($file);
    }

    public function read($size)
    {
      $file = fopen("./api/data/data.txt", "r");
      if(!$size)
      {
        $arr = fread($file,filesize("./api/data/data.txt"));
      }else{
        $arr = fread($file,$size);
      }
      fclose($file);
      return explode(",",$arr);
    }

    public function product_data()
    {
      $this->put(mt_rand());
      for($num=1;$num<100000;$num++)
      {
        $this->put(','.mt_rand());
      }
    }

  	/*快速排序算法*/
  	public function qsort($arr=array())
  	{
  		$length = count($arr);
  		if($length<=1){
  			return $arr;
  		}
  		$key = $arr[0];
  		$left_array = array();
  		$right_array = array();
  		for($i=1;$i<$length;$i++)
  		{
  			if($arr[$i]<$key)
  			{
  				$left_array[] = $arr[$i];
  			}
  			else
  			{
  				$right_array[] = $arr[$i];
  			}
  		}
  		$left_array = $this->qsort($left_array);
  		$right_array = $this->qsort($right_array);
      return array_merge($left_array,array($key),$right_array);
  	}


  	//冒泡排序
  	public function pao($arr)
  	{
  		$len = count($arr);
  		for($i=0;$i<$len;$i++)
  		{
  			for($j=$i+1;$j<$len;$j++)
  			{
  				if($arr[$i]>$arr[$j])
  				{
  					$temp = $arr[$i];
  					$arr[$i] = $arr[$j];
  					$arr[$j] = $temp;
  				}
  			}
  		}
  		return $arr;
  	}

  	//选择排序
  	// public function select($arr){
    //   $len = count($arr);
  	// 	for($i=0;$i<$len-1;$i++) //需要排序的位置
  	// 	{
  	// 		$p = $i;//假定一个最小值的位置，p始终保持一趟遍历的最小值
  	// 		for($j=$i+1;$j<$len;$j++)
  	// 		{
  	// 			if($arr[$p]>$arr[$j])
  	// 			{
  	// 				$p = $j; //把最小值的位置传给$p
  	// 			}
  	// 		}
  	// 		//交换值
  	// 		if($p!=$i)
  	// 		{
  	// 			$temp = $arr[$p];
  	// 			$arr[$p] = $arr[$i];
  	// 			$arr[$i] = $temp;
  	// 		}
  	// 	}
  	// 	return $arr;
  	// }

    //选择
    public function select($arr){
      $len = count($arr);
      for($i=0;$i<$len-1;$i++)
      {
        $p = $i;
        for($j=$i+1;$j<$len;$j++)
        {
          if($arr[$p]>$arr[$j])
          {
            $p = $j; //选择最小值
          }
        }
        //若最小值位置变了
        if($p!=$i)
        {
          $temp = $arr[$p];
          $arr[$p] = $arr[$i];
          $arr[$i] = $temp;
        }
        //交换完成$i位置是最小值
      }
      return $arr;
    }

  	// public function insert($arr){
  	// 	$len = count($arr);
  	// 	for($i=1;$i<$len;$i++) //留一个0，用作哨兵 //第一个作为序列无需排序
  	// 	{
  	// 		$tmp = $arr[$i]; //选择第一个插入对象
  	// 		for($j=$i-1;$j>=0;$j--)
  	// 		{
  	// 			if($tmp<$arr[$j]) //把该对象插入到已排序序列中，如果小于前一个值就交换
  	// 			{
  	// 				$arr[$j+1] = $arr[$j]; //直接用$arr[$j]赋值
  	// 				$arr[$j] = $tmp;
  	// 			}
  	// 			else
  	// 			{
  	// 				break;
  	// 			}
  	// 		}
  	// 	}
  	// 	return $arr;
  	// }

    public function insert($arr){
      $len =  count($arr);
      for($i=1;$i<$len;$i++)
      {
        $tmp = $arr[$i];
        for($j=$i-1;$j>=0;$j--)
        {
          if($arr[$j]>$tmp)
          {
            $arr[$j+1] = $arr[$j];
            $arr[$j] = $tmp;
          }else{
            break;
          }
        }
      }
      return $arr;
    }

    public function q_sort($arr){
      $len = count($arr);
      if($len<=1)
      {
        return $arr;
      }
      $key = $arr[0];
      $left_array = array();
      $right_array = array();

      for($i=1;$i<$len;$i++){
        if($arr[$i]<$key)
        {
          $left_array[] = $arr[$i];
        }else{
          $right_array[] = $arr[$i];
        }
      }

      $left_array = $this->q_sort($left_array);
      $right_array = $this->q_sort($right_array);
      return array_merge($left_array,array($key),$right_array);
    }


    public function setSession(){
      set_session("name","chenglong");
      set_session("name","chenglong1");
      set_session("name","chenglong2");
      set_session("name","chenglong3");
      set_session("name","chenglong4");
    }

    public function getSession(){
      print_r(get_session("name"));exit;
    }

    public function setredis(){
      $redis = new redisDb();
      $WhiteListModel =  loadModel('TblGatewayWhiteList');
      $OnofflineGatewayModel =  loadModel('TblOnofflineGateway');
      $TblWxappAreasModel = loadModel("TblWxappAreas");

      // $result =  $TblWxappAreasModel->info("*");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("areas",$v['areas_id'],$v);
      // }

      // $result = $WhiteListModel->info("*");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("gatewaywhite",$v['mac'],$v);
      // }
      // unset($result);
      // $result = $OnofflineGatewayModel->info("id,white_id,mac,alias,sn,areas_id");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("gateway",$v['mac'],$v);
      //   $redis->setTableRow("gateway",$v['sn'],$v);
      //   $redis->setTableRow("gateway",$v['alias'],$v);
      //   $redis->setTableRow("gateway",$v['areas_id'],$v);
      // }

      unset($result);
      $result = $OnofflineGatewayModel->info("id,white_id,device_type");
      foreach($result as $k=>$v)
      {
        // $redis->setTableRow("gateway",$v['mac'],$v);
        // $redis->setTableRow("gateway",$v['sn'],$v);
        // $redis->setTableRow("gateway",$v['alias'],$v);
        $redis->setTableRow("gatewaytype",$v['device_type'],$v);
      }

      // unset($result);
      // $result = $OnofflineGatewayModel->info("*");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("gateway",$v['mac'],$v);
      // }
      //
      // unset($result);
      // $result = $OnofflineGatewayModel->info("*");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("gateway",$v['mac'],$v);
      // }
      //
      // unset($result);
      // $result = $OnofflineGatewayModel->info("*");
      // foreach($result as $k=>$v)
      // {
      //   $redis->setTableRow("gateway",$v['mac'],$v);
      // }
      echo "存入成功";
    }

    public function delredis(){
      $redis = new redisDb();
      $arr=$redis->keys("gatewaywhite*");
      foreach ($arr as $key => $value) {
        $redis->delList($key);
      }
      echo "存入成功";
    }


    public function getredis(){
      $redis = new redisDb();
      $it = NULL;
      $pattern = 'areas:*';
      $keysArr= $redis->getredis($pattern);
      foreach ($keysArr as $key=>$v)
      {
          print_r($redis->getHash($key));
      }
    }

    public function conf(){
      global $user_arr;
      print_r($user_arr);exit;
    }

    public function runJobWithThread(callable $exeWorkers,$maxJob,$threadNum)
    {
        $pids = array();
        $i = pcntl_fork();
        print_r($i);
        exit;

        for($i = 0; $i < $threadNum; $i++){

            $pids[$i] = pcntl_fork();

            switch ($pids[$i]) {
                case -1:
                    echo "fork error : {$i} \r\n";
                    exit;

                case 0:
                    $totalPage=ceil($maxJob / $threadNum);
                    $param = array(
                        //'lastid' => $maxJob / $threadNum * $i,
                        //'maxid' => $maxJob / $threadNum * ($i+1),

                        'page_start' => $totalPage*$i,
                        'page_end' => $totalPage*($i+1),
                    );

                    $exeWorkers($param);
                    exit;

                default:
                    break;
            }

        }

        foreach ($pids as $i => $pid) {
            if($pid) {
                pcntl_waitpid($pid, $status);
            }
        }
    }

    public function forktest()
    {
      // $this->runJobWithThread(function($para){
      //     echo '进程ID：'.getmypid().'，最小ID是【'.$para['page_start'].'】最大ID为：【'.$para['page_end'].'】'.PHP_EOL;
      // },10,10);
      $i = pcntl_fork();
      print_r($i);
      exit;
    }

    public function whiterepeat()
      {
        $WhiteListModel = loadModel("TblGatewayWhiteList");
        $gateways = $WhiteListModel->info('id,mac');
        $arr=array();
        //php的数组都是needle在前，arr在后
        foreach ($gateways as $key => $value) {
          if(!in_array($value['mac'],$arr))
          {
            $arr[] = $value['mac'];
            continue;
          }
          $map['id'] = $value['id'];
          $WhiteListModel->where($map)->delete();
        }
        echo "删除完成！";
      }

      public function onofflinerepeat()
        {
          $TblOnofflineGatewayModel = loadModel('TblOnofflineGateway');
          $gateways = $TblOnofflineGatewayModel->info('id,mac');
          $arr=array();
          //php的数组都是needle在前，arr在后
          foreach ($gateways as $key => $value) {
            if(!in_array($value['mac'],$arr))
            {
              $arr[] = $value['mac'];
              continue;
            }
            $map['id'] = $value['id'];
            $TblOnofflineGatewayModel->where($map)->delete();
          }
          echo "删除完成！";
        }


        public function devicerepeat()
          {
            $TblDeviceInfoModel = loadModel('TblDeviceInfo');
            $gateways = $TblDeviceInfoModel->field('id,mac')->getList();
            $arr=array();
            //php的数组都是needle在前，arr在后
            foreach ($gateways as $key => $value) {
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

          public function getswoole(){
            $str =  file_get_contents("http://127.0.0.1:9502");
            echo $str;
          }


  }
