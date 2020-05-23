<?php
    //模型
    class TblOnofflineGatewayModel extends Model{
        public function before_insert($data){
            return $data;
        }

        public function after_insert($data){
            $id = $this->db->getLastId();
            return $id;
        }

        #查找边缘网关
        public function getEdgeGateway()
        {
          global $_W;
          $map['uniacid'] = $_W['uniacid'];
          $map['device_class'] = 'edgegateway';
          $edgelists = $this->info("id,mac",$map);
          return $edgelists;
        }

    }
