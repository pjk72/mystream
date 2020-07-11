<!doctype html>
<html>
<script type="text/javascript" src="source/jquery.js"></script>
<script>
	
	$(function () { 
		
		$("#lancia").click(function (){ LoadXML();});

		function LoadXML() {
			console.clear();
			console.log("Load...");
			console.time("Load");				
			try //Internet Explorer
			{
				xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
			}
			catch (e) {
				try //Firefox, Mozilla, Opera, etc.
				{
					xmlDoc = document.implementation.createDocument("", "", null);
				}
				catch (e) {
					alert(e.message);
					return;
				}
			}				
			xmlDoc.async = false;
			xmlDoc.load("it.xml?ReadForm");
			 console.timeEnd("Load");
			 Lista_Canali();			
		}

		function Lista_Canali() {
			console.log("Recupero Nomi...");
			console.time("Recupero_Nomi");
			ls_id = [];ls_canali = [];ls_icon=[];								
			var lista = xmlDoc.getElementsByTagName("channel");						  
				for (var i = 0; i < lista.length; i++) {				
					ls_id[i]=lista[i].getAttribute("id").toUpperCase();
					ls_canali[i] = lista[i].firstChild.text.toUpperCase();
					ls_icon[i] = lista[i].lastChild.getAttribute("src");					
				}								
			console.timeEnd("Recupero_Nomi");				
			console.log("Lista_Canali...");
			console.time("Lista_Canali");				
      $.ajax({
        type: "POST",
        url: "sql/sql_canali.php",
        async: true,
        cache: false,
        data: {
					ls_canali: ls_canali,
					ls_icon : ls_icon,
					ls_id : ls_id
					},
				success: function (msg) {											
					canali=JSON.parse(msg);
				//	console.log(canali);
					console.timeEnd("Lista_Canali");					
					Aggiorna_DB(canali);
				}			
			});				
		}
		
		function Aggiorna_DB(canali_filtrati) {			
			console.log("EPG...");
			console.time("EPG");
			var start=[];
			var stop=[];
			var channel=[];
			var title=[];
			var desc=[];
			var category=[];
			var credits=[];
			var last_channel="";
			var check="";
			var conta=-1;
			var canali = xmlDoc.getElementsByTagName("programme");			
			cl = canali.length;			
			for (var i = 0; i < cl; i++) {						
				check=canali[i].getAttribute("channel").toUpperCase();								
				if (canali_filtrati.indexOf(check) > -1) {  
	//				if (check !== last_channel) {				
						conta=conta+1;
	//					if (check !== last_channel) {console.clear();console.log(check,cl,conta)};
						start[conta]=canali[i].getAttribute("start");
						stop[conta]=canali[i].getAttribute("stop");				
						channel[conta]=canali[i].getAttribute("channel").toUpperCase();				
						title[conta]=canali[i].childNodes[0].text;
						try {desc[conta]=canali[i].childNodes[1].text;} catch(err) {desc[i]="";}
						try {
							if (canali[conta].childNodes[2].nodeName == 'category') { 
								category[conta]=canali[i].childNodes[2].text; 
							}
							else if (canali[conta].childNodes[2].nodeName == 'credits') { 								
								category[conta]=canali[i].childNodes[3].text; 
							}				
						}
						catch (err) {category[conta]="";}
						last_channel=check;
		//		} 
				} 
				// else { 
				// 	supera = i;
				// 	do { supera=supera+1; }
				// 	while (check == canali[supera].getAttribute("channel").toUpperCase() && supera <= cl ) 
				// 	i = supera;		
				// }
			}		
			console.timeEnd("EPG")
			console.log("DETAIL...");					
			console.time("DETAIL");					
      $.ajax({
        type: "POST",
        url: "sql/sql_epg.php",
        async: true,
        cache: false,
        data: {
					start: start,
					stop:stop,					
					channel:channel,
					title:title,
					desc:desc,
					category:category,
					},
        	success: function (msg) {			
						console.timeEnd("DETAIL");
						console.log(JSON.parse(msg));
						console.log(msg);
				}			
			});		
		}
	});
</script>
<body>

<button id="lancia">Click me</button>

</html>