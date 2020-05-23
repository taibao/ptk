#!/bin/bash
#设置mysql备份目录
folder=/home/hwtest123YUnn/www/wef/api/data/
#数据要保留的天数
days=3
cd $folder
day_upstream=`date -d "$days days ago" +%Y_%m_%d`"_upstream.log"
day_onoffline=`date -d "$days days ago" +%Y_%m_%d`"_onoffline.log"
rm  $day_upstream
rm  $day_onoffline
