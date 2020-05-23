<?php
//控制器基类
class Model{
    protected $db;
    public $table_pre = 'ims_';

    public function __construct($model_name=''){
      if($model_name==''){
        $table = $this->table_pre.strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',substr(get_class($this),0,-5)));
      }else{
        $table = $this->table_pre.strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1',$model_name));
      }
      $this->db = DB2::getInstance($table);
    }
	/*
	*当获取属性失败时调用
	*/
	public function __get($param){
		// $ptk = PTK::getInstance();
		// return $ptk->$param;
	}

  #更改数据库
  #@param $database 选择的数据库
  public function db($dbselect = 'default'){
    $this->db->dbselect = $dbselect;
    if(!$this->db->connect())
      echo "connect error";
    else
      return $this;//返回自身对象
  }
    #组装数据
	#@param $data 查询得到的结果集
	public function setup($data){
		if(empty($data)){
			return false;
		}
		//print_r($data);exit;
		//判断$data为多维数组
		if(is_object(current($data))){
			foreach($data as $k=>$v){
				// $data[$k] = $this->_setup($v);
        $data[$k] = $v;
			}
		}
		// else{
		// 	$data = $this->_setup($data);
    // }
		return $data;
	}

	#组装数据（具体操作）
	#@param $data 查询得到的结果集
	public function _setup($data){
		return $data;
	}

    public function getList(){
        if(!$this->db->select())
        {
            die($this->db->getLastSql().'查询失败');
        }
        $result = $this->setup($this->db->getAll());
        $result = $this->object_array($result);
        if(array_key_exists('0',$result)&&$result[0]==''){
          $result=false;
        }
        return $result;
    }

    public function object_array($array) {
        $result = array();
        if($array){
            foreach ($array as $k => $v) {
              if(is_object($v)) {
                $result[] = (array)$v;
              }
              if(is_array($v)) {
                  foreach($v as $key=>$value) {
                      $v[$key] = $this->object_array($value);
                  }
                  $result[] = $v;
              }
            }
        }
        return $result;
    }

    #获取单行数据
    public function find(){
      if(!$this->db->select()){
          die($this->db->getLastSql().'查询失败');
      }
      $data  = (array)$this->db->getOne();
      if(array_key_exists('0',$data)&&$data[0]==''){
        $data=false;
      }
      return $this->setup($data);
    }

    #where 函数
    #@param $condition 数组
    public function where($condition){
      $this->db->where($condition);
      return $this;
    }

    public function commit(){
      $this->db->commit();
      return $this;
    }

    public function rollback(){
      $this->db->rollback();
      return $this;
    }

    public function begin(){
      $this->db->begin();
      return $this;
    }

    #join 函数
    public function join($condtion){
      $this->db->join($condtion);
      return $this;
    }
    //适配器模式，调用db接口
    public function limit($offset=0,$limit=-1){
        $this->db->limit($offset,$limit);
        return $this;
    }

    #返回表最后的sql
    public function getSql(){
      return $this->db->getLastSql().'<br/>';
    }

    /*
        返回order排序
     */

    public function order($order){
        $this->db->order($order);
        return $this;
    }

    /*
        返回字段
     */

    public function field($field){
        $this->db->field($field);
        return $this;
    }

    #添加函数
    public function insert($data){
      $data = $this->before_insert($data);
      if(!$data){
        return false;
      }
      $result = $this->db->insert($this->filter($data));//执行插入操作
      if($result){
        $result = $this->after_insert($data);
      }
      return $this->db->getLastId();//返回影响行数
    }

    public function mul_insert($data){
      return  $this->db->insert($data);
    }

    #添加函数
    public function filter($data){
      //得到数据标的字段信息
      $col_arr = array();
      $Fields = $this->db->getFieldInfo();
      foreach($Fields as $key=>$value){
        $col_arr[] = $value->Field;
      }
      $filter_arr = array(); //过滤数组
      if(is_mul_arr($data))
      {
        foreach ($data as $key => $value) {
          foreach ($value as $k => $d) {
            if(in_array($k,$col_arr)){
              $filter_arr[$key][$k] = $d;
            }
          }
        }
      }else{
        foreach($data as $k=>$d){
          if(in_array($k,$col_arr)){
            $filter_arr[$k] = $d;
          }
        }
      }
      return $filter_arr;
    }

    #插入前操作
    #@param $data 数组
    public function before_insert($data){
      return $data;
    }

    #插入前操作
    #@param $data 数组
    public function after_insert($data){
      return true;
    }

    #修改函数
    public function save($data){
	  $Fields = $this->db->getFieldInfo();
	  $key ='';
	  foreach($Fields as $v){
		  if($v->Key=='PRI'){ //已主键为更新条件
			  $key = $v->Field;
		  }
	  }
	  $arr = array();
	  foreach($data as $k=>$v){
		  // if($k!=$key){
			  $arr[$k] = $v;
		  // }
	  }

	  $data = $this->before_update($data);
      if(!$arr){
        return false;
      }
	  if(trim($this->db->condition)=="")
	  {
		 $result = $this->db->where($key."='".$data[$key]."'")->update($this->filter($arr));//执行插入操作
	  }
	  else
	  {
		  $result = $this->db->update($this->filter($arr));//执行插入操作
	  }
	  if($result){
        $result = $this->after_update($data);
      }
      return $result;//返回影响行数
    }

	public function before_update($data){
		return $data;
	}

	public function after_update($data){
		return true;
	}

    #返回行
   public function getCount(){
      $data = $this->field(" count(*) as nums ")->find();
      return $data['nums'];
   }

   public function delete(){
	  return $this->db->delete();
   }


   public function findByMap($field = '*', $map = '', $order = '')
   {
       $data = $this->field($field)->where($map)->order($order)->find();
       return $data;
   }

   public function info($field = true, $map = array(), $order = '')
   {
       $data = $this->field($field)->where($map)->order($order)->getList();
       return $data;
   }

   public function Count($map)
   {
       $data = $this->where($map)->getCount();
       return $data;
   }

   //查找插入数据是否已经存在
   //在修改中，新值与旧值不相等，且新值在数据库中已存在，则报数据已存在
   public function isexist($field,$new,$status_text,$old='',$filter='uniacid')
   {
     global $_W;
     $map[$field] = $new;
     if($filter)
     {
       $map[$filter] = $_W['uniacid'];
     }
     if($this->where($map)->getCount()>0)
     {
       //要判断修改的数据是不是本身
       if($new!=$old) //避免造成重复
       {
         msgReturn(false,'',$status_text);
       }
     }
   }

}
