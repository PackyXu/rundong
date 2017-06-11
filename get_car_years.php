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
	
	$brand_id =$_GET['brand_id'];
	$brand_id =intval($brand_id);
	
	//查询车辆年款
	$style_id =$_GET['style_id'];
	$style_id =intval($style_id);
	
	if($brand_id==''){
		$arr['error_code'] ="101";
		$arr['error_desc'] ="缺少参数";
	}
	else if($brand_id==''){
		$arr['error_code'] ="101";
		$arr['style_id'] ="缺少参数";
	}else{
		$str_query_brand = "select brand_name from tb_car_brand where brand_id={$brand_id} and enable_state=1";
		$db_result_brand = mysql_query($str_query_brand, $dbconn);
		$row_brand = mysql_fetch_array($db_result_brand);
		
		$str_query_style= "select style_name from tb_car_style where style_id={$style_id} and enable_state=1 ";
		$db_result_style = mysql_query($str_query_style, $dbconn);
		$row_style = mysql_fetch_array($db_result_style);
		
		$str_query ="select * from tb_car_year where style_id={$style_id} and enable_state =1 ";
		$db_result = mysql_query($str_query, $dbconn);
		
		if($db_result && $row_brand && $row_style) {
			while($row = mysql_fetch_array($db_result)) {
				$car_year =array();
				$car_year['year_id']=$row['year_id'];
				$car_year['brand_id']=$row['brand_id'];
				$car_year['style_id']=$row['style_id'];
				$car_year['year_name']=$row['year_name'];
				$car_year['car_no'] =$row['car_no'];
				$car_year['year'] =$row['year'];
				$car_year['volume']=$row['volume'];
				$car_year['tap']=$row['tap'];
				$car_year['speed_box']=$row['speed_box'];
				$car_year['gas']=$row['gas'];
				$car_year['text_name']=$row_brand[false].$row_style[false].$row['year_name'];
				$car_year['url']='index.html?brand_id='.$brand_id.'&style_id='.$style_id.'&year_id='.$row['year_id'].'&text_name='.urlencode($car_year['text_name']).'&car_no='.$car_year['car_no'];
				array_push($arr['data'], $car_year);
			}
			
		}else{
			$arr['error_code'] ="102";
			$arr['error_desc'] ="未查询到信息";
		}
	}
	
	//var_dump($arr);die;
	//输出结果
	echo json_encode($arr);

	//最后关闭数据库连接
	mysql_close($dbconn);

?>

