<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header('Access-Control-Allow-Origin:*');
header ( 'Content-type: text/html; charset=utf-8' );
include("include/mysql_conn.php");
session_start(); 

$_SESSION["user_cid"] ="xuruiyong";

	//连接数据库
	mysql_select_db("rundong", $dbconn);
	$arr["error_code"]="100";
	$arr["error_desc"]="";
	$arr['user_cid'] = $_SESSION["user_cid"];
	$arr["data"]=array();
    
    

	
	//查询状态为可用的品牌列表
	$str_query = "select * from tb_car_brand where enable_state=1 order by letter asc";
	$db_result = mysql_query($str_query, $dbconn);


	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$car_brand=array();
			$car_brand['brand_id']=$row['brand_id'];
			$car_brand['brand_name']=$row['brand_name'];
			$car_brand['letter']=$row['letter'];
			$car_brand['brand_img']=$row['image'];
			$car_brand['brand_state']=$row['enable_state'];
			array_push($arr['data'], $car_brand);
			
		}
	}else{
		$arr['error_code'] ="102";
		$arr['error_desc'] ="未查询到信息";
		$arr['user_cid'] = $_SESSION["user_cid"];
	}

	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

