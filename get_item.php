<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header('Access-Control-Allow-Origin:*');
header ( 'Content-type: text/html; charset=utf-8' );
include("include/mysql_conn.php");

	//连接数据库
	mysql_select_db("rundong", $dbconn);
	$arr["error_code"]="100";
	$arr["error_desc"]="";
	$arr["data"]=array();
	//查询车辆年款
	$style_id =$_GET['style_id'];
	//接收值
	$milemeter =$_GET['milemeter'];
	$carIdent =$_GET['carIdent'];
	//$carIdent = 'EJB0330A0001';
	//$milemeter = '20000';
	if($milemeter==''){
	$arr['error_code'] ="101";
	$arr['error_desc'] ="缺少参数";
	}
	else if($carIdent==''){
		$arr['error_code'] ="101";
		$arr['style_id'] ="缺少参数";
	}else{
		$car_main_item ="";
		$str_query ="select car_manual from tb_car_maint_manual where car_no='{$carIdent}'";
		$db_result=mysql_query($str_query, $dbconn);
		//var_dump($db_result);
		if($db_result && $row = mysql_fetch_array($db_result)) {
			$car_main_item = $row['car_manual'];
			//进行json_decode,数据库里面字段存放的是json编码数据
			$car_main_array=json_decode($car_main_item, true);
			//检查里程数,根据传入的里程数获取位置
			//var_dump($car_main_array);
			$main_index=0;
			$milemeter = intval($milemeter);
			if($milemeter <=10000) $milemeter =10000;
			$temp_indexs =array();
			foreach($car_main_array as $temp_id => $temp_items)
			{
				array_push($temp_indexs, intval($temp_id));
			}

			$delta_left =0;
			$delta_right = 0;
			$bfound = false;
			for($i=0; $i<count($temp_indexs); $i++) {
				if($milemeter <= $temp_indexs[$i]) {
					$delta_left =0;
					if($i>0)
						$delta_left = $milemeter - $temp_indexs[$i-1];
					$delta_right =$temp_indexs[$i] -$milemeter;
					
					if($delta_left <$delta_right)
						$main_index =$i-1;
					else
						$main_index =$i;
					$bfound = true;
					break;
				}
			}
			if(!$bfound) {
				$main_index = count($temp_indexs) -1;
			}
			$temp_arr = $car_main_array[$temp_indexs[$main_index]];
			
			for($i=0; $i<count($temp_arr);$i++)
			{
				$item_id =intval($temp_arr[$i]['i']);
				$items=array();
				$str_sql ="select service_item_id,service_item_name from tb_service_item where service_item_id={$item_id} ";
				$db_result = mysql_query($str_sql,$dbconn);
				if($db_result) {
					while($row = mysql_fetch_array($db_result)) {
						$item_info =array();
						$item_info["service_item_id"] =$item_id;
						$item_info["service_item_name"] =$row['service_item_name'];
						array_push($items, $item_info);
					}
				}

				$item_parts=array();
				$sql_parts = "select r.service_item_id,p.in_price,p.part_name,p.part_no,p.part_id from tb_car_part_service_item r LEFT JOIN tb_car_part p ON r.part_id = p.part_id WHERE r.service_item_id = {$item_id} AND r.car_no = '{$carIdent}'";
				$db_parts = mysql_query($sql_parts,$dbconn);
				if($db_parts) {
					while($row = mysql_fetch_array($db_parts)) {
						$part_info =array();
						$part_info["service_item_id"] =$item_id;
						$part_info["part_id"] =$row['part_id'];
						$part_info["part_name"] =$row['part_name'];
						$part_info["part_no"] =$row['part_no'];
						$part_info["in_price"]=$row['in_price'];
						array_push($item_parts, $part_info);
					}
				}
				
				$item_id =(string)$item_id;
				//$arr['item_parts'][$item_id]=$item_parts[0];
				//$arr['items'][$item_id]=$items[0];
				$arr['data'][$i]['item_id']=$items[0]['service_item_id'];
				$arr['data'][$i]['item_name']=$items[0]['service_item_name'];
				$arr['data'][$i]['is_main']=$temp_arr[$i]['s'];
				$arr['data'][$i]['part_info']=$item_parts[0];
			}		
					
		}else{
			$arr['error_code'] ="102";
			$arr['error_desc'] ="未查询到信息";
		}
	}

	//print_r($arr);die;
	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

