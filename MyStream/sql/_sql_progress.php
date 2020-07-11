<?php
// ---------------- Connection Database ------------------------------------------
include('../Connection.php'); 
// -------------------------------------------------------------------------------

$RoutineSql = "select * from information_schema.PROCESSLIST where DB = 'mystream'";
$result = mysqli_query($mysqli, $RoutineSql);
while ($row = mysqli_fetch_array($result)) {
  $Proc[] = $row;
  $process_id = $row["Id"];
  if ($row["TIME"] > 2000) {
    $sql = "KILL $process_id";
    mysqli_query($mysqli, $sql);
  //  echo $RoutineSql;
  }
}
//echo json_encode($Proc);

$RoutineSql = "SELECT a.trx_started start,
                      a.trx_state status,
                      a.trx_query query,
                      UPPER(SUBSTRING_INDEX(LEFT(a.trx_query,length(a.trx_query)-2),'<',-1)) sub_query,
                      a.trx_operation_state type,
                      a.trx_rows_modified treated 
                      FROM information_schema.INNODB_TRX a
                      WHERE trx_query like 'LOAD XML LOCAL INFILE%'";
//echo $RoutineSql;
$Prog = mysqli_query($mysqli, $RoutineSql);
//echo "Today is " . date("H:i:s");
while ($Data = mysqli_fetch_assoc($Prog)) {
  $Val[] = $Data;
}
echo json_encode($Val);
