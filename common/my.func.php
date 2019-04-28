<?php
/**
 * author:vitas zhuo
 * date:2019-3-29
 */
function ajaxReturn($data,$type='') {
    if(empty($type)) $type  =   "json";
    switch (strtoupper($type)){
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data));
        case 'XML'  :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler  =   '';
            exit($handler.'('.json_encode($data).');');
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}

function getcloumns($results,$feild){
    $arr = array();
    foreach ($results as $value) {
        $arr[] = '"'.$value[$feild].'"';
    }
    return $arr;
}

function addfields($result,$field,$data){
	if($result){
		foreach ($result as $key => $value) {
			$result[$key][$field] = $data;
		}
	}
	return $result;
}

function I($name,$default='',$filter=null,$datas=null) {
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
				case 'raw'		 :
				$bodyData = @file_get_contents('php://input');
				$bodyData = json_decode($bodyData,true);
				$input = $bodyData[0]; break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
										if($_POST){
											$input  =  $_POST;
										}else{
											$bodyData = @file_get_contents('php://input');
											$bodyData = json_decode($bodyData,true);
											$input = $bodyData[0];
										}
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'path'    :
            $input  =   array();
            if(!empty($_SERVER['PATH_INFO'])){
                $depr   =   DIRECTORY_SEPARATOR;
                $input  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        case 'data'    :   $input =& $datas;      break;
        default:
            return NULL;
    }
    if(''==$name) { // 获取全部变量
        $data       =   $input;
        array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:'';
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        is_array($data) && array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:'';
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }

            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}

// 不区分大小写的in_array实现
function in_array_case($value,$array){
    return in_array(strtolower($value),array_map('strtolower',$array));
}

function getsubstr($str='',$len=5){
	return mb_strlen($str)<=$len?$str:mb_substr($str,0,$len).'...';
}

function IntToChr($index, $start = 65) {
		$str = '';
		if (floor($index / 26) > 0) {
				$str .= IntToChr(floor($index / 26)-1);
		}
		return $str . chr($index % 26 + $start);
}

function excelout($result,$columns,$filename=""){
		// ob_clean();
		ob_end_clean();
		load()->library('phpexcel/PHPExcel');//加载PHPExcel.php
		$obj_excel = new PHPExcel();
		//写入单元格
		$c = 0;
		foreach ($columns as $k => $v) {
			$obj_excel->getActiveSheet()->setCellValue(IntToChr($c).'1', $v);
			$c++;
		}

		$i=2;
		foreach($result as $key=>$r){
			$c = 0;
			foreach ($columns as $k => $v) {
				$obj_excel->getActiveSheet()->setCellValue(IntToChr($c).$i, $r[$k]);
				$c++;
			}
			$i++;
		}
		$obj_excel->createSheet();//创建表（默认sheet1）
		$obj_writer = PHPExcel_IOFactory::createWriter($obj_excel, 'Excel2007');
		if($filename==""){
			$filename=date('Ymd',time()).'_down.xlsx';//导出的文件名
		}
		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment;filename='.$filename);
		header('Cache-Control: max-age=0');
		$obj_writer->save('php://output');
};

function excel($arr,$path){
    $excel_path = $path;
    load()->library('phpexcel/PHPExcel');//加载PHPExcel.php
    $obj_excel = new PHPExcel();
    $obj_reader = PHPExcel_IOFactory::createReader('Excel2007');//选择读取的excel格式
    $obj_phpExcel = $obj_reader->load($excel_path);//根据excel加载excel
    $sheet = $obj_phpExcel->getSheet(0);//读入第一个表->"Sheet1"
    $number_row = $sheet->getHighestRow();//取得最后一行的行数（总行数）
    $highest_column = $sheet->getHighestColumn();//取得最后一列的标识
    $number_column= PHPExcel_Cell::columnIndexFromString($highest_column); //字母列转换为数字列(总列数)
    #列从0开始    行从1开始
    $data  = Array();
    for($r = 2;$r<=$number_row;$r++){
        foreach ($arr as $k => $v) {
            $data[$r-2][$v] = (string)$sheet->getCellByColumnAndRow($k,$r)->getValue();
        }
    }
    return $data;
};

#多维数组排序
function f_order($arr,$field,$sort){
		$order = array();
		foreach($arr as $kay => $value){
				$order[] = $value[$field];
		}
		if($sort==1){
				array_multisort($order,SORT_ASC,$arr);
		}else{
				array_multisort($order,SORT_DESC,$arr);
		}
		return $arr;
};

#显示网关状态
function getlink_status($link_status){
	$status = '';
	switch($link_status){
		case '0':$status='在线';break;
		case null:$status='离线';break;
		case '1':$status='离线';break;
		case '2':$status='异常';break;
	}
	return $status;
}

#显示网络体验
function getlink_quality($quality){
	$status = '';
	//0,极差；1，差；2，中；3，好；4，极好
	switch($quality){
		case '0':$status='极差';break;
		case '1':$status='差';break;
		case '2':$status='中';break;
		case '3':$status='好';break;
		case '4':$status='很好';break;
	}
	return $status;
}

#验证excel文件上传
function validate_excel($file){
	if(!$file['size']){
			message('文件上传不能为空！','','error');
		}
		if( $file['name'] && $file['error'] == 0){
		$type = @end( explode('.', $file['name']));
		$type = strtolower($type);
		if(!in_array($type, array('xls','xlsx','csv')) ){
				message('文件类型错误！','','error');
		}
		if($type=='xls'){
			$inputFileType = 'Excel5';
		}else{
			$inputFileType = 'Excel2007';
		}
		return true;
	}
	else{
			message('文件上传错误！','','error');
	}
}

#导入网关表字段
function getGatewayFileds(){
	return array('sn','mac','link_status','loid','broadband_account','area','manufacturer','cpe_type','os','os_version','device_class','device_type','remark','product_time','receive_time');
}

#返回状态信息
function getresponse($status,$data=array()){
	$response['type'] = 'setting';
	switch($status){
		case 'success':
			$response['errorCode'] = '0000';
			$response['errorDesc'] = '查询成功';
			if($data){
				$response['data'] = $data;
			}
		break;
		case 'error':
			$response['errorCode'] = '0005';
			$response['errorDesc'] = '参数传递出错';
			if($data){
				$response['remark'] = $data;
			}
		break;
	}
	return $response;
}

/**
 * 发送数据
 * @param String $url     请求的地址
 * @param Array  $header  自定义的header数据
 * @param Array  $method  发送的方式
 * @param Array  $content POST的数据
 * @return String
 */
function tocurl($url,$header=array('Content-Type:application/json'),$method='',$content=array()){
		$ch = curl_init();
		if(substr($url,0,5)=='https'){
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在 2：表示true
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS,5000);
		curl_setopt($ch, CURLOPT_URL, $url);
    // $leng = strlen(http_build_query($content));
    // $header[] = 'content-length:'.$leng;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		if($method=='POST'){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		}
		$response = curl_exec($ch);
		if($error=curl_error($ch)){
				die($error);
		}
		curl_close($ch);
		return $response;
}

function getdelurl($url,$header=array('Content-Type:application/json'),$data=array()){
    $data  = '['.json_encode($data).']';
    $ch = curl_init();
    curl_setopt ($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);
    curl_setopt ($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output,true);
		return $output;
}

function set_session($name, $data, $expire=600){
		$session_data = array();
		$session_data['data'] = $data;
		$session_data['expire'] = time()+$expire;
		$_SESSION[$name] = $session_data;
}

function get_session($name){
		if(isset($_SESSION[$name])){
				if($_SESSION[$name]['expire']>time()){
						return $_SESSION[$name]['data'];
				}else{
						clear_session($name);
				}
		}
		return false;
}

function clear_session($name){
		unset($_SESSION[$name]);
}

function sendsms($data,$openId){
	$sendapi='http://114.116.186.36:8762/captcha/phoneCaptcha';
	$response = tocurl($sendapi,array('Content-Type' => 'application/x-www-form-urlencoded'),"POST",$data);
	$response = json_decode($response,true);
	$_SESSION[$openId]['message_verify'] = $response['captchaData'][$data['phone']];
	return $response;
}

function now() {
  list($usec,$sec) = explode(' ', microtime());
  return ((float)$usec + (float)$sec);
}
