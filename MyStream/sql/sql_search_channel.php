<?php
// ---------------- Connection Database ------------------------------------------
include('../Connection.php');
// -------------------------------------------------------------------------------
//$tz = new DateTimeZone('CEST');

$date = new DateTime();
//$date->setTimezone($tz);

$channel = $_POST['s_channel'];
//print_r($channel);
for ($i = 0; $i < count($channel); $i++) {
  $channel[$i] = str_replace("'", "`", $channel[$i]);
  if ($channel[$i] === "" OR $channel[$i] === null ) {
    unset($channel[$i]);
  }
}
$lista = implode("','", $channel);
$lista = str_replace(' ', '', $lista);

//$lista = 'RSI LA2 HD';
$TextSql = "SELECT d.start,d.stop,d.channel,      
                 d.title,d.`desc`,d.category,
                 c.icon,d.status
            FROM (import_epg d INNER JOIN import_channel c 
              ON (UPPER(c.`display-name`) = UPPER(d.channel))   
              AND ( 
                UPPER(REPLACE(c.`display-name`, ' ','')) in ('" . $lista . "')
                OR
                UPPER(c.`display-name`) in ('" . $lista . "')
              )
 /* GMT  */  -- AND DATE_ADD(NOW(), INTERVAL 2 HOUR) BETWEEN STR_TO_DATE(SUBSTRING(start,1,14), '%Y%c%e%H%i%s') AND  STR_TO_DATE(SUBSTRING(stop,1,14), '%Y%c%e%H%i%s') 
 /* GMT */    AND DATE_ADD(NOW(), INTERVAL SUBSTRING(start,16,3) + TIME_FORMAT(TIMEDIFF(NOW(), UTC_TIMESTAMP),'%H') HOUR) BETWEEN STR_TO_DATE(SUBSTRING(start,1,14), '%Y%c%e%H%i%s') AND  STR_TO_DATE(SUBSTRING(stop,1,14), '%Y%c%e%H%i%s') 

              )
            GROUP BY d.start,d.stop, d.title
            UNION 
            SELECT '' `start`,'' `stop`,`display-name`,'' title,'' `desc`,'' category,icon,'0' `status` 
            FROM import_channel    
            WHERE  UPPER(REPLACE(`display-name`, ' ','')) in ('" . $lista . "')
              OR   UPPER(`display-name`) in ('" . $lista . "')
";
//echo $TextSql;
$Res = mysqli_query($mysqli, $TextSql);
//$Dt = $date->format('YmdHis');
while ($Info = mysqli_fetch_assoc($Res)) {
  if ($Info["start"] !== "") {
    $Info["status"] = "1";
  }
  $Info["desc"] = htmlspecialchars($Info["desc"]);
  $Info["title"] = htmlspecialchars($Info["title"]);
  $Info["channel"] = strtoupper(str_replace(" ", "", $Info["channel"]));
  $Data[$Info["channel"]][] = $Info;
}
echo json_encode($Data);
