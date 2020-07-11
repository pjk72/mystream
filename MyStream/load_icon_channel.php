<?php

// ---------------- Connection Database ------------------------------------------
include('Connection.php'); 
// -------------------------------------------------------------------------------

$dir = 'image/picons/';
foreach (glob($dir . "*.png") as $files) {
	$icon = str_replace($dir, '', $files);
  $file = explode('.',$icon);
  $picon = strtoupper($file[0]);
  $TextSql = "SELECT * FROM import_channel WHERE id = '".$picon."'";  
  $Res = mysql_query($TextSql);
	while ($Info = mysql_fetch_assoc($Res)) {		
		 if ($Info["ICON"]  ===  '') {
		 	$TextSql1 = "UPDATE import_channel SET icon = '".$files."' WHERE id = '".$picon."'";		
		 	$Res1 = mysql_query($TextSql1);		 	
		 }		
	}
}