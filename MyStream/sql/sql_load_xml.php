  <?php
  // ---------------- Connection Database ------------------------------------------
  include('../Connection.php');
  ini_set('max_execution_time', 120);
  // -------------------------------------------------------------------------------
  $path_xml = '../epg/it.xml';
  $path_epg = '../epg/epg.csv';
  $path_channel = '../epg/channel.csv';
  $path_picon = 'image/picons/';
  //$path_picon = "https://static.iptv-epg.com/it/";

  $del_channel = '..\epg\channel.csv';
  $del_epg = '..\epg\epg.csv';
  $del_xml = '..\epg\it.xml';

  $cmd = shell_exec('Del ' . $del_xml);
  $cmd = shell_exec('Del ' . $del_channel);
  $cmd = shell_exec('Del ' . $del_epg);

  $cmd = shell_exec('"..\epg\7z.exe" x ..\epg\_it.gz -so > '.$path_xml);  
  $cmd = shell_exec('..\epg\msxsl -t it.xml channel.xsl > '.$del_channel);
  $cmd = shell_exec('..\epg\msxsl -t it.xml epg.xsl > '.$del_epg);
  //  $cmd = shell_exec('powershell "Get-Content ' . $path_xml . ' -Encoding byte -TotalCount 400KB | Set-Content ' . $path_channel . ' -Encoding byte 2>&1"');
  $TextSql = "TRUNCATE TABLE `mystream`.`import_channel`";
  //echo $TextSql;
  $Res = mysqli_query($mysqli, $TextSql);

//  $TextSql = "LOAD XML LOCAL INFILE '$path_channel' INTO TABLE `mystream`.`import_channel` CHARACTER SET 'utf8' ROWS IDENTIFIED BY '<channel>'";
  $TextSql = "LOAD DATA LOCAL INFILE '$path_channel' INTO TABLE `mystream`.`import_channel` CHARACTER SET 'utf8' FIELDS TERMINATED BY '|'";
  //echo $TextSql;
  $Res = mysqli_query($mysqli, $TextSql);

  $TextSql = "UPDATE `mystream`.`import_channel`  SET `id` = REPLACE(`id`, ' ', ''),`display-name` = UPPER(`display-name`),icon = concat('" . $path_picon . "',id,'.png') ;";  
  //echo $TextSql;
  $Res = mysqli_query($mysqli, $TextSql);

  $TextSql = "TRUNCATE TABLE `mystream`.`import_epg`";
  //echo $TextSql;
  $Res = mysqli_query($mysqli, $TextSql);

  //$TextSql = "LOAD XML LOCAL INFILE '$path_xml' INTO TABLE `mystream`.`import_epg` CHARACTER SET 'utf8' ROWS IDENTIFIED BY '<programme>'";
  $TextSql = "LOAD DATA LOCAL INFILE '$path_epg' INTO TABLE `mystream`.`import_epg` CHARACTER SET 'utf8' FIELDS TERMINATED BY '|'";
  //echo $TextSql;
  $Res = mysqli_query($mysqli, $TextSql);
  ?>