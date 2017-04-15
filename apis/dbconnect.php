<?php
$db_name = "<set your own>";
$db_password = "<set your own>";
$link = mysql_connect('localhost', $db_name, $db_password) or die('Could not connect: ' . mysql_error());
mysql_select_db('bsf') or die('Could not select database');
mysql_query("set names utf8;");
mysql_set_charset('utf8');
?>