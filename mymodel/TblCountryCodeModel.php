<?php
    //模型
    class TblCountryCodeModel extends Model{
        public function before_insert($data){
            return $data;
        }

        public function after_insert($data){
            $id = $this->db->getLastId();
            return $id;
        }

        public function getcountrycode($id)
        {
          $map['id'] = $id;
          $data = $this->findByMap('*',$map);
          return $data['name'];
        }

    }
