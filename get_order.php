<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header('Access-Control-Allow-Origin:*');
header ( 'Content-type: text/html; charset=utf-8' );
include("include/mysql_conn.php");
session_start(); 
	//连接数据库
	mysql_select_db("rundong", $dbconn);
	$arr["error_code"]="100";
	$arr["error_desc"]="";
	$arr["data"]=array();
	//查询车辆年款
	$service_item_id =$_GET['items'];
	$carIdent =$_GET['carIdent'];
	$milemeter =$_GET['milemeter'];
	/*
	$service_item_id ='1,2,4';
	$carIdent ='ELH0520A0002';
	$milemeter ='10000';
	*/
	$service_item_id=explode(',',$service_item_id);
	
	if($milemeter==''){
		$arr['error_code'] ="101";
		$arr['error_desc'] ="缺少参数";
		$_SESSION['milemeter']='';
		$_SESSION['carIdent']='';
		$_SESSION['items']='';
		$_SESSION['total_price']='0';
	}
	else if($carIdent==''){
		$arr['error_code'] ="101";
		$arr['style_id'] ="缺少参数";
		$_SESSION['milemeter']='';
		$_SESSION['carIdent']='';
		$_SESSION['items']='';
		$_SESSION['total_price']='0';
	}else{
		foreach ( $service_item_id as $key=> $item ) {
			$str_sql ="select service_item_id,service_item_name,serevice_type_id from tb_service_item where service_item_id={$item} ";
			$db_result = mysql_query($str_sql,$dbconn);
			$items=array();
			if($db_result) {
				while($row = mysql_fetch_array($db_result)) {
					$item_info =array();
					$item_info["service_item_id"] =$item;
					$item_info["service_item_name"] =$row['service_item_name'];
					$item_info["serevice_type_id"] =$row['serevice_type_id'];
					array_push($items, $item_info);
				}
			}else{
				$arr['error_code'] ="102";
				$arr['error_desc'] ="未查询到信息";
			}
			$item_parts=array();
			$sql_parts = "select r.service_item_id,p.in_price,p.part_name,p.part_no,p.part_id from tb_car_part_service_item r LEFT JOIN tb_car_part p ON r.part_id = p.part_id WHERE r.service_item_id = {$item} AND r.car_no = '{$carIdent}'";
			$db_parts = mysql_query($sql_parts,$dbconn);
			if($db_parts) {
				while($row = mysql_fetch_array($db_parts)) {
					$part_info =array();
					$part_info["service_item_id"] =$item;
					$part_info["part_id"] =$row['part_id'];
					$part_info["part_name"] =$row['part_name'];
					$part_info["part_no"] =$row['part_no'];
					$part_info["in_price"]=$row['in_price'];
					array_push($item_parts, $part_info);
				}
			}
			$arr['data']['items'][$key]['item_id']=$items[0]['service_item_id'];
			$arr['data']['items'][$key]['item_name']=$items[0]['service_item_name'];
			$arr['data']['items'][$key]['serevice_type_id']=$items[0]['serevice_type_id'];
			$arr['data']['items'][$key]['part_id']=$item_parts[0]['part_id'];
			$arr['data']['items'][$key]['part_name']=$item_parts[0]['part_name'];
			$arr['data']['items'][$key]['in_price']=$item_parts[0]['in_price'];
			$arr['data']['total_price']+= $item_parts[0]['in_price'];
		}
		$twoDays = time();
		$time = array();
		for($i=2;$i<7;$i++){
			$time[] .= date('Y-m-d',$twoDays+3600*24*$i);
		}
		$arr['data']['time']=$time;
		$arr['data']['milemeter']=$milemeter;
		$arr['data']['carIdent']=$carIdent;
		$_SESSION['milemeter']=$milemeter;
		$_SESSION['carIdent']=$carIdent;
		$_SESSION['items']=$arr['data']['items'];
		$_SESSION['total_price']=$arr['data']['total_price'];
	}
	
	//var_dump($_SESSION) ;
	//var_dump($arr);die;
	//print_r($arr);die;
	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

