var m3uParser = function(e) {
    function t(e) {
        if (!e) return !1;
        var t = /^\s*$(?:\r\n?|\n)/gm;
        e = e.replace(t, "");
        var i = e.split("\r#");
        if (0 != i[0].indexOf(n)) return !1;
        var l = 0;
        for (var o in i) - 1 == i[o].indexOf(n) && (r.push(a(i[o], l)), l++);
        return !0
    }

    function a(e, t) {
        let a = e.split(/\r\n|\r|\n/g),
            r = {};
        for (var n = 0; n < a.length; n++) {
            let e = a[n];
            if (!(e.length < 1)) {
                if (e.startsWith("EXTINF")) {
                    var o = l(e, /#EXTINF:.\d.+?\,(.+)|,(.+)/g);
                    o && (r.title = o);
                    var s = i(e, /.*tvg-id="(.*?)".*/g);
                    r.tvgId = s || r.title;
                    var d = i(e, /.*tvg-name="(.*?)".*/g);
                    r.tvgName = d || r.title;
                    var c = i(e, /.*tvg-logo="(.*?)".*/g);
                    r.tvgLogo = c ? r.tvgLogo : r.title;
                    var p = i(e, /.*group-title="(.*?)".*/g);
                    r.group = p || "UNGROUPED";
                    var u = i(e, /EXTINF:(.\d)/g);
                    r.timeshift = u || 0
                } else(e.startsWith("http") || e.startsWith("https") || e.startsWith("rtmp") || e.startsWith("rtsp") || e.startsWith("mms")) && (r.link = e);
                r.link && r.title && (r.listIndex = t)
            }
        }
        return r
    }

    function i(e, t) {
        var a = t.exec(e);
        if (a) return a[1]
    }

    function l(e, t) {
        var a = t.exec(e);
        if (a) return a[2]
    }
    var r = [],
        n = "#EXTM3U";
    this.getItem = function(e) {
        return r[e]
    }, this.getList = function() {
        return r
    }, this.clearList = function() {
        r = []
    }, this.addDataAndParse = function(e) {
        return t(e)
    }, this.getLength = function() {
        return r.length
    }
};

function loadDataFromStorage() {
    loadListeners();
    let e = $("#channelList").children.length,
        t = e - 1,
        a = "";
    if (void 0 !== playLists)
        for (let e of playLists) e = JSON.parse(e), null !== e && (0 != t && (a = '<li id="List' + e.playlistId + '" data-index="' + e.playlistId + '"><ul class="collapsible"> <li> <div class=""><span id="deleteList' + e.playlistId + '" style="cursor:pointer;"class="card-title grey-text text-darken-4"><i style="margin-top: 12px;" class="material-icons right">close</i></span><span class="card-title grey-text text-darken-4"><label><input name="listradio" type="radio"/><span><input type="text" id="inputListNameText' + e.playlistId + '" value="' + e.listname + '"/></span></label><i style="cursor: pointer;" class="collapsible-header material-icons right">more_vert</i></span></div> <div class="collapsible-body"><ul><li> <div class="input-field"><label for="inputM3UText' + e.playlistId + '">Enter Url for M3U List:</label> <input type="text" id="inputM3UText' + e.playlistId + '" placeholder="" value="' + e.playlist_url + '"/></div> </li><li> <div class="input-field"><label for="inputLogoText' + e.playlistId + '">Enter Url for Logo:</label> <input type="text" id="inputLogoText' + e.playlistId + '" placeholder="" value="' + e.logo_url + '"/><span></span> </div> </li> <li> <div class="input-field"><label for="inputEPGText' + e.playlistId + '">Enter Url for EPG(XMLTV):</label> <input type="text" id="inputEPGText' + e.playlistId + '" placeholder="" value="' + e.epg_url + '"/></div> </li> </ul></div> </li></ul> </li>'), $("#channelList").append(a), $(".collapsible").collapsible(), $("#deleteList" + e.playlistId).click(removeList), $('[id^="inputListNameText"],[id^="inputM3UText"],[id^="inputLogoText"],[id^="inputEPGText"]').on("change", function(t) {
            t.preventDefault(), console.log("accc"), localStorage.setItem("List" + e.playlistId, JSON.stringify({
                playlistId: e.playlistId,
                listname: $("#inputListNameText" + e.playlistId)[0].value,
                logo_url: $("#inputLogoText" + e.playlistId)[0].value,
                epg_url: $("#inputEPGText" + e.playlistId)[0].value,
                playlist_url: $("#inputM3UText" + e.playlistId)[0].value
            }))
        }));
    $("input:radio[name=listradio]").change(async function(e) {
        let t = $(e.target.parentNode.parentNode.parentNode.parentNode),
            a = $(t[0].children[1].children[0])[0],
            i = a.children[0].children[0].children[1].value,
            l = (a.children[1].children[0].children[1].value, a.children[2].children[0].children[1].value);
        if ('""' !== l) {
            let e = await loadxml(l),
                t = await new xmlTvParser(e);
            localStorage.setItem("epg_current", await JSON.stringify(t)), storageEpgCurrent = t
        } else localStorage.setItem("epg_current", []), M.toast({
            html: "Problem Downloading EPG",
            classes: "rounded"
        }, 3e3);
        if ('""' === i) return localStorage.setItem("channels", []), void M.toast({
            html: "Problem Downloading Channels List",
            classes: "rounded"
        }, 3e3); {
            let e = await loadm3u(i);
            if (!parserM3U.addDataAndParse(e)) return void M.toast({
                html: "Error Parsing M3U Playlist",
                classes: "rounded"
            }, 3e3); {
                let e = parserM3U.getList();
                channelsDataFromFile = groupChannels(e), localStorage.setItem("channels", JSON.stringify(channelsDataFromFile)), storageChannels = channelsDataFromFile, localStorage.setItem("currentList", JSON.stringify(t[0].childNodes[1].childNodes[0].id.replace("delete", "")))
            }
        }
        0 !== storageChannels.length && loadCurrentList(storageChannels, storageEpgCurrent)
    });
    try {
        let e = document.querySelector("#" + currentListfromStorage + " > ul > li > div:nth-child(1) > span:nth-child(2) > label > input[type=radio]");
        e.checked = !0;
        let t = new Event("change");
        e.dispatchEvent(t)
    } catch {
        console.log("a")
    }
}

function groupChannels(e) {
    let t = Object.values(e.reduce((e, {
        group: t,
        title: a,
        tvgName: i,
        link: l,
        tvgLogo: r,
        timeshift: n
    }) => (e[t] || (e[t] = {
        name: t,
        channels: []
    }), e[t].channels.push({
        Title: a,
        TvgName: void 0 === i ? a : i,
        TvgLogo: void 0 === r ? a : r,
        Group: t,
        Link: l,
        Timeshift: n
    }), e), {}));
    return t
}

function saveToStorage(e, t) {
    localStorage.setItem([e], t)
}

function strEndsWith(e, t) {
    return e.match(t + "$") == t
}

function doGo(e) {
    "undefined" != typeof hls && hls.destroy();
    let t = document.getElementById("video");
    if (e.endsWith(".ts") && (e = e.replace(".ts", "    .m3u8")), Hls.isSupported()) {
        let a = {
                autoStartLoad: !0,
                startPosition: -1,
                capLevelOnFPSDrop: !1,
                capLevelToPlayerSize: !1,
                defaultAudioCodec: void 0,
                initialLiveManifestSize: 1,
                maxBufferLength: 30,
                maxMaxBufferLength: 600,
                maxBufferSize: 6e7,
                maxBufferHole: .5,
                lowBufferWatchdogPeriod: .5,
                highBufferWatchdogPeriod: 3,
                nudgeOffset: .1,
                nudgeMaxRetry: 3,
                maxFragLookUpTolerance: .25,
                liveSyncDurationCount: 3,
                liveMaxLatencyDurationCount: 1 / 0,
                enableWorker: !0,
                enableSoftwareAES: !0,
                manifestLoadingTimeOut: 1e4,
                manifestLoadingMaxRetry: 1,
                manifestLoadingRetryDelay: 1e3,
                manifestLoadingMaxRetryTimeout: 64e3,
                startLevel: void 0,
                levelLoadingTimeOut: 1e4,
                levelLoadingMaxRetry: 4,
                levelLoadingRetryDelay: 1e3,
                levelLoadingMaxRetryTimeout: 64e3,
                fragLoadingTimeOut: 2e4,
                fragLoadingMaxRetry: 6,
                fragLoadingRetryDelay: 1e3,
                fragLoadingMaxRetryTimeout: 64e3,
                startFragPrefetch: !1,
                fpsDroppedMonitoringPeriod: 5e3,
                fpsDroppedMonitoringThreshold: .2,
                appendErrorMaxRetry: 3,
                enableWebVTT: !0,
                enableCEA708Captions: !0,
                stretchShortVideoTrack: !1,
                maxAudioFramesDrift: 1,
                forceKeyFrameOnDiscontinuity: !0,
                abrEwmaFastLive: 3,
                abrEwmaSlowLive: 9,
                abrEwmaFastVoD: 3,
                abrEwmaSlowVoD: 9,
                abrEwmaDefaultEstimate: 5e5,
                abrBandWidthFactor: .95,
                abrBandWidthUpFactor: .7,
                abrMaxWithRealBitrate: !1,
                maxStarvationDelay: 4,
                maxLoadingDelay: 4,
                minAutoBitrate: 0,
                emeEnabled: !1,
                widevineLicenseUrl: void 0
            },
            i = new Hls(a);
        i.loadSource(e), i.attachMedia(t), i.on(Hls.Events.MEDIA_ATTACHED, function() {
            M.toast({
                html: "[INFO] Preparing Video...",
                classes: "rounded"
            }, 3e3), t.play()
        })
    } else console.log("HLS Not suipported !")
}

function grupaTop(e) {
    return '<li id="grupa"><a class="collapsible-header waves-effect waves-blue">' + e + '<i id="grupa_arrow" class="material-icons right" style="margin-right:0;">arrow_drop_down</i></a><div class="collapsible-body"><ul id="grupaul">'
}
async function loadCurrentList(e, t) {
    let a = {};
    $("#playlist-slide-out").empty();
    let i = 0,
        l = '<li><div class="input-field col s12"><i class="material-icons prefix">search</i><input type="text" id="autocomplete-input" class="autocomplete"><label for="autocomplete-input">Search</label></div></li>',
        r = await JSON.parse(JSON.stringify(e)),
        n = await r.length,
        o = await JSON.parse(JSON.stringify(t)),
        s = await o.length;
    if (e.length > 0 || e.length > 100) {
        for (var d = 0; d < n; d++)
            if (l += grupaTop(r[d].name), r[d].channels) {
                for (let e of r[d].channels) {
                    a[e.Title] = {
                        link: e.Link,
                        text: e.Title,
                        img: "img/play.png"
                    }, i++;
                    let t = "No EPG";
                    if (!jQuery.isEmptyObject(o))
                        for (var c = 0; c < s; c++) o[c].channelName === e.TvgName && (t = o[c]);
                    l += createChannel(r[d].name, e.Title, e.TvgLogo, e.Link, "newTask", t, i)
                }
                l += "</ul></div></li>"
            } else r[d].channels = [], M.toast({
                html: "Empty Playlist!!!",
                classes: "rounded"
            }, 3e3);
        M.toast({
            html: "Playlist loaded!!!",
            classes: "rounded"
        }, 3e3), $("#playlist-slide-out").html(l), loadListeners(), $("#autocomplete-input").autocomplete({
            data: a,
            limit: 5,
            onAutocomplete: function(e) {
                doGo(a[e].link)
            },
            minLength: 2
        })
    }
}

function createChannel(e, t, a, i, l, r, n) {
    return "No EPG" === r ? (r = {
        channelName: t,
        programTitle: "",
        description: "",
        elapsed: 0,
        time: ""
    }, '<li id="channelli"><div class="chip"><label class="kanalMethod"><img id="image" src="' + storageLogo + "/" + a + '.png" style="background-color:white;"></img><input type="radio" name="channellinkradio" id="channellinkradio" value="' + i + '"></label><p class="text-epg" style="font-size:18px !important;">' + t + '<br></p></div></li><li><div class="divider"></div></li>') : '<li id="channelli"><div class="chip"><label class="kanalMethod"><img id="image" src="' + storageLogo + "/" + a + '.png" style="background-color:white;"></img><input type="radio" name="channellinkradio" id="channellinkradio" value="' + i + '"></label><p class="text-epg" style="font-size:18px !important;">' + t + '<br></p><span style="font-size:14px !important; margin-left: 50px;" class="tooltipped" data-position="right" data-tooltip="' + r.description.substring(0, 600) + '">' + r.programTitle.substring(0, 48) + "  " + r.time + '</span><div class="progress"><div class="determinate" style="width:' + r.elapsed + '%"></div></div></div></li><li><div class="divider"></div></li>'
}

function loadListeners() {
    $(".sidenav").sidenav(), $(".sidenav.sidenav-right").sidenav({
        menuWidth: 300,
        closeOnClick: !0,
        edge: "right"
    }), $(".collapsible").collapsible({
        accordion: !1
    }), $(".dropdown-trigger").dropdown(), $(".tooltipped").tooltip(), $("input:radio[name=channellinkradio]").change(function() {
        let e = $(this).val();
        doGo(e)		
    }), $("img").on("error", function() {
        $(this).attr("src", "img/play.png")
    })
}

function xmlTvParser(e) {
    async function t(e) {
        var t = $(e);
        return await a(t)
    }
    async function a(e) {
        let t = [],
            a = [],
            r = [];
        var n;
        $channel = e.find("channel"), $channel.each(function() {
            var e = $(this),
                t = e.find("display-name");
            a.push({
                id: e.attr("id"),
                name: t.text(),
                scheduleDays: []
            })
        }), $programme = await e.find("programme[start^='" + moment().utc().subtract(1, "hours").format("YYYYMMDDHH") + "'],programme[start^='" + moment().utc().format("YYYYMMDDHH") + "'],programme[start^='" + moment().utc().add(1, "hours").format("YYYYMMDDHH") + "']"), $programme.each(function() {
            let e = $(this),
                a = e.find("title"),
                l = e.find("desc"),
                r = e.find("category"),
                n = e.find("rating"),
                o = e.attr("channel"),
                s = a.text(),
                d = l.text(),
                c = r.text(),
                p = n.text(),
                u = moment(e.attr("start"), i),
                g = moment(e.attr("stop"), i),
                m = {
                    channelId: o,
                    category: c,
                    title: s,
                    desc: d,
                    rating: p,
                    beginTime: u,
                    endTime: g
                };
            t.push(m)
        });
        for (let e of Object.entries(t))
            for (let t of Object.entries(a))
                if (e[1].channelId === t[1].id && (t[1].scheduleDays.push(e[1]), void 0 !== t[1].name && t[1].scheduleDays.length > 0)) {
                    var o = $.grep(t[1].scheduleDays, function(e) {
                        return l.isBetween(e.beginTime, e.endTime, null, "[]")
                    });
                    if (n = o[0], void 0 !== n) {
                        var s = n.endTime.diff(n.beginTime, "minutes"),
                            d = l.diff(n.beginTime, "minutes"),
                            c = Math.round(100 * d / s);
                        r.push({
                            channelName: t[1].name,
                            programTitle: n.title,
                            description: n.desc,
                            iconSrc: n.iconSrc,
                            time: "(" + n.beginTime.format("HH:mm") + ")",
                            elapsed: c
                        })
                    }
                }
        return await r
    }
    if ("undefined" == typeof jQuery) throw new Error("jQuery is required!");
    if ("undefined" == typeof moment) throw new Error("moment.js is required!");
    var i = "YYYYMMDDHHmmss Z",
        l = moment();
    return t(e)
}
$(document).ready(function() {
    loadDataFromStorage()
});
let parserM3U = new m3uParser;
var lastId = 0,
    storageLogo = "";
if (localStorage.getItem("currentList")) {
    var currentListfromStorage = JSON.parse(localStorage.getItem("currentList")) || 0;
    storageLogo = JSON.parse(localStorage.getItem(currentListfromStorage)).logo_url
}
let storageChannels = localStorage.getItem("channels") || [],
    storageEpgCurrent = localStorage.getItem("epg_current") || [],
    search = "List";
var playLists = Object.keys(localStorage).filter(e => e.startsWith(search)).map(e => localStorage[e]) || [];
const loadxml = async e => {
        let t;
        try {
            return t = await $.get({
                beforeSend: function(e) {
                    e.overrideMimeType("text/xml; charset=UTF-8")
                },
                type: "GET",
                url: e,
                crossDomain: !0,
                dataType: "xml"
            }), t
        } catch (e) {
            return ""
        }
    },
    loadm3u = async e => {
        let t;
        try {
            return t = await $.get({
                type: "GET",
                url: e,
                crossDomain: !0
            }), t
        } catch (e) {
            return ""
        }
    },
    removeList = e => {
        $(e.target.parentNode.parentNode.parentNode.parentNode.parentNode).remove();
        let t = e.target.parentNode.parentNode.children[0].attributes[0].nodeValue,
            a = /deleteList(\d+)/gm,
            i = a.exec(t);
        playLists = playLists.filter(e => e.playlistId != i[1]), localStorage.removeItem("List" + i[1]), JSON.parse(localStorage.getItem("currentList")) === "List" + i[1] && localStorage.setItem("currentList", "")
    },
    addNewList = e => {
        localStorage.length > 0 && (lastId = localStorage.length);
        let t = lastId,
            a = '<li id="List' + t + ' "data-index="' + t + '"><ul class="collapsible"> <li> <div class=""><span id="deleteList' + t + '" style="cursor:pointer;"class="card-title grey-text text-darken-4"><i style="margin-top: 12px;" class="material-icons right">close</i></span><span class="card-title grey-text text-darken-4"><label><input name="listradio" type="radio"/><span><input type="text" id="inputListNameText' + t + '" value=""/></span></label><i style="cursor: pointer;" class="collapsible-header material-icons right">more_vert</i></span></div> <div class="collapsible-body"><ul><li> <div class="input-field"><label for="inputM3UText' + t + '">Enter Url for M3U List:</label> <input type="text" id="inputM3UText' + t + '" placeholder="" value=""/></div> </li><li> <div class="input-field"><label for="inputLogoText' + t + '">Enter Url for Logo:</label> <input type="text" id="inputLogoText' + t + '" placeholder="" value=""/><span></span> </div> </li> <li> <div class="input-field"><label for="inputEPGText' + t + '">Enter Url for EPG(XMLTV):</label> <input type="text" id="inputEPGText' + t + '" placeholder="" value=""/></div> </li> </ul></div> </li></ul> </li>';
        $("#channelList").append(a), $(".collapsible").collapsible(), $("#deleteList" + t).click(removeList);
        let i = {
            playlistId: t,
            listname: $("#inputListNameText" + t)[0].value,
            logo_url: $("#inputLogoText" + t)[0].value,
            epg_url: $("#inputEPGText" + t)[0].value,
            playlist_url: $("#inputM3UText" + t)[0].value
        };
        null !== i && (lastId++, localStorage.setItem("List" + t, JSON.stringify(i)), localStorage.getItem("currentList") || localStorage.setItem("currentList", JSON.stringify("List" + t))), $('[id^="inputListNameText"],[id^="inputM3UText"],[id^="inputLogoText"],[id^="inputEPGText"]').on("change", function(e) {
            e.preventDefault(), localStorage.setItem("List" + t, JSON.stringify({
                playlistId: t,
                listname: $("#inputListNameText" + t)[0].value,
                logo_url: $("#inputLogoText" + t)[0].value,
                epg_url: $("#inputEPGText" + t)[0].value,
                playlist_url: $("#inputM3UText" + t)[0].value
            }))
        }), $("input:radio[name=listradio]").change(async function(e) {
            let t = $(e.target.parentNode.parentNode.parentNode.parentNode),
                a = $(t[0].children[1].children[0])[0],
                i = a.children[0].children[0].children[1].value,
                l = (a.children[1].children[0].children[1].value, a.children[2].children[0].children[1].value);
            if ('""' !== l) {
                let e = await loadxml(l),
                    t = await new xmlTvParser(e);
                localStorage.setItem("epg_current", await JSON.stringify(t)), storageEpgCurrent = t
            } else localStorage.setItem("epg_current", []);
            if ('""' !== i) {
                let e = await loadm3u(i);
                if (parserM3U.addDataAndParse(e)) {
                    let e = parserM3U.getList();
                    channelsDataFromFile = groupChannels(e), localStorage.setItem("channels", JSON.stringify(channelsDataFromFile)), storageChannels = channelsDataFromFile, localStorage.setItem("currentList", JSON.stringify(t[0].childNodes[1].childNodes[0].id.replace("delete", "")))
                } else alert("The file you selected does not contain the M3U format, \n Choose another file please")
            } else localStorage.setItem("channels", []);
            0 !== storageChannels.length && loadCurrentList(storageChannels, storageEpgCurrent)
        })
    };
$("#addNewList").click(addNewList);