<?php
$db_server="121.43.147.27";
$db_username="butlertest";
$db_userpwd="butlertest";

$dbconn =mysql_connect($db_server,$db_username,$db_userpwd) or die("Could not connect mysql");
mysql_set_charset('utf8', $dbconn); 

unset($db_server,$db_username,$db_userpwd);