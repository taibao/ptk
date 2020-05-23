<?php
    //模型
    class TblAreasModel extends Model{
        public function before_insert($data){
            return $data;
        }
        public function after_insert($data){
            $id = $this->db->getLastId();
            return $id;
        }

        #组装数据（具体操作）
        #@param $data 查询得到的结果集
        public function _setup($data){
            // $data->edit_url = $this->url->siteUrl('admin/news/add/id/'.$data->id);
            // $data->del_url = $this->url->siteUrl('admin/news/del/id/'.$data->id);
            // return $data;
        }

        /*
        *得到地址
        *@param $param 入口文件的参数
        */
        // public function siteUrl($param = ''){
        //  $url = PTK::$config['url']['base_url'].PTK::$config['url']['app'].'/'.$param;
        //  return $url;
        // }


        #找出该编号以上的所有区域
        public function getchildnum($parent_id,$pre_result,$level=0){
        		if($parent_id){
        				$map['areas_id']=$parent_id;
        				$data = $this->findByMap("areas_id,name,parent_id",$map);
        				$pre_result[]=$data;
        				$parent_id=$data['parent_id'];
        				$pre_result=$this->getchildnum($parent_id,$pre_result,$level+1);
        		}
        		return $pre_result;
        }

        #找出该编号以下的所有区域
        public function getchildnum2($id,$child_result,$level=1){
            $temp = $id;
            $sub_result = $this->info("*",array('parent_id'=>$id));
            foreach ($sub_result as $k => $v) {
                if($temp==$v['parent_id']){
                    $id = $v['areas_id'];
                    $v['level'] = $level;
                    $child_result[] = $v;
                    $child_result = $this->getchildnum2($id,$child_result,$level+1);
                }
            }
            return $child_result;
        }

    }
