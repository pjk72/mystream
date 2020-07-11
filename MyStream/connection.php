<?php
// ---------------- Connection Database ------------------------------------------
$host = "localhost";
$user = "root";
$pass = "passroot";
$db = "mystream";
$mysqli = mysqli_connect($host, $user, $pass, $db);
$mysqli->set_charset("utf8");
/* check connection */
if ($mysqli->connect_errno) {
	printf("Connect failed: %s\n", $mysqli->connect_error);
	exit();
}
/*if (!$cn) {
	die('Could not connect: ' . mysql_error());
	exit;
}

if (!mysqli_select_db("mystream")) {
	die('Unable to select the database: ' . mysql_error());
	exit;
}*/
// -------------------------------------------------------------------------------
?>