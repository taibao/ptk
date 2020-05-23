<?php
    //模型
    class TblSnmpModel extends Model{
        public function before_insert($data){
            return $data;
        }

        public function after_insert($data){
            $id = $this->db->getLastId();
            return $id;
        }

        public function getsnmps()
        {
          global $_W;
          //查找该租户下所有的模版
          $map['uniacid'] = $_W['uniacid'];
          $snmps = $this->order('id asc')->info('id,name,protocol',$map);
          return $snmps;
        }

    }
