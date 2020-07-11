function state(st) {
  if (st === 0)
    return "IDLE";
  if (st === 1)
    return "OPENING";
  if (st === 2)
    return "BUFFERING";
  if (st === 3)
    return "PLAYING";
  if (st === 4)
    return "PAUSED";
  if (st === 5)
    return "STOPPING";
  if (st === 6)
    return "ENDED";
  if (st === 7)
    return "ERROR";
}

function onServiceSelected(currentServiceRef, currentName) {
  console.log("-->" + currentServiceRef);
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
  searchXML(current_name, '1');
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