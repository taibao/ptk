<?php
//数据库连接类,单例模式
class DB2{
    static private $_instance = null;
    private $query = null;

    static public $_model_array = array();
    public $table = null;
    public $limit = '';
    public $order = '';
    public $field = '*';
    public $condition = '';
    public $sql = '';
    public $dbselect = 'default';


    private function __construct($table){
        $this->table = $table;
        $this->connect();
    }
    static public function getInstance($table){
        //单例模式只能静态调用
        if(self::$_instance ==null||!in_array($table,self::$_model_array))
        {
            self::$_instance = new self($table);//实例化自身
            self::$_model_array[] = $table;
        }
        return self::$_instance;
    }
    /*
    连接
     */
     public function connect(){
         global $user_arr;
         $dbbase = $user_arr['database'];
         $connect_str = $dbbase[$this->dbselect]['type'].':host='.$dbbase[$this->dbselect]['host'].';port='.$dbbase[$this->dbselect]['port'].';dbname='.$dbbase[$this->dbselect]['dbname'];
         $this->link = new PDO($connect_str,$dbbase[$this->dbselect]['username'],$dbbase[$this->dbselect]['password']);//连接成功
         $this->link->query('set names '.$dbbase[$this->dbselect]['charset']);
         return $this;
     }


    public function select(){
        $this->query = $this->link->query($this->getSql());
        return $this->query;
    }

    public function commit(){
      $this->query = $this->link->commit();
      return $this->query;
    }

    public function rollback(){
      $this->query = $this->link->rollBack();
      return $this->query;
    }

    public function begin(){
      $this->query = $this->link->beginTransaction();
      return $this->query;
    }

    public function getSql(){
        $this->sql = 'select '.$this->field.' from '.$this->table;
        if(!empty($this->condition)){
            $this->sql .= $this->condition;
        }
        if(!empty($this->order)){
            $this->sql .= $this->order;
        }
        if(!empty($this->limit)){
            $this->sql .= $this->limit;
        }
        $this->clearSql();
        return $this->sql;
   }

   //返回最后的sql语句
   public function getLastSql(){
        return $this->sql;
   }
   /*
   清空语句
    */
   public function clearSql(){
        $this->field = '*';
        $this->order = '';
        $this->limit = '';
        $this->condition = '';
   }

    /*
        返回limit
    *   @param $offset 偏移值
    *   @param $limit  限制条数
     */

    public function limit($offset=0,$limit=-1){
        if($offset <0){
            if($limit >0){
               $this->limit = ' limit '.$limit;//limit 1
            }
        }
        else{
            $this->limit = ' limit '.$offset.','.$limit;
        }
        return $this;
    }

    /*
        返回order排序
     */

    public function order($order){
        if(!empty($order)){
            $this->order = ' order by '.$order;
        }
        return $this;
    }

    /*
        返回字段
     */

    public function field($field){
        $this->field = $field;
        return $this;
    }


    /*
    返回全部数据
     */
    public function getAll(){
        return $this->query->fetchAll(PDO::FETCH_CLASS);
    }
    /*
    返回单行数据
     */
    public function getOne(){
        return $this->query->fetch(PDO::FETCH_OBJ);//取回一行作为返回
    }

    /**
     * 返回条件
     * @param $condtion 字符串可直接写where条件，数组时，字段=>值，字段=>array('连接'，'值'，'运算符')
     */
    public function where($condition=array()){
		    $i = 0;$j=0;$str='';
        if(!empty($condition)){
            if(is_array($condition)){
                if(stripos($this->condition,'where')===false){
                    $this->condition .= ' where ';
                }else{
                  $pos_len = stripos($this->condition,'where');
                  $this->condition = substr($this->condition,0,$pos_len);
                  $this->condition .= 'where ';
                }
                foreach ($condition as $key => $value) {
                  if(is_array($value)){
                      if(is_array($value[0])){
                        foreach ($value as $k => $v) {
                          if($i==0&&$k==0){
                            $str .=  ' '.$key.' '.$v[0].' "'.$v[1].'" ';
                            $i=1;
                          }else{
                            $str .= ' and '.$key.' '.$v[0].' "'.$v[1].'" ';
                          }
                        }
                        $this->condition .= $str;
                      }else{
                        if(empty($value[2])&&$i!=0){
                          $value['2'] = ' and ';
                        }
                        if($this->condition == ' where '){
                          $value['2'] = '';
                        }
                        if($j == 0){
                          $value[2] = $value[2].' (';
                          $j = 1;
                        }
                        //由关键字判断条件拼接
                        switch($value[0]){
                            case 'like':$this->condition .= $value[2].' `'.$key.'` '.$value[0].' "%'.$value[1].'%" ';break;
                            case 'not_like':$this->condition .= $value[2].' `'.$key.'` '.$value[0].' "%'.$value[1].'%" ';break;
                            case 'in':
                            $in_str = array();
                            foreach($value[1] as $v){
                                    $in_str[] = $v;
                            }
                            $in_str = join(',',$in_str);
                            if(!$in_str)$in_str='"-903$#@$9981232221"';
                            $this->condition .= $value[2].' `'.$key.'` '.$value[0].' ('.$in_str.') ';
                            break;
                            case 'not_in':
                            $in_str = array();
                            foreach($value[1] as $v){
                              $in_str[] = $v;
                            }
                            $in_str = join(',',$in_str);
                            if(!$in_str)$in_str='"-903$#@$9981232221"';
                            $this->condition .= $value[2].' `'.$key.'` '.$value[0].' ('.$in_str.') ';
                                 break;
                            default:$this->condition .= $value[2].' `'.$key.'` '.$value[0].' "'.$value[1].'" ';break;
                        }
                        $this->condition .= ') ';
                        $j=0;$i=1;
                      }
        					}
        					else{
                    if($i==0){
                        $this->condition .= ' `'.$key.'`='.'"'.$value.'" ';
                        $i=1;
                    }else{
                      $this->condition .= ' and `'.$key.'`='.'"'.$value.'"';
                    }
        					}
                }

              }else{
                if(empty($this->condition)){
                    $this->condition = ' where '. $condition;
                }else{
                  if(stripos($this->condition,'where')===false){
                    $this->condition .= ' where '.$condition;//字符串
                  }
                }
            }
        }
        return $this;
    }

    public function join($condition){
      $this->condition .= ' '.$condition;
      return $this;
    }
    /**
     * 返回条件
     * @param $condtion 字符串
     */
    public function where_or($condition=array()){
        if(!empty($condition)){
            if(is_array($condition)){
                if(empty($this->condition)){
                    $this->condition = ' where ';
                }
                foreach ($condition as $key => $value) {
                    $this->condition .= '`'.$key.'`='.'"'.$value.'" or ';
                }
                $this->condition = substr($this->condition,0,-3);
              }
        }
        return $this;
    }

    /**
     * 返回条件
     * @param $condtion 字符串
     */
    public function where_like($condition=array()){
        if(!empty($condition)){
            if(is_array($condition)){
                if(empty($this->condition)){
                    $this->condition = ' where ';
                }
                foreach ($condition as $key => $value) {
                    $this->condition .= '`'.$key.'` like '.'"%'.$value.'%" and ';
                }
                $this->condition = substr($this->condition,0,-4);
              }
        }
        return $this;
    }

    /**
     * 返回条件
     * @param $condtion 字符串
     */
    public function where_like_or($condition=array()){
        if(!empty($condition)){
            if(is_array($condition)){
                if(empty($this->condition)){
                    $this->condition = ' where ';
                }
                foreach ($condition as $key => $value) {
                    $this->condition .= '`'.$key.'` like '.'"%'.$value.'%" or ';
                }
                $this->condition = substr($this->condition,0,-4);
              }
        }
        return $this;
    }


    /*
    数据添加
    */
   public function insert($data=array()){
    $keys = array();
    $values = array();
    foreach($data as $key=>$value)
    {
        if(!is_array($value))
        {
          $value = str_replace('"','\"',$value);
          $keys[] = '`'.$key.'`';
          $values[] = '"'.$value.'"';
          $str = '('.join(',',$values).')';
        }else{
          $str .= '(';
          foreach ($value as $k => $v) {
            $v = str_replace('"','\"',$v);
            $str .= '"'.$v.'",';
          }
          $str = substr($str,0,-1);
          $str .= '),';
        }
    }
    //多维数组
    $last = end($data);
    if(is_array($last))
    {
      foreach ($last as $key => $value){
        $keys[] = '`'.$key.'`';
      }
      if($str)
      {
        $str = substr($str,0,-1);
      }
    }
    $this->sql = 'insert into '.$this->table.'('.join(',',$keys).') values '.$str;
    return $this->link->exec($this->sql);
   }

   #返回添加id
   public function getLastId(){
        return $this->link->lastInsertId();
   }

   #获取字段信息
   public function getFieldInfo(){
    $sql = "show columns from ".$this->table;
    $this->query = $this->link->query($sql);
    return $this->getAll();
   }

   #数据更新
   public function update($data=array()){
    $values = array();
    foreach($data as $key=>$value)
    {
        $value = str_replace('"','\"',$value);
        $values[] = '`'.$key.'` = '.'"'.$value.'"';
    }
  	if(trim($this->condition)==""){
  		die("不存在更新条件");
  	}
    $this->sql = 'update '.$this->table.' set '.join(',',$values).$this->condition;
    return $this->link->exec($this->sql);
   }

   #数据删除
   public function delete(){
    $this->sql = 'delete from '.$this->table.$this->condition;
    return $this->link->exec($this->sql);
  }

   #调用存储过程
   // public function procedure($condition,$param){
   //   $sql = 'SELECT name, colour, calories FROM fruit WHERE calories < :calories AND colour = :colour';
   //   $sth = $this->link->prepare($condition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
   //   $sth->execute($param);
   //   $red = $sth->fetchAll();
   //
   // }

}
