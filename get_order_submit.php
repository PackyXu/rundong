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

	$mobile = $_POST['mobile'];//联系方式
	$contact = $_POST['contact'];//联系人
	$address = $_POST['address'];//取车地址
	$date = $_POST['date'];//预约日期
	$time = $_POST['time'];//预约时段
	$fangshi = $_POST['fangshi'];//是否有取车费
	
	$carIdent = $_SESSION['carIdent'];//车辆标识码
	$total_price = $_SESSION['total_price'];//价格
	$items = $_SESSION['items'];//配件信息json数组 

	$time=$date.$time;
	if($fangshi!=''){
		$total_price = $total_price+$fangshi;//价格
	}
	$rand = rand(10000,99999);
	$orderNum = substr(date(Ymd),2).$rand.substr(date(His),2);
	//echo $orderNum;
	if($total_price=='' ||$items=='' ||$mobile=='' ||$contact=='' ){
		$arr['error_code'] ="101";
		$arr['error_desc'] ="缺少参数";

	}else{
		$time=time();
		$str_query = "INSERT INTO tb_user_order(order_no, car_no, create_time,link_username,link_address,link_phone,order_price_0,order_price_1,into_time)VALUES ('{$orderNum}','{$carIdent}','{$time}','{$contact}','{$mobile}','{$address}','{$total_price}','{$total_price}','{$time}')"; 
		$result=mysql_query($str_query, $dbconn);
		$order_id = mysql_insert_id();
		
		if($result){
			if(!empty($items)){
				foreach($items as $it){
					$str_sql ="select service_type_name from tb_service_type where service_type_id={$it['serevice_type_id']} ";
					$db_result = mysql_query($str_sql,$dbconn);
					$row =  mysql_fetch_assoc($db_result);
					$service_type_name=$row['service_type_name'];
				
					$sql = "INSERT INTO tb_user_order_item(order_id,order_no,service_item_id,service_item_name,car_part_id,car_part_name,service_type_id, service_type_name,sell_price) VALUES ('{$order_id}','{$orderNum}','{$it['item_id']}','{$it['item_name']}','{$it['part_id']}','{$it['part_name']}','{$it['serevice_type_id']}','{$service_type_name}','{$it['in_price']}')";
					
					$results = mysql_query($sql,$dbconn);
				}
				//删除session
				unset($_SESSION['items']);
				unset($_SESSION['total_price']);
			}
			$arr['data']['orderNum']=$orderNum;
			$arr['data']['total_price']=$total_price;
	
		} else {
			$arr['error_code'] ="102";
			$arr['error_desc'] ="未查询到信息";
		}
	}
	//输出结果
	echo json_encode($arr);
	
	//print_r($arr);die;
	//最后关闭数据库连接
	mysql_close($dbconn);
	
	
	

?>

