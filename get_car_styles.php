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
	$arr['user_cid'] = $_SESSION["user_cid"];
	$arr["data"]=array();

	//查询车辆型号
	$brand_id =$_GET['brand_id'];
	$brand_id=intval($brand_id);
	if($brand_id==''){
		$arr['error_code'] ="101";
		$arr['error_desc'] ="缺少参数";
		$arr['user_cid'] = $_SESSION["user_cid"];
	}else{
		$str_query = "select * from tb_car_style where brand_id={$brand_id} and enable_state=1 ";
		//var_dump($str_query);
		$db_result = mysql_query($str_query, $dbconn);
		if($db_result) {
			while($row = mysql_fetch_array($db_result)) {
				$car_style=array();
				$car_style['style_id'] =$row['style_id'];
				$car_style['brand_id'] =$row['brand_id'];
				$car_style['style_name']=$row['style_name'];
				$car_style['type_name']=$row['type'];
				$car_style['style_state']=$row['enable_state'];
				$car_style['style_size']=$row['car_size'];
				$car_style['fid']=$row['fid'];
				array_push($arr['data'], $car_style);
				
			}
			 foreach ($arr['data'] as $key=>$info) {
                $style[$info['fid']][] = $arr['data'][$key];
            }
			$style=array_values($style);
			$arr['data']=$style;
		}else{
			$arr['error_code'] ="102";
			$arr['error_desc'] ="未查询到信息";
			$arr['user_cid'] = $_SESSION["user_cid"];
		}
	}
	
	//print_r($arr);die;
	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

