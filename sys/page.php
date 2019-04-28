<?php
 class page{

    public $cur_page = '';
    public $num = '';
    public $offset = '';
    public $total = '';

    public function __construct($total,$num='10') {
        $this->total = $total;
        if($_GET['num']){
          $_SESSION['num'] = $_GET['num'];
        }
        $this->num = $_SESSION['num']?$_SESSION['num']:$num;
        $this->cur_page = !empty($_GET['p'])?(int)$_GET['p']:1;
        $this->offset   = $this->num * ($this->cur_page - 1);
    }

    public function getPage($url='',$page_num='5'){
        $this->total_page = ceil($this->total/$this->num); //获取总页数
        //确定当前页
        if($this->cur_page<1){
            $this->cur_page = 1;
        }
        if($this->cur_page>$this->total_page){
            $this->cur_page = $this->total_page;
        }

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

        $page .= '<select class="page_select"  id="select_arrow_down">';
        $page .= '<option';
        if($this->num==10){
          $page.=' selected ';
        }
        $page .= ' value="'.$url.'&num=10"> 10</option>';
        $page .= '<option ';
        if($this->num==20){
          $page.=' selected ';
        }
        $page .=' value="'.$url.'&num=20"> 20</option>';
        $page .= '<option ';
        if($this->num==50){
          $page.=' selected ';
        }
        $page .=' value="'.$url.'&num=50"> 50</option>';
        $page .='</select>&nbsp;<span class="page_line_total">Line</span>';

        // $page .= '<li onclick="javascript:window.location.href=\''.$url.'&p=1\'">首页</li>';
        if($this->cur_page-1>0)
        {
            $prv_page = $this->cur_page - 1;
            $page .= '<li id="prv_page" onclick="javascript:window.location.href=\''.$url.'&p='.$prv_page.'\'"></li>';
        }
        if($this->cur_page-5>0)
        {
            $prv5_page = $this->cur_page-5;
            $page .= '<li class="more_page" onclick="javascript:window.location.href=\''.$url.'&p='.$prv5_page.'\'" >...</li>';
        }
        for($i=$start_page;$i<=$end_page;$i++)
        {
            if($i==$this->cur_page){
                $page .= '<li onclick="javascript:window.location.href=\''.$url.'&p='.$i.'\'" class="active">'.$i.'</li>';
            }
            else{
                $page .= '<li onclick="javascript:window.location.href=\''.$url.'&p='.$i.'\'">'.$i.'</li>';
            }
        }
        if($this->cur_page+1<=$this->total_page)
        {
            $next_page = $this->cur_page + 1;
            $page .= '<li id="next_page" onclick="javascript:window.location.href=\''.$url.'&p='.$next_page.'\'"></li>';
        }
        if($this->cur_page+5<=$this->total_page)
        {
            $next5_page = $this->cur_page+5;
            $page .= '<li class="more_page" onclick="javascript:window.location.href=\''.$url.'&p='.$next5_page.'\'">...</li>';
        }
        // $page .= '<li onclick="javascript:window.location.href=\''.$url.'&p='.$this->total_page.'\'">尾页</li>';

    		$page .= '<li class="totalPage "><form  id="page_nation" action="'.$url.'" method="get">';
    		$page .= '<input type="text" id="page_input" name="p" value="'.$this->cur_page.'" > 页 ';
            $page .= '<input type="hidden" name="c" value='.$_GET['c'].'>';
            $page .= '<input type="hidden" name="a" value='.$_GET['a'].'>';
            $page .= '<input type="hidden" name="eid" value='.$_GET['eid'].'>';
            $page .= '<input type="hidden" name="version_id" value='.$_GET['version_id'].'>';
    		$page .= '<input type="submit" id="page_input_submit" value="GO">';
    		$page .= '</form></li>';

        // $page .= '<li class="totalPage">共'.$this->total_page.'页</li>';
        // $page .= '<li class="totalPage">合计'.$this->total.'条数据</li>';
    		$page .= '</ul></div></div>';
        return $page;
    }
 }
