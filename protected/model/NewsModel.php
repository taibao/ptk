<?php
    //模型
    class NewsModel{
        public function select(){
            $link = new PDO('mysql:host=localhost;dbname=ptk','root','');//成功返回pdo对象
            $link->query('set names utf-8');
            $query = $link->query('select * from news order by id desc');
            $result = $query->fetchAll(PDO::FETCH_CLASS);
            return $result;
        }
        // public function del




    }