var dontScroll = false;

$(document).ready(function() {
	$("div#overlay").click(function(){ closeOverlay(); });
	ready();
});

function closeOverlay() {
	$("#wait").fadeIn();
	$("#ajax, #overlay").fadeTo(400,0,function(){
		$("#overlay, #ajax").hide();
		$("#ajax").css({'width':'800px','margin-left':'-400px'});
		$("#wait").fadeOut();
	});
}

function ready() {
	jWindow();
	jSubmit();
	jToggle();
	jLinks();
	jTabs();
	jChange();
	jTraining();
	jImgChange();
	jPanels();
}

function jReloadContent() {
	$("#r div.panel").each(function(){
		var id = $(this).attr('id');
		jLoadLink(id, 'inc/class.Panel.display.php?id='+id.substring(6), null)
	})

	dontScroll = true;
	$("div#daten h1 span.right a:first").click();
	$("ul.tabs li.active a").click(function(){
		dontScroll = false;
	});
}

function jWindow() {
	$("a.window").unbind("click").click(function(){
		$("#wait").fadeIn();
		var href = $(this).attr("href"),
			rel = $(this).attr("rel");
		if (rel == "small") {
			$("div#ajax").css({'width':'400px','margin-left':'-200px'});
		} else if (rel == "big") {
			$("div#ajax").css({'width':'90%','margin-left':'-45%'});
		}

		if (href == "inc/tpl/window.search.php" && $("#ajax h1:first").text() == "Suche") {
			$("#ajax, #overlay").show(function(){
				$("#ajax, #overlay").fadeTo(400,1,function(){
					$("#wait").fadeOut();
				});
			});
		} else {
			$("#ajax").load(href,function(){
				$("#ajax, #overlay").show(function(){
					$("#ajax, #overlay").fadeTo(400,1,function(){
						jWindow();
						jLinks();
						jChange();
						jToggle();
						jImgChange();
						jSubmit();
						jCalendar();
						$("#wait").fadeOut();
					});
				});
			});
		}

		return false;
	});
}

function jSubmit() {
	$("form.ajax").unbind("submit").submit(function(e){
		e.preventDefault();

		$("#wait").fadeIn();
		$(this).fadeOut();
		var form_id = $(this).attr("id");

		if (form_id != "training" && form_id != "search" && form_id != "newtraining" && form_id != "sportler" && form_id != "config" && form_id != "schuhe" && form_id != "schuhe_edit") {
			window.alert($(this).serialize());
			$("#wait").fadeOut();
			$(this).fadeIn();
			return;
		}

		$(this).children(":submit").attr("value", "Bitte warten");

		$("#ajax").load($(this).attr("action"), $(this).serializeArray(), function(){
			jSubmit();
			$("#wait").fadeOut();
			$(this).fadeIn();
			$("#submit-info").fadeIn().delay(2000).fadeOut();

			if (form_id != "search" && form_id != "config")
				jReloadContent();

			ready();
         });
	});
}

function jLinks() {
	$("a.ajax").unbind("click").click(function(){
		jLoadLink($(this).attr("target"), $(this).attr("href"), $(this).attr("rel"));
		return false;
	});
}

function jLoadLink(id, href, data) {
	id = '#'+id;
	$("#wait").fadeIn();
	$(id).fadeTo(400, 0.01, function(){
		$(id).load(href, data, function(){
			$(id).fadeTo(400, 1, function(){
				$("#wait").fadeOut();
				ready();
			});
		});
	});
}

function jToggle() {
	$(".toggle").unbind("click").click(function(){
		$("#"+$(this).attr("rel")).animate({opacity: 'toggle'});
	});  
}

function jChange() {
	$("a.change").unbind("click").click(function(){
		var id = $(this).attr("href"),
			target = "#"+$(this).attr("target");
		$("#wait").fadeIn();
		$(target+" div.change").each(function(){
			if ($(this).css("display") == "block")
				$(this).fadeOut(400, function(){
					$(target+" div#"+id).fadeTo(400, 1, function(){
						$("#wait").fadeOut();
					});
				});
		});
		return false;
	});
}

function jImgChange() {
	$("a.jImg").unbind("click").click(function(){
		var href = $(this).attr("href"),
			rel = $(this).attr("rel");
		$("#wait").fadeIn();
		$("img#"+rel).fadeOut(200,function(){
			$("img#"+rel).attr("src",href).load(function(){
				$(this).fadeIn(200);
			});
		});
		$("#wait").fadeOut();

		return false;
	});
}

function jTabs() {
	$("ul.tabs li").unbind("click").click(function() {
		$("ul.tabs li").removeClass("active");
		$("ul.tabs li#tabs_back").hide();

		if ($(this).attr("id") == "tabs_back") {
			$("#tab_content").fadeTo(400, 0.01, function(){
				$("#tab_content").html( $("#tab_content_prev").html() );
				$("#tab_content").fadeTo(400, 1, function(){
					$("#wait").fadeOut();
					if (!dontScroll)
						$.scrollTo($("ul.tabs"),800);
					ready();
				});
			});
		} else {
			var load = false,
				href = $(this).find("a").attr("href"),
				rel = $(this).find("a").attr("rel");
			$("#wait").fadeIn();
			$(this).addClass("active");
			$("#tab_content").fadeTo(400, 0.01, function(){
				$("#tab_content").load(href, function(){
					$("#tab_content").fadeTo(400, 1, function(){
						$("#wait").fadeOut();
						if (!dontScroll)
							$.scrollTo($("ul.tabs"),800);
						ready();
					});
				});
			});
		}
		
		return false;
	});
}

function jTraining() {
	$("#daten tr.training, a.training").unbind("click").click(function() {
		var id = $(this).attr("rel");
		var href = 'inc/class.Training.display.php?id='+id;
		if ($(this).is("a"))
			href = $(this).attr("href");

		$("#wait").fadeIn();
		$("ul.tabs li").removeClass("active");
		$("#tab_content").fadeTo(400, 0.01, function(){
			$("ul.tabs li#tabs_back").show().addClass("active");
			$("#tab_content_prev").html( $("#tab_content").html() );
			$("#tab_content").load(href, function(){
				$("#tab_content").fadeTo(400, 1, function(){
					$("#wait").fadeOut();
					$.scrollTo($("#tab_content"),800);
					ready();
				});
			});
		});
		
		return false;
	});
}

function jPanelsConfig() {
	// Show config buttons on mouseover if panel is unclapped
	$("#r .panel").unbind("hover").hover(function(){
		if ($(this).find(".content").css("display") != "none")
			$(this).find("div.config").fadeIn();
	}, function(){
		$(this).find("div.config").fadeOut();
	});	
}

function jPanels() {
	jPanelsConfig();

	$(".panel .clap").unbind("click").click(function(){
		$("#wait").fadeIn();
		$(this).closest(".panel").find(".content").toggle(400);
		$.get("class.Panel.clap.php", { id: $(this).attr("rel") }, function() {
			jPanelsConfig();
		});
		$("#wait").fadeOut();
	});

	$(".panel .config img.up").unbind("click").click(function(){
		$("#wait").fadeIn();
		$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
		$.get("inc/class.Panel.move.php", { mode: "up", id: $(this).attr("rel") }, function() {
			$("#wait").fadeOut();
		});
	});

	$(".panel .config img.down").unbind("click").click(function(){
		$("#wait").fadeIn();
		$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
		$.get("inc/class.Panel.move.php", { mode: "down", id: $(this).attr("rel") }, function() {
			$("#wait").fadeOut();
		});
	});
}

function jCalendar() {
	if ( !$('#widgetCalendar').length )
		return;

	$('#widgetCalendar').DatePicker({
		flat: true,
		format: 'd B Y',
		date: [new Date(), new Date()],
		calendars: 3,
		mode: 'range',
		starts: 1,
		onChange: function(formated) {
			$('#widgetField span').get(0).innerHTML = formated.join(' - ');
		}
	});

	$('#widgetCalendar').stop().animate({height: $('#widgetCalendar div.datepicker').get(0).offsetHeight}, 500);

	$('#widgetField>a').unbind('click').bind('click', function(){
		var text = $('#widgetField span').get(0).innerHTML;
		if (text.substring(0,1) == "W")
			return false;

		var pos   = text.indexOf('-');
		var start = text.substring(0, pos-1);
		var end   = text.substring(pos+1);
		start = Math.round(Date.parse(start)/1000);
		end   = Math.round(Date.parse(end)/1000) + 23*60*60+59*60+50;
		jLoadLink('daten', 'inc/class.DataBrowser.display.php?start='+start+'&end='+end, null);
		closeOverlay();
		return false;
	});
	$('#widgetCalendar div.datepicker').css('position', 'absolute');
}





function createObject() {
	var request_type;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer") request_type = new ActiveXObject("Microsoft.XMLHTTP");
	else request_type = new XMLHttpRequest();
	return request_type;
}

var http = createObject();

function daten(heute, start, end) {
	wait(1);
	nocache = Math.random();
	http.open('get', 'lib/datentabelle.php?heute='+heute+'&start='+start+'&ende='+end+'&nocache = '+nocache);
	http.onreadystatechange = datenReply;
	http.send(null);
}

function datenReply() {
	if (http.readyState == 4) {
		wait(0);
		document.getElementById('daten').style.display = "block";
		e = document.getElementById('daten_results');
		e.innerHTML = http.responseText;
		e.style.display = "block";
		ready();
	}
}

function submit_form_training(id) {
	wait(1);
	var f=document.forms.train;
	var sendDaten='';
	var j;
	for (var i=0; i<f.length; i++) {
		j = f.elements[i];
		if (j.type == "checkbox") {
			if (j.checked == true) sendDaten += f[i].name+'=1';
			else sendDaten += f[i].name+'=0';
		}
		else sendDaten += f[i].name+'='+encodeURIComponent(f[i].value);
		if ( i < f.length-1 ) sendDaten += '&';
	}
	nocache = Math.random();
	http.open('POST', 'lib/form_training.php?submit=true&id='+id+'&nocache = '+nocache);
	http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", sendDaten.length);
	http.setRequestHeader("Connection", "close");
	http.send(sendDaten);
	http.onreadystatechange = seiteReply;
}

function submit_form_suche() {
	wait(1);
	var f = document.forms.suche;
	var sendDaten = '';
	var j;
	for (var i = 0; i < f.length; i++) {
		j = f.elements[i];
		if (j.type == "checkbox") {
			if (j.checked == true) sendDaten += f[i].name+'=1';
			else sendDaten += f[i].name+'=0';
		}
		else if (f[i].multiple == true) {
			var arrayDaten = '';
			var x = f[i].options;
			for (var a = 0; a < x.length; a++)
				if (x[a].selected) {
					arrayDaten += x[a].value+',';
				}
			sendDaten += f[i].name.substr(0,f[i].name.length-2)+'='+arrayDaten.substr(0,arrayDaten.length-1);
		}
		else sendDaten += f[i].name+'='+encodeURIComponent(f[i].value);
		if ( i < f.length-1 )
			sendDaten += '&';
	}
	nocache = Math.random();
	http.open('POST', 'suche.php?submit=true&nocache = '+nocache);
	http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", sendDaten.length);
	http.setRequestHeader("Connection", "close");
	http.send(sendDaten);
	http.onreadystatechange = sucheReply;
}

function submit_suche(sendDaten) {
	wait(1);
	http.open('POST', 'suche.php');
	http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", sendDaten.length);
	http.setRequestHeader("Connection", "close");
	http.send(sendDaten);
	http.onreadystatechange = sucheReply;
}

function sucheReply() {
	if (http.readyState == 4) {
		wait(0);
		document.getElementById('sucher').style.display='block';
		document.getElementById('suche').style.display='block';
		document.getElementById('suche').innerHTML = http.responseText;
		ready();
	}
}

function closeSuche() {
	document.getElementById('sucher').style.display='none';
	document.getElementById('suche').style.display='none';
}

function wait(mode) {
	if (mode == 1) document.getElementById('wait').style.display = "block";
	else document.getElementById('wait').style.display = "none";
}