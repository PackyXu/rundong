<?php
$db_server="172.16.4.7";
$db_username="root";
$db_userpwd="";

$dbconn =mysql_connect($db_server,$db_username,$db_userpwd) or die("Could not connect mysql");
mysql_set_charset('utf8', $dbconn); 

unset($db_server,$db_username,$db_userpwd);