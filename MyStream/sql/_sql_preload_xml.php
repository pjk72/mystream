 <!DOCTYPE html>
 <link type="text/css" rel="stylesheet" href="../source/jquery-ui.css" />
 <script type="text/javascript" src="../source/jquery.js"></script>
 <script type="text/javascript" src="../source/jquery-ui.js"></script>
 <script language="javascript" type="text/javascript">
   $(function() {

     trovato = false;
     $("#output").text("");
     var progressbar = $("#progressbar"),
      progressLabel = $(".progress-label");
     progressbar.progressbar({
       value: false,
       change: function() {
         progressLabel.text(progressbar.progressbar("value") + "%");
       },
       complete: function() {
         progressLabel.text("Complete!");
       }
     });
     $(".ui-progressbar-value").css({
       "background": "#01579b"
     });

     function progress() {
       totale = 180500;
       //totale = 30000;
       $.ajax({
         type: "POST",
         url: "sql_progress.php",
         async: true,
         cache: false,
         data: {},
         success: function(msg) {
           if (msg !== "null") {
             vista = JSON.parse(msg);
             $("#query").text(vista[0]["sub_query"]);
             perc = parseFloat(((100 / totale) * vista[0]["treated"]).toFixed(2));
             $("#output").html(vista[0]["treated"] + " of ~" + totale);
             progressbar.progressbar("value", perc);
           }
         }
       })
     }

     $.ajax({
       type: "POST",
       url: "sql_preload_check.php",
       async: true,
       cache: false,
       data: {},
       success: function(tr) {
         if (tr.trim() === "0") {
           prog = setInterval(progress, 3000);
           $.ajax({
             type: "POST",
             url: "sql_load_xml.php",
             async: true,
             cache: false,
             data: {},
             success: function(msg1) {
               console.log(msg1);
               clearInterval(prog);
             }
           });
         } else {
           $("#output").html("DOWNLOAD ALREADY IN PROGRESS");
           setTimeout(function() {
             prog = setInterval(progress, 3000);
           }, 3000);
         }
       }
     });

   });
 </script>
 <div id="progressbar" style="background:#81d4fa;position:relative">
   <span id="query" style="position:absolute"></span>
   <label style="position:absolute;top:15px;left:49%;font-size:10px" id="output"></label>
   <div class="progress-label" style="position: absolute;left: 50%;font-weight:bold;text-shadow:1px 1px 0 fff">Loading...</div>
 </div>