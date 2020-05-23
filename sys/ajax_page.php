<?php
 class ajax_page{

    public $cur_page = '';
    public $num = '';
    public $offset = '';
    public $total = '';

    public function __construct($total,$num='10',$jqName='sub_num') {
        $this->total = $total;
        if($_POST['num']){
          $_SESSION[$jqName] = $_POST['num'];
        }
        foreach ($_SESSION as $key => $value) {
          if(stripos($key,'_num')&&$key!=$jqName)
          {
            $_SESSION[$key] = '10';
          }
        }
        $this->num = $_SESSION[$jqName]?$_SESSION[$jqName]:$num;
        $this->cur_page = !empty($_POST['p'])?(int)$_POST['p']:1;

        $this->total_page = ceil($this->total/$this->num); //获取总页数
        //确定当前页
        if($this->cur_page<1){
            $this->cur_page = 1;
        }
        if($this->cur_page>$this->total_page){
            $this->cur_page = $this->total_page;
        }
        $this->offset   = $this->num * ($this->cur_page - 1);
    }

    public function getPage($url='',$page_num='5'){

        $float_page = floor($page_num/2);

        if($this->cur_page-$float_page<1){
            $start_page = 1;
        }
        else{
            $start_page = $this->cur_page - $float_page;
        }

        if($this->cur_page+$float_page>$this->total_page){
            $end_page = $this->total_page;
        }
        else{
            $end_page = $this->cur_page+$float_page;
        }

        $page = '<div class="page_div"><div class="page_total_div"><span class="page_total">Total:</span><span style="font-size:16px;padding-left:5px;">'.$this->total.'</span></div>';
        $page .= '<div id="pagination" class="pagination"> <ul>';

        $page .= '<select class="sub_page_select"  id="select_arrow_down">';
        $page .= '<option';
        if($this->num==10){
          $page.=' selected ';
        }
        $page .= ' value="10"> 10</option>';
        $page .= '<option ';
        if($this->num==20){
          $page.=' selected ';
        }
        $page .=' value="20"> 20</option>';
        $page .= '<option ';
        if($this->num==50){
          $page.=' selected ';
        }
        $page .=' value="50"> 50</option>';
        $page .='</select>&nbsp;<span class="page_line_total">Line</span>';

        // $page .= '<li class="class_page_num" url="'.$url.'1">首页</li>';
        if($this->cur_page-1>0)
        {
            $prv_page = $this->cur_page - 1;
            $page .= '<li id="prv_page" class="class_page_num" url="'.$url.''.$prv_page.'"></li>';
        }
        if($this->cur_page-5>0)
        {
            $prv5_page = $this->cur_page-5;
            $page .= '<li class="class_page_num more_page" url="'.$url.''.$prv5_page.'" >...</li>';
        }
        for($i=$start_page;$i<=$end_page;$i++)
        {
            if($i==$this->cur_page){
                $page .= '<li class="class_page_num active" url="'.$url.''.$i.'" >'.$i.'</li>';
            }
            else{
                $page .= '<li class="class_page_num" url="'.$url.''.$i.'">'.$i.'</li>';
            }
        }
        if($this->cur_page<$this->total_page && $this->cur_page != $this->total_page && $this->total_page > 5)
        {
          if ($this->cur_page+5 <= $this->total_page) {
            $next5_page = $this->cur_page+5;
          } else {
            $next5_page = $this->total_page;
          }
            $page .= '<li class="class_page_num more_page" url="'.$url.''.$next5_page.'">...</li>';
            $page .= '<li class="class_page_num" url="'.$url.''.$this->total_page.'">'.$this->total_page.'</li>';
        }
        if($this->cur_page+1<=$this->total_page)
        {
            $next_page = $this->cur_page + 1;
            $page .= '<li id="next_page" class="class_page_num" url="'.$url.''.$next_page.'"></li>';
        }

		$page .= '<li class="totalPage"><span  id="page_nation" url="'.$url.'" >';
		$page .= '<input type="tel" id="sub_page_input" autocomplete="off" name="p" value="'.$this->cur_page.'" > 页 ';
		$page .= '<input type="button" class="class_page_num" id="page_submit" style="line-height:18px;border:none;" value="GO">';
		$page .= '</span></li>';

		$page .= '</ul></div>';
        return $page;
    }
 }
