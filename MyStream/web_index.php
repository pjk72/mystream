<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <title>MyStream</title>

    <link href="source/materialize-src/css/materialize.css" type="text/css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="source/materialize-src/css/material_icon_i.css" />
    <link type="text/css" rel="stylesheet" href="source/jquery-ui.css" />
    <link type="text/css" rel="stylesheet" href="source/sidebar.css" /> 
    <link type="text/css" rel="stylesheet" href="source/jquery-ui-multiselect/jquery.multiselect.css" />
    <link type="text/css" rel="stylesheet" href="source/jquery-ui-multiselect/jquery.multiselect.filter.css" />
    <link rel="shortcut icon" type="/web-data/image/x-icon" href="/web-data/img/favicon.ico">

    <script type="text/javascript" src="source/jquery.js"></script>
    <script type="text/javascript" src="source/jquery-ui.js"></script>
    <script type="text/javascript" src="source/materialize-src/js/bin/materialize.js"></script>
    <script type="text/javascript" src="source/jquery-ui-multiselect/src/jquery.multiselect.js"></script>
    <script type="text/javascript" src="source/jquery-ui-multiselect/src/jquery.multiselect.filter.js"></script>
    <!-- <script type="text/javascript" src="source/moment-timezone.js"></script> -->
    <script type="text/javascript" src="source/moment.js"></script>    

    <script language="javascript" type="text/javascript">

      $(function () {

        function state(st) {
          if (st === 0) return "IDLE";
          if (st === 1) return "OPENING";
          if (st === 2) return "BUFFERING";
          if (st === 3) return "PLAYING";
          if (st === 4) return "PAUSED";
          if (st === 5) return "STOPPING";
          if (st === 6) return "ENDED";
          if (st === 7) return "ERROR";
        }

        function onServiceSelected(currentServiceRef, currentName) {
          $("embed").attr("hidden", false);
          if (currentServiceRef !== "vlcemptyservice") {
            vlc.video.marquee.enable();
            vlc.video.marquee.size = 12;
            vlc.video.marquee.position = "top-right";
            vlc.video.marquee.text = (currentName);
            setStreamTarget(currentServiceRef);
          } else {
            vlcStop();
          }
        }


        function vlcPlay(currentServiceRef, currentName) {
          try {
            onServiceSelected(currentServiceRef, currentName);
          } catch (e) {
            console.log("Nothing to play");
          }
        }

        function vlcPrev(currentNumber) {
          if (currentNumber >= 1) {
            current_number = Number(currentNumber) - 1;
            current_channel = $("option[count='" + current_number + "']").attr("id");
            current_name = $("option[count='" + current_number + "']").text();
            $("#SelectChannel option").removeAttr('selected');
            $("option[count='" + current_number + "']").prop("selected", true);
            $("#SelectChannel").multiselect('refresh');
            playUrl(current_channel);
          }
        }

        function vlcNext(currentNumber) {
          list_channel = $("#SelectChannel option").size();
          console.log(list_channel);
          if (currentNumber < list_channel - 1) {
            current_number = Number(currentNumber) + 1;
            current_channel = $("option[count='" + current_number + "']").attr("id");
            current_name = $("option[count='" + current_number + "']").text();
            $("#SelectChannel option").removeAttr('selected');
            $("option[count='" + current_number + "']").prop("selected", true);
            $("#SelectChannel").multiselect('refresh');
            playUrl(current_channel);
          }
        }

        function vlcStop() {
          try {
            vlc.playlist.stop();
            //CleanXML();
          } catch (e) {
            //	notify("Nothing to stop", false);
          }
        }

        function vlcVolumeUp() {
          if (vlc.audio.volume < 200) {
            if (vlc.audio.volume + 10 > 200) {
              vlc.audio.volume = 200;
            } else {
              vlc.audio.volume += 10;
            }
          }

          $('#vlcVolume').text(vlc.audio.volume);
        }

        function vlcVolumeDown() {
          if (vlc.audio.volume > 0) {
            if (vlc.audio.volume < 10) {
              vlc.audio.volume = 0;
            } else {
              vlc.audio.volume -= 10;
            }
          }

          $('#vlcVolume').text(vlc.audio.volume);
        }

        function vlcToogleMute() {
          vlc.audio.mute = !vlc.audio.mute;
          if (vlc.audio.mute) {
            $('#vlcVolume').text('Muted');
          } else {
            $('#vlcVolume').text(vlc.audio.volume);
          }
        }

        function vlcFullscreen() {
          if (vlc.playlist.isPlaying) {
            //if (vlc.input.hasVout) {
            if (vlc.video.fullscreen === false) {
              vlc.video.fullscreen = true;
              return;
            }
          }
          //notify("Cannot enable fullscreen mode when no Video is being played!",false);
        }

        function playUrl(url) {
          current = vlc.playlist.add(url);
          vlc.playlist.playItem(current);
          entrato = false;
          CleanXML();
          searchXML(current_name,current_channel, '1');
        }

        function setStreamTarget(servicereference) {
          host = top.location.host;
          url = servicereference;
          // debug("setStreamTarget " + url);
          vlc.playlist.clear();
          playUrl(url);
        }

        function CleanXML() {
          $(".brand-logo").text("");
          $("#start_info").text("--:--");
          $("#stop_info").text("--:--");
          $("#dettagli_info").text("");
          $("#info").attr({
            'title': ""
          });
          $(".linea_principale").css({
            "width": "0%"
          })
          $("#info").text("EPG - NO INFO");
        }


        function escapeHtml(text) {
          return text
              .replace("&amp;","&" )
              .replace("&lt;","<")
              .replace("&gt;",">" )
              .replace("&quot;", "'")
              .replace("&#039;","'");
        }

        function dateformat(dt_tempo,show) {
          GG = dt_tempo.getDate();if (GG.length === 1) {GG = "0" + GG;}
          MM = dt_tempo.getMonth() + 1;if (MM <= 9) {MM = "0" + MM;}
          YY = dt_tempo.getFullYear();
          HH = dt_tempo.getHours();if (HH <= 9) {HH = "0" + HH;}
          II = dt_tempo.getMinutes();if (II <= 9) {II = "0" + II;}
          SS = dt_tempo.getSeconds();if (SS <= 9) {SS = "0" + SS;}
          if (show === "0") {return  "" + YY + MM + GG + HH + II + SS;}
          if (show === "1") {return  HH +":"+ II;}
        }

        function searchXML(current_name,current_channel,vision) {                            
          var srchChannel = current_name.trim();    
          //console.log(srchChannel,srchChannel.length);
          $.ajax({
            type: "POST",
            url: "sql/sql_search_channel.php",
            async: false,
            cache: false,
            data: {s_channel: srchChannel},
            success: function (msg) {
              canale = JSON.parse(msg);   
              //console.log(canale); 
              var dt_tempo = new Date();              
              var dt_now = dt_tempo.getTime();
              trovato=false
              in_onda=0;              
              if (canale !== null) {
                for (i = 0; i < canale.length; i++) {                
                  if (canale[i]['status'] === '1') {
                    trovato=true;
                    in_onda = i;                    
                    i = canale.length;
                  }                
                }                           
                if (trovato) {                  
                  var tzstart = moment(canale[in_onda]['start'],'YYYYMMDDHHmmSS').subtract(60,'minutes');
                  start = new Date(tzstart);
                  dt_start = start.getTime();
                  var tzstop = moment(canale[in_onda]['stop'],'YYYYMMDDHHmmSS').subtract(60,'minutes');
                  stop = new Date(tzstop);
                  dt_stop = stop.getTime();
                  diff = (dt_stop) - (dt_start);      // (diff) : 100 = (avanz) : x --> (100 x avanz)/diff
                  avanz = (dt_now) - (dt_start);        
                  perc_visto = Math.round((avanz*100)/diff);          
                //  console.log(diff,avanz+" | "+ dt_now,dt_start+"-->"+perc_visto);
                } else {perc_visto = 0;}                   
                if (vision === '1') {                                                           
                  if (canale[in_onda]["icon"]  !== null) {
                  if (  canale[in_onda]["icon"].substr(0,4) === 'http') { path ="";} else {path="./image/picon/"}             
                  $(".brand-logo").html("<div style='float:right;margin:5px;'><img width='100px'; style='margin-right:50px' src='"+path+canale[in_onda]["icon"]+"'></div> "+canale[in_onda]['Link_Name']);                  
                  }
                  if (trovato) { $(".linea_principale").css({ "width": perc_visto + "%" }); 
                  $("#info").text(escapeHtml(canale[in_onda]['title']));                                    
                  $("#start_info").text(dateformat(start,"1")); 
                  $("#stop_info").text(dateformat(stop,"1")); 
                  trama = canale[in_onda]['desc'];
                  trama = trama.split("- Visita");
                  $("#dettagli_info").text(escapeHtml(trama[0]));
                  $("#info").attr({'title': trama[0]});
                  try { $("#next_info").text(canale[in_onda + 1]['title']); } catch (e) {}                             
                  }
                }
                if (vision ==='0') {                                                      
                  return canale[in_onda];
                }
              } else if (vision === '1') { $(".brand-logo").text(srchChannel); }
            }
          });
        }

        var current_name = "";
        var vlc = '';


        var def_title = "MyStream";
        $(document).ready(function () {
          setting();
        });

        function getPosition(string, subString, index) {
          return string.split(subString, index).join(subString).length;
        }

        function readSingleFile(e) {
          //console.log(e);
          var file = e.target.files[0];
          if (!file) {
            return;
          }
          $("title").text(def_title + "[" + file.name + "]");
          var reader = new FileReader();
          reader.onload = function (e) {
            var contents = e.target.result;
            data_array = contents.split('\n');
            //console.log(data_array);
            grouparray = "0|NOGROUP";
            namecount = -1;
            groupcount = 0;
            costrutto = new Array();
            costrutto[groupcount] = new Array();
            for (a = 1; a < data_array.length; a++) {
              linea = data_array[a];
              server = linea.search("http://");
              if (server === -1) { // Linea Contenente Nome
                group = linea.search("group-title");
                if (group > 0) { // Liena Contiene group-title
                  namecount = 0;
                  groupcount++;
                  var ps1 = getPosition(linea, '"', 1);
                  var ps2 = getPosition(linea, '"', 2);
                  var ps3 = getPosition(linea, ',', 2);
                  var grouparray = groupcount + "|" + linea.substring(ps1 + 1, ps2);
                  var namearray = linea.substring(ps3 + 1, linea.length);
                  costrutto[groupcount] = new Array();
                } else {
                  namecount++;
                  var ps4 = getPosition(linea, ',', 1);
                  var namearray = linea.substring(ps4 + 1, linea.length);
                }
                //console.log(grouparray, namecount,"|-|", linea);
                costrutto[groupcount][namecount] = grouparray + "|" + namearray;
              }
              if (server === 0) {
                //costrutto[grouparray][namecount] = linea;                        
                costrutto[groupcount][namecount] = costrutto[groupcount][namecount] + "|" + linea;
              }
            }
            //console.log(costrutto);
            $("#SelectBouquet").empty();            
            Inserisci(costrutto, "");
          };
          reader.readAsText(file);
        }

        function video_center(delta) {          
          mini = false;
          nh = $(window).height() - 190;
          nw = $(window).width() - 10 - delta;
          $("#vlc").height(nh);
          $("#vlc").width(nw);

          $("#vlcPlayer").css({
            "position": "relative",
            "left": "0px",
            "top": "0px"
          });                    
          $("#vlcButtons").css({"margin": "auto", "width": "455px"});
          $(".on_progress").css("width", (nw - 150));
          $("#div_dettagli").css("width", (nw - 150));
        }
        function video_mini_left() {
          mini = true;
          nh = $(window).height() - 190;
          nw = 560;
          $("#vlcPlayer").css({
            "position": "relative",
            "left": "0px",
            "top": "0px"
          });

          $("#vlc").width(nw);
          $("#vlc").height(nh);
          $("#vlcButtons").css({"margin": "auto", "width": "90%"});
        }

        function video_mini_right() {
          mini = true;
          nw = $(window).width() - 440 + "px";
          $("#vlcPlayer").css({
            "position": "relative",
            "left": nw,
            "top": "0px"
          });

          $("#vlc").width(440);
          $("#vlc").height(200);
        }

        $(window).resize(function () {
          video_center(delta);
        });

        function Inserisci(costrutto, gruppo) {          
          if (gruppo == 'refresh') {gruppo = lastgroup;} else {lastgroup=gruppo;}
          $(".collection").empty();          
          for (x = 0; x <= costrutto.length - 1; x++) {
            for (y = 0; y <= costrutto[x].length - 1; y++) {
              segment = costrutto[x][y].split("|");              
              if (y === 0 && gruppo === "") {
                $("#SelectBouquet").append("<option class='cl_bouquet' id='" + segment[1] + "'>" + segment[1] + "</option>");                
              }          
              if ($.trim(segment[1]) === $.trim(gruppo) || $.trim(gruppo) === "All Channel") {                
                searchXML(segment[2],'','0');      
                p_title="";
                p_orario="";                
                p_header="";
                if (y===0 && x===0) {p_header = "<i style='position:absolute;left:10px;top:12px;' class='material-icons prefix'>search</i><input type='search' class='white-text' style='margin-bottom:30px' placeholder='Search' id='s_canale'>"}                
                aj_logo = "  ";
                p_canale = "<div style='margin-top:-10px' class='scelto'>"+ segment[2].toUpperCase() + "</div>";
                p_progress = "<div class='determinate blue' style='width: 0%'></div>" +"</div>";                                
                 p_logo= "<div style='position:absolute;left:2px'><img style='width:35px; margin-top:-10px' src='image/tv-orange.png'></div>";
                if (canale !== null)  {                  
                  if (canale[in_onda]["icon"] !== null) {
                    if (canale[in_onda]["icon"].substr(0,4) === 'http') { path ="";} else {path="./image/picon/"}
                    p_logo= "<div style='position:absolute;left:2px'><img style='width:40px' src='"+path+canale[in_onda]["icon"]+"'></div>";
                    }
                }
                if (trovato) {                                
                  p_progress = "<div class='determinate blue' style='width: "+ perc_visto +"%'></div>" +"</div>";
                  p_orario="<div style='margin-top:-8px;font-size:11px;'>"+dateformat(start,"1")+" - "+dateformat(stop,"1")+"</div>"
                  p_title= "<div class='orange-text' style='margin-bottom:-10px;'>"+canale[in_onda]['title']+"</div>";                  
                }
                
                $(".collection").append("<a href='#!' class='collection-item grey darken-4 white-text' cur_group= '"+ segment[1] + "' cur_nome='"+ segment[2] +"' style='padding-left:50px;font-size:12px;' id='" + segment[3]+"'>" +                                        
                                        p_header +
                                        p_logo +
                                        p_canale +
                                        p_orario +
                                        "<div class='progress l_progress grey darken-3' style='width: 300px;margin:1px;'>" +
                                        p_progress +
                                        p_title + 
                                        "</a>");                                    
              }
            }
          }
          $(".collection-item").click( function() {            
            current_name = $(this).closest("a").attr("cur_nome");
            current_channel = $(this).closest("a").attr("id");
            searchXML(current_name,current_channel,"1");
            if (gruppo !== 'refresh') {onServiceSelected(current_channel, current_name);}
          });

          if (gruppo === "") {
            $(".singolo").multiselect('refresh');
          } 
          setTimeout(function () {
            $(".ui-multiselect-menu").css({"top": "90px"});
          }, 500);
        }

        $("#opennav").click(function () {
          delta = 400;
          $("#mySidenav").css({"width": delta + "px"});
          $("#main").css({"marginLeft": delta + "px"});
          video_center(delta);
        });

        $("#closenav").click(function () {
          $("#mySidenav").css({"width": "0"});
          $("#main").css({"marginLeft": "0"});
          delta = 0;
          video_center(delta);
        });

        function setting() {
          $("#SelectBouquet").multiselect({
            selectedList: 1,
            multiple: false,
            header: "",
            noneSelectedText: "BOUQUET",
            show: ["blind", 200],
            hide: ["blind", 200],
            click: function (event, ui) {              
              Inserisci(costrutto, ui.value);              
            }
            //	beforeopen: function () { video_mini_left(); },
            //	close: function () { video_center(delta); },
          }).multiselectfilter();
          setTimeout(function() {$("#opennav").click();},300);

          vlc = document.getElementById("vlc");
          var entry = document.getElementById('file-input');
          entry.addEventListener('change', readSingleFile, false);
          $('.sidenav').sidenav({
            onCloseStart: function () {
              video_center(delta);
            },
            onOpenStart: function () {
              video_mini_right();
            }
          });

          $("title").text(def_title);

          $("#bt_vlcPlay").click(function () {
            vlcPlay(current_channel, current_name);
          });
          $("#bt_vlcStop").click(function () {
            vlcStop();
          });
          $("#bt_vlcFullscreen").click(function () {
            vlcFullscreen();
          });
          $("#bt_vlcPrev").click(function () {
            vlcPrev(current_number);
          });
          $("#bt_vlcNext").click(function () {
            vlcNext(current_number);
          });
          $("#bt_vlcVolumeUp").click(function () {
            vlcVolumeUp();
          });
          $("#bt_vlcToogleMute").click(function () {
            vlcToogleMute();
          });
          $("#bt_vlcVolumeDown").click(function () {
            vlcVolumeDown();
          });

          $(".drp_channel_nav,.drp_bouquet_nav").mouseleave(function () {
            $(this).click();
            video_center(delta);
          });
          //$(".liste_nav").click(function () { if (mini === true) { video_center(delta); } else { video_mini_left(); } });
          setInterval(function () {
            if (vlc.input.state === 3) {
              searchXML(current_name,current_channel,'1');
              Inserisci(costrutto, "refresh");
              
            }            
          }, 60000);

          setInterval(function () {
            $("#status").text(state(vlc.input.state));            
            if ((vlc.input.state === 3 || vlc.input.state === 1) && entrato === false) {
              entrato = true; 
              searchXML(current_name,current_channel,'1');
              video_center(delta);
            }
            if (vlc.input.state === 6) {
              playUrl(current_channel);
            }
          }, 3000);
        }
      })      
    </script>


  </head>


  <body>    
    <div id="mySidenav" class="p_sidenav">            
      <a id="closenav" class="closebtn">
        <button style="position: absolute;top:5px;left:10px;margin:5px;" class="waves-effect waves-light white-text btn-flat">
          <i class="material-icons">close</i>
        </button>        
      </a>
      <div class="browser">
        <input type="file" id="file-input" style="width:1px;display:none" />
        <button style="position: absolute;top:8px; margin:5px;" title="download file m3u" class="waves-effect waves-light btn red white-text" onclick="document.getElementById('file-input').click();">
          <i class="material-icons">local_movies</i>
        </button>
      </div>  
            <div id="div_download" style="position: absolute;top:8px;left:75px;margin:5px">
              <a href="http://epg-guide.com/it.gz" class="waves-effect waves-light btn orange lighten-2" title="download epg info">
                <i class="material-icons">file_download</i>
              </a>
              <!--	<a href="http://epg-guide.com/it.gz" download="Filename.ext">
                  Click to download</a> -->
            </div>      
      <div id="div_bouquet">
        <select class="singolo" id="SelectBouquet" multiple='multiple'></select>
      </div> 
       <ul class="collection" style="border: 0"></ul>
    </div>

    <div id="main" style="padding:0;">		
      <!-- <div id="div_update_epg" style="background:red;position:absolute; width:100%">
        <button class="btn waves-effect waves-light" onclick="window.open('loading_epg.php', 'windowname', 'width=400,height=300,scrollbars=no');">test</button>
        </div> -->
      <div class="blue-grey" style="height:130px;" id="container">			
        <span id="opennav" style="font-size:30px;cursor:pointer;margin:20px">
          <button class="waves-effect waves-light btn-floating blue white-text">
          <i class="material-icons">menu</i>
        </button>

        </span>
        <span class="white-text brand-logo" style="font-size:24px;margin-left:10px;font-weight: bold"></span>

        <div id="div_info" class="white-text" style="position:absolute;top:32px;margin-left: 100px">
          <span id="info"></span>&nbsp;&nbsp;(&nbsp;
          <span class="yellow-text" style="font-size: 10px;font-weight:bolder" id="status">IDLE</span>        
          &nbsp;)&nbsp;&nbsp;>&nbsp;&nbsp;
          <span id="next_info" class="grey-text darken-4" style="font-size: 10px;"></span>
        </div>
        
        <div id="div_start" class="white-text" style="top: 50px; position: absolute;"><span id="start_info" style="margin-left: 100px">--:--</span>&nbsp;&nbsp;-&nbsp;&nbsp;<span id="stop_info">--:--</span></div>
        
        <div class="progress on_progress" class="blue-grey darken-1" style="margin-left: 100px;position: absolute;top:65px">
          <div class="determinate linea_principale white" style="width: 0%"></div>            
        </div>
                
        <div id="div_dettagli" class="grey-text lighten-4" style="font-size: 11px;top: 80px; margin-left: 100px; position: absolute;">
          <span id="dettagli_info"></span>
        </div>              
                       

        <div id="tab_video" class="col s12">
          <div id="content" style="position:relative;top:80px" class="card blue-grey lighten-3">

            <div id="vlcPlayer" style="display: inline;float: none; padding: 5px; ">
              <embed type="application/x-vlc-plugin" toolbar="false" hidden="false" volume="100" pluginspage="http://www.videolan.org
                     " version="VideoLAN.VLCPlugin.2" width="100%" height="100px" id="vlc">
            </div>
            <div style="padding: 5px" id="vlcButtons">
                <!-- <center> -->
              <button class="waves-effect waves-light btn blue" id="bt_vlcPrev" title="Previous Service">
                <i class="material-icons">skip_previous</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcPlay" title="Play">
                <i class="material-icons">play_arrow</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcNext" title="Next Service">
                <i class="material-icons">skip_next</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcStop" title="Stop">
                <i class="material-icons">stop</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcFullscreen" title="Full Screen">
                <i class="material-icons">fullscreen</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcVolumeDown" title="Volume Down">
                <i class="material-icons">volume_down</i>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcToogleMute" title="Mute Audio">                
                <i class="material-icons">volume_mute</i>
                <span id="vlcVolume" class="yellow-text" style="font-size: 10px;position: absolute;top:12px;left:5px">100</span>
              </button>
              <button class="waves-effect waves-light btn blue" id="bt_vlcVolumeUp" title="Volume Up">
                <i class="material-icons">volume_up</i>
              </button>
              <!-- </center> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html> 