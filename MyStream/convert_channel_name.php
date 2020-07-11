<?php

// ---------------- Connection Database ------------------------------------------
include('Connection.php'); 
// -------------------------------------------------------------------------------

$dir_or = 'image/picon_id/';
$dir_ds = 'image/picon_name/';
//$files = scandir($dir);
//print_r($files);
$TextSql = "select * from exticon ";
$Res = mysql_query($TextSql);
while ($Info = mysql_fetch_assoc($Res)) {
  foreach (glob($dir_or . "1*.png") as $files) {
    $files = str_replace($dir_or, '', $files);
    $immagine = explode("_", $Info['ic_image']);
    $file = explode("_", $files);
    if ($immagine[0] <= $file[0]) {
      if ($immagine[1] <= $file[1]) {
        if ($immagine[2] <= $file[2]) {
          if ($immagine[3] <= $file[3]) {
            if ($immagine[4] <= $file[4]) {
              if ($immagine[5] <= $file[5]) {
                if ($immagine[6] <= $file[6]) {
                  //echo strlen($Info['ic_image']), $Info['ic_image'], strlen($files), $files . "<br>";
                  if ($Info['ic_image'] === $files) {
                    rename($dir_or . $files, $dir_ds . $Info['ic_name'] . '.png');
                    echo "<p style='background:red'>" . $files . " >>> " . $Info['ic_name'] . '.png</p><br>';
                    continue;
                  }
                } else {
                  continue;
                }
              } else {
                continue;
              }
            } else {
              continue;
            }
          } else {
            continue;
          }
        } else {
          continue;
        }
      } else {
        continue;
      }
    } else {
      continue;
    }
  }
}
