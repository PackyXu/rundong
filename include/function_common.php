<?php

define('EARTH_RADIUS', 6371);//地球半径，平均半径为6371km

function alert($str){
	echo("<script language=javascript>alert('$str');history.go(-1);</script>");
	exit;
}

/*****************************************************************
 *计算某个经纬度的周围某段距离的正方形的四个点
 *
 *@param lng float 经度
 *@param lat float 纬度
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 *@return array 正方形的四个点的经纬度坐标
****************************************************************/
 function return_SquarePoint($lng, $lat,$distance = 0.5)
 {
    $dlng =  2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
     
    $dlat = $distance/EARTH_RADIUS;
    $dlat = rad2deg($dlat);
     
    return array(
                'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
                'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
                'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
                'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
                );
 }
 
 
/*****************************************************************
*求两个已知经纬度之间的距离,单位为千米
*@param lng1,lng2 经度
*@param lat1,lat2 纬度
*@return float 距离，单位千米
****************************************************************/
function return_Distance($lng1,$lat1,$lng2,$lat2)//根据经纬度计算距离
{
	//将角度转为弧度
	$radLat1=deg2rad($lat1);
	$radLat2=deg2rad($lat2);
	$radLng1=deg2rad($lng1);
	$radLng2=deg2rad($lng2);
	$a=$radLat1-$radLat2;//两纬度之差,纬度<90
	$b=$radLng1-$radLng2;//两经度之差纬度<180
	$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
	return $s;
}
 

/*****************************************************************
获取用户车辆的保养项目和默认配件(价格)
传入参数: 
$user_id 用户id, 
$car_id 车辆id, 
$car_yearid 年款id,  
$carIdent 车辆编号
$buy_time 购买时间, 
$milemeter 里程数, 
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["main_items"],$arr["item_parts"]
****************************************************************/
function get_usercar_byxm($user_id,$car_id,$car_yearid,$carIdent,$buy_time,$milemeter,$dblink)
{
	$arr["error_code"]="0";
	$arr["main_items"]=array();
	$arr["item_parts"]=array();
	//先通过年款id查询汽车编码
	if(strlen($car_yearid) > 0) {
		$car_yearid = intval($car_yearid);  //安全检查
		$str_query ="select carNo from lee_cdd_car_year where id={$car_yearid}";
		$db_result = mysql_query($str_query, $dblink);
		if($db_result && $row = mysql_free_result($db_result)) {
			$carIdent =$row['carNo'];
		}
	}
	//再通过用户id和车辆id查询汽车编码
	if(strlen($carIdent)<=0) {
		$user_id =intval($user_id);  //安全检查
		$car_id =intval($car_id);    //安全检查
		$str_query ="select carIdentifier from lee_cdd_car where userId={$user_id} and id={$car_id}";
		$db_result=mysql_query($str_query, $dblink);
		if($row = mysql_fetch_array($db_result)) {
			$carIdent=$row['carIdentifier'];
		}
	}
	//没有找到对应车型
	if(strlen($carIdent)<=0) {
		$arr["error_code"]="101";   //没有找到对应车型
		return;
	}
	//var_dump($carIdent);
	//获取保养手册 (从表 lee_cdd_maintenance_manual.item where carIdentifier)
	$car_main_item ="";
	$str_query ="select item from lee_cdd_maintenance_manual where carIdentifier='{$carIdent}'";
	$db_result=mysql_query($str_query, $dblink);
	if($db_result && $row = mysql_fetch_array($db_result)) {
		$car_main_item = $row['item'];
		//进行json_decode,数据库里面字段存放的是json编码数据
		$car_main_array=json_decode($car_main_item, true);
		//检查里程数,根据传入的里程数获取位置
		//var_dump($car_main_array);
		$main_index=0;
		$milemeter = intval($milemeter);
		if($milemeter <=0) $milemeter =0;
	    $temp_indexs =array();
		foreach($car_main_array as $temp_id => $temp_items)
		{
			array_push($temp_indexs, intval($temp_id));
		}
		//var_dump("================= main_index array =============\r\n");
		//var_dump($temp_indexs);
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
		
		//echo "=================select main_index =============\r\n";
		//echo "delta_left:{$delta_left}"."  "."delta_right:{$delta_right}"."   "."main_index:{$main_index}";
		//echo "\r\n";
		//获取对应里程数的保养项目列表
        //var_dump($car_main_array[$temp_indexs[$main_index]]);
		$temp_arr = $car_main_array[$temp_indexs[$main_index]];
		//var_dump($temp_arr);
		//echo "=================for each =============\r\n";
		for($i=0; $i<count($temp_arr);$i++)
		{
			$item_id =$temp_arr[$i]['i'];
			$item_state =$temp_arr[$i]['s'];
			$str_query ="select id,name from lee_cdd_maintenance_item where id={$item_id}";
			$item_name ="";   //默认项目名称为空
			$db_result = mysql_query($str_query,$dblink);
			if($db_result && $row = mysql_fetch_array($db_result)) {
				$item_name = $row['name'];
			}
			//获取保养项目对应的配件列表(最高价格为默认)
			$str_query = "select partsNo,price from  lee_cdd_visit_car_parts_relation where carIdentifier='{$carIdent}' and itemId={$item_id} order by price desc limit 0,1";
			$part_id ="";
			$part_no ="";
			$part_price="";
			$part_name="";
			$db_result = mysql_query($str_query, $dblink);
			if($db_result && $row = mysql_fetch_array($db_result)) {
				$part_no =$row['partsNo'];
				$part_price =$row['price'];
			}
			//如果查到配件编号,由于配件编号可能相同,获取价格最高的配件名称和配件id
			if(strlen($part_no)>0) {
				//var_dump($part_no);
				$str_query = "select id,name,salePrice from lee_cdd_visit_parts where specNo='{$part_no}' order by salePrice desc limit 0,1";
				$db_result = mysql_query($str_query, $dblink);
				if($db_result && $row = mysql_fetch_array($db_result)) {
				   $part_id = $row['id'];
				   $part_name =$row['name'];
				   $part_price =$row['salePrice'];
				}
			}
			$main_item = array();
			$main_item['item_name']=urlencode($item_name);   //保养项目名称
			$main_item['item_id']=$item_id;                  //保养项目id
			$main_item['item_state']=$item_state;            //保养项目状态
			$main_item['part_id']=$part_id;                  //对应配件id
			$main_item['part_no']=$part_no;                  //对应配件编码
			$main_item['part_name']=urlencode($part_name);   //对应配件名称
			$main_item['part_price']=$part_price;            //对应配件价格
			array_push($arr["main_items"], $main_item);
		}
		//echo "=================for each item-parts =============\r\n";
		for($i=0; $i<count($temp_arr);$i++)
		{
			$item_id =intval($temp_arr[$i]['i']);
			$item_parts=array();
			$str_query = "select id,name,specNo,specName,salePrice from lee_cdd_visit_parts where specNo in (select partsNo from lee_cdd_visit_car_parts_relation where carIdentifier='{$carIdent}' and itemId={$item_id}) order by salePrice desc limit 0,20";
			$db_result = mysql_query($str_query, $dblink);
			if($db_result) {
				while($row = mysql_fetch_array($db_result)) {
					$part_info =array();
					$part_info["item_id"] =(string)$item_id;
					$part_info["part_id"] =$row['id'];
					$part_info["part_name"] =urlencode($row['name']);
					$part_info["part_no"] =urlencode($row['specNo']);
					$part_info["part_saleprice"]=$row['salePrice'];
					array_push($item_parts, $part_info);
				}
			}
			$item_id =(string)$item_id;
			$arr['item_parts'][$item_id]=$item_parts;
		}	
	}
	else {
		$arr["error_code"]="102";   //没有找到保养手册
	}
	
	//var_dump($arr);
	return $arr;
}

/*****************************************************************
获取用户车辆保养项目的所有配件列表(按价格高低排序)
传入参数: 
$user_id 用户id, 
$car_id 车辆id, 
$car_yearid 年款id,  
$carIdent 车辆编码
$item_id 保养项目id
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_byxm_parts($user_id,$car_id,$car_yearid,$carIdent,$item_id,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	//如果没有车辆编码，先通过年款id查询汽车编码
	if(strlen($carIdent)==0 && strlen($car_yearid) > 0) {
		$car_yearid = intval($car_yearid);  //安全检查
		$str_query ="select carNo from lee_cdd_car_year where id={$car_yearid}";
		$db_result = mysql_query($str_query, $dblink);
		if($db_result && $row = mysql_free_result($db_result)) {
			$carIdent =$row['carNo'];
		}
	}
	//再通过用户id和车辆id查询汽车编码
	if(strlen($carIdent)<=0) {
		$user_id =intval($user_id);  //安全检查
		$car_id =intval($car_id);    //安全检查
		$str_query ="select carIdentifier from lee_cdd_car where userId={$user_id} and id={$car_id}";
		$db_result=mysql_query($str_query, $dblink);
		if($db_result && $row = mysql_fetch_array($db_result)) {
			$carIdent=$row['carIdentifier'];
		}
	}
	//没有找到对应车型
	if(strlen($carIdent)<=0) {
		$arr["error_code"]="101";   //没有找到对应车型
		return;
	}
	
	//获取保养项目对应的配件列表(最高价格为默认)
	$str_query = "select id,name,specNo,specName,salePrice from lee_cdd_visit_parts where specNo in (select partsNo from lee_cdd_visit_car_parts_relation where carIdentifier='{$carIdent}' and itemId={$item_id}) order by salePrice desc limit 0,20";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$part_info =array();
			$part_info["item_id"] =(string)$item_id;
			$part_info["part_id"] =$row['id'];
			$part_info["part_name"] =urlencode($row['name']);
			$part_info["part_no"] =urlencode($row['specNo']);
			$part_info["part_saleprice"]=$row['salePrice'];
			array_push($arr['data'], $part_info);
		}
	}
	else {
		$arr["error_code"]="102";   //没有找到对应配件列表
	}
	return $arr;
}

/*****************************************************************
获取用户的车辆列表
传入参数: 
$user_id 用户id, 
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_user_cars($user_id,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	$user_id = intval($user_id);
	//用户是否存在
	$str_query ="select * from lee_cdd_userinfo where id={$user_id}";
	$db_result = mysql_query($str_query, $dblink);
	if(!$db_result) {
		$arr['error_code'] ="101";  //用户不存在
		return $arr;
	}
	//查询用户车辆列表
	$str_query = "select * from lee_cdd_car where userId={$user_id} and isDel=0";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$car_info =array();
			$car_info["user_id"] =(string)$user_id;
			$car_info["car_id"] =$row['id'];
			$car_info["car_no"] =urlencode($row['carNo']);
			$car_info["car_name"] =urlencode($row['carName']);
			$car_info['buy_date']=urlencode($row['buyDate']);
			$car_info['milemeter']=$row['milemeter'];
			$car_info['is_main']=$row['isMain'];
			$car_info['brand_id']=$row['brandId'];
			$car_info['fid']=$row['fid'];
			$car_info['brand_img']=$row['carBrandImg'];
			$car_info['car_identifier']=$row['carIdentifier'];
			$car_info['car_year']=$row['year'];
			$car_info['car_styleid']=$row['style_id'];
			$car_info['car_yearid']=$row['year_id'];
			array_push($arr['data'], $car_info);
		}
	}
	else {
		$arr["error_code"]="102";  //没有找到对应用户车辆
	}
	return $arr;
}

/*****************************************************************
获取车辆品牌列表
传入参数: 
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_brands($dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
	//查询状态为可用的品牌列表
	$str_query = "select * from lee_cdd_car_brand where state=1 order by letter asc";
	$db_result = mysql_query($str_query, $dblink);
	$count =0;
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$car_brand=array();
			$car_brand['brand_id']=$row['id'];
			$car_brand['brand_name']=urlencode($row['name']);
			$car_brand['letter']=urlencode($row['letter']);
			$car_brand['brand_img']=urlencode($row['image']);
			$car_brand['brand_state']=$row['state'];
			array_push($arr['data'], $car_brand);
			$count++;
		}
	}
	if($count == 0) {
		$arr['error_code'] ="101";  //没有找到品牌列表
	}
	return $arr;
}

/**************************************************************
获取车辆型号列表
传入参数:
$car_brandid  车辆品牌id
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_styles($car_brandid,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
	//查询车辆型号
	$car_brandid=intval($car_brandid);
	$str_query = "select * from lee_cdd_car_style where brandId={$car_brandid} and state=1 order by id";
	//var_dump($str_query);
	$db_result = mysql_query($str_query, $dblink);
	$count =0;
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$car_style=array();
			$car_style['style_id'] =$row['id'];
			$car_style['brand_id'] =$row['brand_id'];
			$car_style['style_name']=urlencode($row['name']);
			$car_style['type_name']=urlencode($row['type']);
			$car_style['fid']=$row['fid'];
			$car_style['style_state']=$row['state'];
			$car_style['style_size']=$row['size'];
			array_push($arr['data'], $car_style);
			$count++;
		}
	}
    
	if($count == 0) {
		$arr['error_code'] ="101";  //没有找到型号列表
	}
	return $arr;
}

/**************************************************************
获取车辆年款列表
传入参数:
$car_styleid  车辆style_id
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_years($car_styleid,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();

	//查询车辆年款
	$car_styleid =intval($car_styleid);
	$str_query ="select * from lee_cdd_car_year where styleId={$car_styleid} and state =1 order by id asc";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$car_year =array();
			$car_year['year_id']=$row['id'];
			$car_year['brand_id']=$row['brandId'];
			$car_year['fid']=$row['fid'];
			$car_year['style_id']=$row['styleId'];
			$car_year['year_name']=urlencode($row['name']);
			$car_year['car_no'] =urlencode($row['carNo']);
			$car_year['year'] =$row['year'];
			$car_year['volume']=$row['volume'];
			$car_year['tap']=$row['tap'];
			$car_year['speedbox']=urlencode($row['speedbox']);
			$car_year['gas']=urlencode($row['gas']);
			$car_year['test']=urlencode($row['test']);
			$car_year['year_state']=$row['state'];
			array_push($arr['data'], $car_year);
		}
	}
	else {
		$arr['error_code'] ="101";  //没有找到年款列表
	}
	return $arr;
}		
			
/**************************************************************
获取洗车美容的通用项目列表
传入参数:
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_proj101($dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
	//查询项目列表
	$str_query = "select * from lee_cdd_visit_main_item a where store_type=0 and projtype=101 order by a.order";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$item_xcmr =array();
			$item_xcmr['proj_id']=$row['id'];
			$item_xcmr['proj_name']=urlencode($row['itemName']);
			$item_xcmr['price'] =$row['price'];
			$item_xcmr['proj_type']=$row['projtype'];
			$item_xcmr['havepart']=$row['havepart'];
			$item_xcmr['id2']=$row['id2'];
			$item_xcmr['store_type']=$row['store_type'];
			$item_xcmr['store_id']=$row['store_id'];
			$item_xcmr['city_id']=$row['city_id'];
			array_push($arr['data'], $item_xcmr);
		}
	}
	else {
		$arr['error_code'] ="101";  //没有找到洗车美容列表
	}
	return $arr;
}
			
/**************************************************************
获取洗车美容的商户项目列表
传入参数:
$store_id  门店id
$db_link   数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_proj101_storeid($store_id,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
	//查询项目列表(lee_cdd_visit_main_item)
	//TODO:根据门店项目价格对应表查询门店价格
	$str_query = "select * from lee_cdd_visit_main_item a where (store_type=0 or store_id={$store_id}) and projtype=101 order by a.order";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$item_xcmr =array();
			$item_xcmr['proj_id']=$row['id'];
			$item_xcmr['proj_name']=urlencode($row['itemName']);
			$item_xcmr['price'] =$row['price'];
			$item_xcmr['proj_type']=$row['projtype'];
			$item_xcmr['havepart']=$row['havepart'];
			$item_xcmr['id2']=$row['id2'];
			$item_xcmr['store_type']=$row['store_type'];
			$item_xcmr['store_id']=$row['store_id'];
			$item_xcmr['city_id']=$row['city_id'];
			array_push($arr['data'], $item_xcmr);
		}
	}
	else {
		$arr['error_code'] ="101";  //没有找到洗车美容列表
	}
	return $arr;
}
			

/**************************************************************
获取洗车美容的城市项目列表
传入参数:
$city_id   城市id
$city_name 城市名称
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_car_proj101_city($city_id,$city_name,$dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
	//查询项目列表(lee_cdd_visit_main_item)
	//TODO:根据城市对应表获取城市价格
	$str_query = "select * from lee_cdd_visit_main_item a where (store_type=0 or store_id={$store_id}) and projtype=101 order by a.order";
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$item_xcmr =array();
			$item_xcmr['proj_id']=$row['id'];
			$item_xcmr['proj_name']=urlencode($row['itemName']);
			$item_xcmr['price'] =$row['price'];
			$item_xcmr['proj_type']=$row['projtype'];
			$item_xcmr['havepart']=$row['havepart'];
			$item_xcmr['id2']=$row['id2'];
			$item_xcmr['store_type']=$row['store_type'];
			$item_xcmr['store_id']=$row['store_id'];
			$item_xcmr['city_id']=$row['city_id'];
			array_push($arr['data'], $item_xcmr);
		}
	}
	else {
		$arr['error_code'] ="101";  //没有找到洗车美容列表
	}
	return $arr;
}			

/**************************************************************
获取门店列表列表
传入参数:
$city_id   城市id
$city_name 城市名称
$region_name 区名称
$lat   经度
$lng   纬度
$projtype 项目id
$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/
function get_storeshops($city_id, $city_name, $region_name, $lat, $lng, $projtype, $dblink)
{
	$arr["error_code"]="0";
	$arr["data"]=array();
	
    //使用此函数计算得到结果后，带入sql查询。
	$str_query ="";
	$str_query .="select * from lee_cdd_b_store where (1=1) ";
	//城市
	if(strlen($city_name) >0 && strlen($region_name) >0) {
		$str_filter =$city_name.$region_name;
		$str_query .=" and (area like '{$str_filter}%') ";
	}
	else if(strlen($city_name) >0) {
		$str_filter =$city_name;
		$str_query .=" and (area like '{$str_filter}%') ";
	}
	else if(strlen($city_id) > 0) {
		$city_id = intval($city_id);
		$str_query .=" and (cityId={$city_id}) ";
	}
	//项目ID
	if(strlen($projtype) >0) {
		$str_query .=" and (projectstr like '%{$projtype}%') ";
	}
	//坐标排序 或ID 排序
	if(strlen($lat) >0 && strlen($lng) >0) {
        $squares = return_SquarePoint($lng, $lat, 200);
	    $str_query .=" and (lat<>0 and lat>{$squares['right-bottom']['lat']} and lat<{$squares['left-top']['lat']} and lng>{$squares['left-top']['lng']} and lng<{$squares['right-bottom']['lng']}) ";
		$str_query .=" order by ACOS(SIN(({$lat} * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(({$lat} * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(({$lng} * 3.1415) / 180 - (lng * 3.1415) / 180 ))*6380 asc limit 200";
	}
	else {
		$str_query .=" order by id asc limit 200";
	}
	var_dump($str_query);
	$db_result = mysql_query($str_query, $dblink);
	if($db_result) {
		while($row = mysql_fetch_array($db_result)) {
			$store_item =array();
			$store_item['store_id'] =$row['id'];
			$store_item['area']=urlencode($row['area']);
			$store_item['store_name']=urlencode($row['name']);
			$store_item['address']=urlencode($row['address']);
			$store_item['cityid']=$row['cityId'];
			$store_item['lat']=$row['lat'];
			$store_item['lng']=$row['lng'];
			$store_item['projectstr']=urlencode($row['projectstr']);
			$store_item['distance']="-1";
			if(strlen($lat)>0 && strlen($lng)> 0 && strlen($store_item['lat'])>0 && strlen($store_item['lng']) >0) {
				$store_item['distance'] = return_Distance($lng,$lat,$store_item['lng'],$store_item['lat']);
			}
			array_push($arr['data'], $store_item);
		}
	}
	else {
		$arr['error_code'] ="101";  //没有找到门店列表
	}
	return $arr;
}			
			
/**************************************************************
获取车辆可用喷漆类型列表(含喷漆面价格列表)
传入参数:

$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/


/**************************************************************
获取车辆可用喷漆面列表
传入参数:

$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/

/**************************************************************
获取用户订单列表
传入参数:

$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/

/**************************************************************
获取门店订单列表
传入参数:

$db_link 数据库连接
返回:
查询结果数据 $arr["err_code"],$arr["data"]
****************************************************************/


//HTTP请求（支持HTTP/HTTPS，支持GET/POST）
function http_request($url, $postkey, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
		//var_dump("use post field");
        curl_setopt($curl, CURLOPT_POST, 1);
		if(strlen($postkey) >0) {
			//var_dump("have postkey");
		    $fields_string = $postkey."=".$data;
			//var_dump($fields_string);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		}
		else {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/*
function test_http_post($url)
{
	$fields = array(
	'lname' => urlencode($_POST['last_name']),
	'fname' => urlencode($_POST['first_name']),
	'title' => urlencode($_POST['title']),
	'company' => urlencode($_POST['institution']),
	'age' => urlencode($_POST['age']),
	'email' => urlencode($_POST['email']),
	'phone' => urlencode($_POST['phone'])
    );
	//url-ify the data for the POST
    foreach($fields as $key=>$value) 
	{ $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	$result = curl_exec($ch);
    curl_close($ch);
	return $result;
}
*/



?>
