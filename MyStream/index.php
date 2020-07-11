<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta content="text/html; charset=UTF-8" http-equiv="content-type">
  <title>MyStream</title>

  <link type="text/css" rel="stylesheet" href="source/materialize-src/css/materialize.css">
  <link type="text/css" rel="stylesheet" href="source/main.css">
  <link type="text/css" rel="stylesheet" href="source/materialize-src/css/material_icon_g.css" />
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
  <script type="text/javascript" src="source/moment.js"></script>
  <script type="text/javascript" src="source/moment-timezone.js"></script>


  <script language="javascript" type="text/javascript">
    $(function() {

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
        console.log(currentServiceRef, currentName)
        $("embed").attr("hidden", false);
        if (currentServiceRef !== "vlcemptyservice") {
          //     vlc.video.marquee.enable();
          //     vlc.video.marquee.size = 12;
          //     vlc.video.marquee.position = "top-right";
          //     vlc.video.marquee.text = (currentName);
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
          // }
          //notify("Cannot enable fullscreen mode when no Video is being played!",false);
        }
      }

      function playUrl(url) {
        //  current = vlc.playlist.add(url);
        //vlc.playlist.playItem(current);
        entrato = false;
        CleanXML();
        console.log("XML->play");
        Inserisci(costrutto, "refresh", "1");
      }

      function setStreamTarget(servicereference) {
        host = top.location.host;
        url = servicereference;
        // debug("setStreamTarget " + url);
        // vlc.playlist.clear();
        interv = 0;
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
          .replace("&amp;", "&")
          .replace("&lt;", "<")
          .replace("&gt;", ">")
          .replace("&quot;", "'")
          .replace("&#039;", "'");
      }

      function dateformat(dt_tempo, show) {
        GG = dt_tempo.getDate();
        if (GG.length === 1) {
          GG = "0" + GG;
        }
        MM = dt_tempo.getMonth() + 1;
        if (MM <= 9) {
          MM = "0" + MM;
        }
        YY = dt_tempo.getFullYear();
        HH = dt_tempo.getHours();
        if (HH <= 9) {
          HH = "0" + HH;
        }
        II = dt_tempo.getMinutes();
        if (II <= 9) {
          II = "0" + II;
        }
        SS = dt_tempo.getSeconds();
        if (SS <= 9) {
          SS = "0" + SS;
        }
        if (show === "0") {
          return "" + YY + MM + GG + HH + II + SS;
        }
        if (show === "1") {
          return HH + ":" + II;
        }
      }

      function searchXML(current_name, current_channel) {
        $.ajax({
          type: "POST",
          url: "sql/sql_search_channel.php",
          async: false,
          cache: false,
          data: {
            s_channel: current_name
          },
          success: function(msg) {
            canale = JSON.parse(msg);
            // console.log(canale);
          }
        });
      }

      var current_name = "";
      var vlc = '';


      var def_title = "MyStream";
      $(document).ready(function() {
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
        reader.onload = function(e) {
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
                linea = linea.replace(/[\n\r]/g, '');
                var namearray = linea.substring(ps4 + 1, linea.length);
              }
              costrutto[groupcount][namecount] = grouparray + "|" + namearray;
            }
            if (server === 0) {
              costrutto[groupcount][namecount] = costrutto[groupcount][namecount] + "|" + linea;
            }
          }
          //console.log(costrutto);                        
          Inserisci(costrutto, "", "0");

          $("#SelectBouquet").empty();
        };
        reader.readAsText(file);
      }

      function Filter(str) {
        $(".collection-item").each(function() {
          if ($(this).attr("cur_nome").includes(str.toUpperCase())) {
            $(this).fadeIn(300);
          } else {
            $(this).fadeOut(300)
          }
        });
      }

      $("#id_search_channel").keyup(function() {
        Filter($(this).val());
      });

      function List_Zone() {
        $.ajax({
          type: "POST",
          url: "sql/sql_list_zone.php",
          async: true,
          cache: false,
          data: {},
          success: function(msg) {
            // listzone = JSON.parse(msg);
            // console.log(canale);
            var offset = new Date().getTimezoneOffset();
            console.log(offset);
          }
        });
      }

      function offset_zone(tempo, locale) {
        locale = "Africa/Casablanca";
        tempo = tempo.replace(/\s+/g, '');
        tempo = [tempo.slice(0, 17), ":", tempo.slice(17)].join('');
        tempo = [tempo.slice(0, 12), ":", tempo.slice(12)].join('');
        tempo = [tempo.slice(0, 10), ":", tempo.slice(10)].join('');
        tempo = [tempo.slice(0, 8), "T", tempo.slice(8)].join('');
        tempo = [tempo.slice(0, 6), "-", tempo.slice(6)].join('');
        tempo = [tempo.slice(0, 4), "-", tempo.slice(4)].join('');
        var m_tempo = moment(tempo);
        var m_format = m_tempo.tz(locale);
        var result = new Date(m_format);
        return result
      }

      function Inserisci(costrutto, gruppo, vision) {
        console.log("Entrato in Inserisci", gruppo, vision);
        $("ul.collection").empty();
        cc = -1;
        lista_canali = [];
        lista_url = [];
        lista_grp = [];
        if (gruppo == 'refresh') {
          gruppo = lastgroup;
        } else {
          lastgroup = gruppo;
        }
        console.log(costrutto);
        for (x = 0; x <= costrutto.length - 1; x++) {
          for (y = 0; y <= costrutto[x].length - 1; y++) {
            segment = costrutto[x][y].split("|");
            if (y === 0 && gruppo === "" && segment[2] !== "") {
              $("#SelectBouquet").append("<option class='cl_bouquet' id='" + segment[1] + "'>" + segment[1] + "</option>");
            }
            if (($.trim(segment[1]) === $.trim(gruppo) || $.trim(gruppo) === "All Channel") && segment[2] !== "") {
              cc++;
              lista_grp[cc] = segment[1].toUpperCase();
              lista_url[cc] = segment[3];
              lista_canali[cc] = segment[2].toUpperCase();

            }
          }
        }
        if (gruppo === "") {
          $(".singolo").multiselect('refresh');
        }
        setTimeout(function() {
          $(".ui-multiselect-menu").css({
            "top": "90px"
          });
        }, 500);
        searchXML(lista_canali, '');
        //console.log(canale, lista_url, lista_grp, lista_canali,lista_canali.length);
        esiste = false;
        for (z = 0; z <= lista_canali.length - 1; z++) {
          var dt_tempo = new Date();
          var dt_now = dt_tempo.getTime();
          trovato = false
          in_onda = 0;
          act_canale = lista_canali[z].replace(/\s/g, '');
          if (canale !== null && typeof canale[act_canale] !== "undefined" && canale[act_canale][0]['channel'] !== '') {
            for (i = 0; i < canale[act_canale].length; i++) {
              if (canale[act_canale][i]['status'] === '1') {
                trovato = true;
                in_onda = i;
                i = canale[act_canale].length;
              }
            }
            if (trovato) {
              /* GMT */
              //var tzstart = moment(canale[act_canale][in_onda]['start'], 'YYYYMMDDHHmmSS').subtract(60, 'minutes');
              start = offset_zone(canale[act_canale][in_onda]['start']);
              dt_start = start.getTime();
              /* GMT */
              //              var tzstop = moment(canale[act_canale][in_onda]['stop'], 'YYYYMMDDHHmmSS').subtract(60, 'minutes');
              stop = offset_zone(canale[act_canale][in_onda]['stop']);
              dt_stop = stop.getTime();
              diff = (dt_stop) - (dt_start); // (diff) : 100 = (avanz) : x --> (100 x avanz)/diff
              avanz = (dt_now) - (dt_start);
              rest = (dt_stop) - (dt_now);
              rest_min = (Math.round(rest / 1000 / 60));
              perc_visto = Math.round((avanz * 100) / diff);
              //console.log(diff, avanz + " | " + dt_now, dt_start + "-->" + perc_visto);
            } else {
              perc_visto = 0;
            }
            if (vision === '1' && current_name.replace(/\s/g, '') === canale[act_canale][in_onda]["channel"]) {
              esiste = true;
              $(".brand-logo").html(current_name);
              if (canale[act_canale][in_onda]["icon"] !== null) {
                $(".brand-logo").html("<div style='float:right;margin:5px;'><img width='100px'; style='margin-right:50px' src='" + canale[act_canale][in_onda]["icon"] + "'  ></div> " + current_name);
              }
              if (trovato) {
                $(".linea_principale").css({
                  "width": perc_visto + "%"
                });
                $("#info").text(escapeHtml(canale[act_canale][in_onda]['title']));
                $("#start_info").text(dateformat(start, "1"));
                $("#stop_info").text(dateformat(stop, "1"));
                trama = canale[act_canale][in_onda]['desc'];
                trama = trama.split("- Visita");
                $("#dettagli_info").text((trama[0]));
                $("#info").attr({
                  'title': trama[0]
                });
                try {
                  $("#next_info").text(canale[act_canale][in_onda + 1]['title']);
                } catch (e) {}
              }
            }
          } else if (vision === '1' && esiste === false) {
            $(".brand-logo").text(current_name);
          }
          //}
          p_title = "";
          p_orario = "";
          p_header = "";
          aj_logo = "  ";
          p_canale = "<div style='margin-top:-10px; margin-left:80px' class='scelto'>" + lista_canali[z].toUpperCase() + "</div>";
          p_progress = "<div class='determinate blue' style='width: 0%;margin-bottom:0'></div>" + "</div>";
          p_logo = "<div style='position:absolute;left:2px'><img style='height:35px;width:75px; margin-left:5px;margin-top:-10px' src='image/tv-orange.png'></div>";
          if (canale !== null && typeof canale[act_canale] !== "undefined") {
            p_logo = "<div style='position:absolute;left:2'><img style='height:45px;width:75px;margin-top:-10px;' src='" + canale[act_canale][in_onda]['icon'] + "'  onerror=\"this.src='image/tv-orange.png'\"></div>";
          }
          if (trovato) {
            p_progress = "<div class='determinate blue' style='margin-bottom:0;width: " + perc_visto + "%'></div>" + "</div>";
            p_orario = "<div style='margin-left:80px;margin-top:-8px;font-size:11px;'>" + dateformat(start, "1") + " - " + dateformat(stop, "1") + "<span style='float:right'>+" + Math.abs(rest_min) + " min.</span></div>"
            p_title = "<div class='orange-text' style='margin-bottom:-15px;'>" + canale[act_canale][in_onda]['title'] + "</div>";
          }
          str = $("#id_search_channel").val();
          if (lista_canali[z].toUpperCase().includes(str.toUpperCase()) || str === "") {
            st = "style = 'padding-bottom:15px;padding-left:5px;font-size:12px;'";
          } else {
            st = "style = 'display:none;padding-bottom:15px;padding-left:5px;font-size:12px;'";
          }
          $(".collection").append("<a href='#!' class='collection-item grey darken-4 white-text' cur_group= '" + lista_grp[z] + "' cur_nome='" + lista_canali[z] + "' " + st + " id='" + lista_url[z] + "'>" +
            p_header +
            p_logo +
            p_canale +
            p_orario +
            "<div class='progress l_progress grey darken-3' style='margin-bottom:0;margin-left:80px;width: 295px;'>" +
            p_progress +
            p_title +
            "</a>");


        }
        $(".collection-item").click(function() {
          current_name = $(this).closest("a").attr("cur_nome");
          current_channel = $(this).closest("a").attr("id");
          if (gruppo !== 'refresh') {
            onServiceSelected(current_channel, current_name);
          }
        });
      }

      $("#opennav").click(function() {
        $("#opennav").fadeOut(100);
        $("#mySidenav").css({
          "width": "400px",
        });
      });

      $("#closenav, #video").click(function() {
        $("#mySidenav").css({
          "width": "0",
        });
        $("#opennav").fadeIn(500);
      });

      function setting() {
        interv = 0;
        List_Zone();
        $("#SelectZone").multiselect({
          selectedList: 1,
          multiple: false,
          header: "",
          minWidth: 100,
          noneSelectedText: "ZONE",
          show: ["blind", 200],
          hide: ["blind", 200],
          click: function(event, ui) {}
        }).multiselectfilter();


        $("#SelectBouquet").multiselect({
          selectedList: 1,
          multiple: false,
          header: "",
          minWidth: 300,
          noneSelectedText: "BOUQUET",
          show: ["blind", 200],
          hide: ["blind", 200],
          click: function(event, ui) {
            Inserisci(costrutto, ui.value, "0");
          }
        }).multiselectfilter();
        setTimeout(function() {}, 300);

        vlc = document.getElementById("vlc");
        var entry = document.getElementById('file-input');
        entry.addEventListener('change', readSingleFile, false);
        $('.sidenav').sidenav({
          onCloseStart: function() {},
          onOpenStart: function() {}
        });

        $("title").text(def_title);        

        $(".drp_channel_nav,.drp_bouquet_nav").mouseleave(function() {
          $(this).click();
        });
        setInterval(function() {
          Inserisci(costrutto, "refresh", '1');
        }, 30000);

      }
    })
  </script>

</head>


<body>
  <div id="mySidenav" class="p_sidenav" style="opacity:0.5;box-shadow: 5px 0px 10px 2px">
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
    <div id="div_scelta">
      <div style="float:left;width:75%">
        <select class="singolo" id="SelectBouquet" multiple='multiple'></select>
      </div>
      <div style="float:right;width:25%"><select class="singolo" id="SelectZone" multiple='multiple'>
          <option>GMT</option>
        </select></div>
    </div>
    <div class="input-field">
      <input id="id_search_channel" type="text" style="color:white;padding-left:5px;padding-right:5px" placeholder="search" value="" />
    </div>
    <ul class="collection" style="border: 0"></ul>
  </div>

  <div id="main" style="padding:0;">
    <div class="blue-grey" style="height:130px;" id="container">
      <span id="opennav" style="position:absolute;top:-15px;font-size:30px;cursor:pointer;margin:20px">
        <button id="but_menu" class="waves-effect waves-light btn-floating grey">
          <i class="material-icons">list</i>
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
        <div class="bgfull" style="display: inline;float: none; padding: 5px; ">
          <video id="video" class="responsive-video" style="width: 100%;height: 100%;" controls></video>
        </div>
      </div>
    </div>
  </div>

</body>

</html>