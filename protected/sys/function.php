<?php
	/*二分查找算法*/
	function btsearch($max,$min)
	{
		while($min<=$max) //或for(;$min<=$max;)
		{
			$i = (Int)($min + $max)/2;
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
	/*快速排序算法*/
	function Q_sort($arr=array())
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
		$left_array = Q_sort($left_array);
		$right_array = Q_sort($right_array);
    	return array_merge($left_array,array($key),$right_array);
	}

	//冒泡排序
	function pao($arr)
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
	function select($arr){
		for($i=0,$len=count($arr);$i<$len-1;$i++)
		{
			$p = $i;//假定一个最小值的位置
			for($j=$i+1;$j<$len;$j++)
			{
				if($arr[$p]>$arr[$j])
				{
					$p = $j;
				}
			}
			//交换值
			if($p!=$i)
			{
				$temp = $arr[$p];
				$arr[$p] = $arr[$i];
				$arr[$i] = $temp;
			}
		}
		return $arr;
	}
	
	//插入选择排序
	function insert($arr){
		$len = count($arr);
		for($i=1;$i<$len;$i++)
		{
			$tmp = $arr[$i];
			for($j=$i-1;$j>=0;$j--)
			{
				if($tmp<$arr[$j])
				{
					$arr[$j+1] = $arr[$j]; //直接用$arr[$j]赋值
					$arr[$j] = $tmp;
				}
				else
				{
					break;
				}
			}
		}
		return $arr;
	}
	
	
	