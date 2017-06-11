<?php
header('Content-type:text/json');
//header("Content-type: text/html; charset=utf-8");
session_cache_limiter('private, must-revalidate');  //为了返回的时候还保留数据
session_start();
?>
