<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header('Access-Control-Allow-Origin:*');
header ( 'Content-type: text/html; charset=utf-8' );
include("include/mysql_conn.php");

	//连接数据库
	mysql_select_db("rundong", $dbconn);

	$arr["data"]=array();

	//查询车辆年款
	$brand_id =$_GET['brand_id'];
	$brand_id =intval($brand_id);
	
	$style_id =$_GET['style_id'];
	$style_id =intval($style_id);
	
	$year_id =$_GET['year_id'];
	$year_id =intval($year_id);
	
	$str_query_brand = "select brand_name from tb_car_brand where brand_id={$brand_id} and enable_state=1";
	$db_result_brand = mysql_query($str_query_brand, $dbconn);
	$row_brand = mysql_fetch_array($db_result_brand);
	
	$str_query_style= "select style_name from tb_car_style where style_id={$style_id} and enable_state=1 ";
	$db_result_style = mysql_query($str_query_style, $dbconn);
	$row_style = mysql_fetch_array($db_result_style);
	
	$str_query_year ="select year_name from tb_car_year where year_id={$year_id} and enable_state =1 ";
	$db_result_year = mysql_query($str_query_year, $dbconn);
	$row_year = mysql_fetch_array($db_result_year);
	
	if($row_brand && $row_style && $row_year) {

		$car =array();
		$car['brand_name']=$row_brand[false];
		$car['style_name']=$row_style[false];
		$car['year_name']=$row_year[false];
		array_push($arr['data'], $car);
		
	}
	/*var_dump($row_brand);
	var_dump($row_style);
	var_dump($row_year);
	var_dump($car);die;*/
	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

