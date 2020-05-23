<?php
    //模型
    class TblGatewayWhiteListModel extends Model{
        public function before_insert($data){
            return $data;
        }
        public function after_insert($data){
            $id = $this->db->getLastId();
            return $id;
        }

        public function checkById($id){
          if($id)
          {
            $white_map['id'] = $id;
            if($this->Count($white_map)<=0)
            {
              return false;
            }
          }
          return true;
        }

        public function checkByIdAndMac($id,$mac){
          if($id&&$mac)
          {
            $white_map['id'] = $id;
            $white_map['mac'] = $mac;
            if($this->Count($white_map)<=0)
            {
              return false;
            }
          }else if($id=="")
          {
            return false;
          }
          return true;
        }

        public function setErrorLog($err_arr,$mac,$sn,$context){
          $err_temp=array();
          $err_temp['mac'] = $mac;
          $err_temp['sn'] = $sn;
          $err_temp['reason'] = $context;
          $err_temp['type'] = 'SaaS';
          $err_arr[] = $err_temp;
          return $err_arr;
        }

        public function saveGateway($value){
          unset($white_map);
          $white_map['id'] = $value['id'];
          $this->where($white_map)->save($value);
          unset($onoff_map);
          $onoff_map['white_id'] = $value['id'];
          $OnofflineGatewayModel =  loadModel('TblOnofflineGateway');
          $OnofflineGatewayModel->where($onoff_map)->save($value);
        }

    }
