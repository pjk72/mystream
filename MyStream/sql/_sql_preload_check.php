  <?php
  // ---------------- Connection Database ------------------------------------------
  include('../Connection.php');
  // -------------------------------------------------------------------------------
  $result = mysqli_query($mysqli, "select * from information_schema.PROCESSLIST where DB = 'mystream'");
  while ($row = mysqli_fetch_array($result)) {
    $process_id = $row["Id"];
    if ($row["TIME"] > 2000) {
      $sql = "KILL $process_id";
      mysqli_query($mysqli, $sql);
    }
  }

  $result = mysqli_query($mysqli, "select * from information_schema.PROCESSLIST where DB = 'mystream' and state <> 'query end'");
  $trovato = "0";
  while ($row = mysqli_fetch_array($result)) {
    // print_r($row);    
    if (strpos($row["INFO"], 'LOAD XML LOCAL INFILE') !== false) {
      $trovato = "1";
    }
  }
  echo $trovato;
