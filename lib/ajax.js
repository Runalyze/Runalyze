var dontScroll = false;
var fadeSpeed = 200;
var fadeSpeedImg = 100;

$(document).ready(function() {
	$("div#overlay").click(function(){ closeOverlay(); });
	jTablesorterAddParser();
	ready();
});

$(window).resize(function() {
	jCheckForAjaxWindowSize();
})

function jCheckForAjaxWindowSize() {
	if ($("body").width() == 1000)
		$("#ajax").addClass('tooBig');
	else
		$("#ajax").removeClass('tooBig');
}


function getXmlFromIFrame(xml) {
	var href = $("form#newtraining").attr("action");

	$("#ajax").html('');
	
	$("#ajax").load(href, {'data': xml}, function(){
		$("#ajax").fadeTo(fadeSpeed, 1, function(){
			$("#wait").fadeOut();
			ready();
		});
	});
}

function closeOverlay() {
	$("#wait").fadeIn();
	$("#ajax, #overlay").fadeTo(fadeSpeed, 0, function(){
		$("#overlay, #ajax").hide();
		$("#ajax").removeClass('smallWin').removeClass('bigWin');
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
	jCheckForAjaxWindowSize();
	jCalendar();
	jTablesorter();
}

dontScroll = true;

function jReloadContent() {
	$("div#daten h1 span.right a:first").trigger('click');
	$("ul.tabs li.active a").trigger('click');

	$("#r div.panel").each(function(){
		var id = $(this).attr('id');
		jLoadLink(id, 'call/call.Plugin.display.php?id='+id.substring(6), null)
	})
}

function jWindow() {
	$("a.window").unbind("click").click(function(){
		$("#wait").fadeIn();
		var href = $(this).attr("href"),
			rel = $(this).attr("rel");
		if (rel == "small") {
			$("#ajax").addClass('smallWin');
		} else if (rel == "big") {
			$("#ajax").addClass('bigWin');
		}

		if (href == "call/window.search.php" && $("#ajax h1:first").text() == "Suche") {
			$("#ajax, #overlay").show(function(){
				$("#ajax, #overlay").fadeTo(fadeSpeed, 1, function(){
					$("#wait").fadeOut();
				});
			});
		} else {
			$("#ajax").load(href,function(){
				$("#ajax, #overlay").show(function(){
					$("#ajax, #overlay").fadeTo(fadeSpeed, 1, function(){
						jWindow();
						jSubmit();
						jToggle();
						jLinks();
						jChange();
						jTraining();
						jImgChange();
						jTablesorter();
						jCheckForAjaxWindowSize();
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
		var form_id = $(this).attr("id");

		$("#wait").fadeIn();

		if (form_id == "search") {
			$("#searchResult").fadeOut();
			$("#searchResult").load($(this).attr("action")+'?pager=true', $(this).serializeArray(), function(){
				$("#searchResult").fadeIn();
				$("#wait").fadeOut();
				ready();
			});
			return;
		}

		$(this).fadeOut();

		if (form_id != "training" && form_id != "search" && form_id != "tcxUpload" && form_id != "newtraining" && form_id != "sportler" && form_id != "config" && form_id != "pluginconfig" && form_id != "schuhe" && form_id != "schuhe_edit") {
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

			if (form_id != "search" && form_id != "tcxUpload")
				jReloadContent();

			ready();
         });
	});
}

function jLinks() {
	$("a.ajax").unbind("click").click(function(e){
		e.preventDefault();
		jLoadLink($(this).attr("target"), $(this).attr("href"), $(this).attr("rel"));
		return false;
	});
}

function jLoadLink(id, href, data) {
	id = '#'+id;
	$("#wait").fadeIn();
	$(id).fadeTo(fadeSpeed, 0.01, function(){
		$(id).load(href, data, function(){
			$(id).fadeTo(fadeSpeed, 1, function(){
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
		var id = $(this).attr("href").split('#').pop(),
			target = "#"+$(this).attr("target");
		$("#wait").fadeIn();
		$(target+" div.change").each(function(){
			if ($(this).css("display") == "block")
				$(this).fadeOut(fadeSpeed, function(){
					$(target+" div#"+id).fadeTo(fadeSpeed, 1, function(){
						$(target+" div#"+id+" div.change:first-child").show();
						$("#wait").fadeOut();
					});
				});
		});
		return false;
	});
}

function jImgChange() {
	$("a.jImg").unbind("click").click(function(){
		var href = $(this).attr("href").split('#').pop(),
			rel = $(this).attr("rel");
		$("#wait").fadeIn();
		$("img#"+rel).fadeOut(fadeSpeedImg, function(){
			$("img#"+rel).attr("src",href).load(function(){
				$(this).fadeIn(fadeSpeedImg);
			});
		});
		$("#wait").fadeOut();

		return false;
	});
}

function jTabs() {
	$("ul.tabs li").unbind("click").click(function() {
		$("tr.training").removeClass('highlight');

		$("ul.tabs li").removeClass("active");
		$("ul.tabs li#tabs_back").hide();

		if ($(this).attr("id") == "tabs_back") {
			$("#tab_content").fadeTo(fadeSpeed, 0.01, function(){
				$("#tab_content").html( $("#tab_content_prev").html() );
				$("#tab_content").fadeTo(fadeSpeed, 1, function(){
					$("#wait").fadeOut();
					ready();
				});
			});
		} else {
			var load = false,
				href = $(this).find("a").attr("href"),
				rel = $(this).find("a").attr("rel");
			$("#wait").fadeIn();
			$(this).addClass("active");
			$("#tab_content").fadeTo(fadeSpeed, 0.01, function(){
				$("#tab_content").load(href, function(){
					$("#tab_content").fadeTo(fadeSpeed, 1, function(){
						$("#wait").fadeOut();
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
		$("tr.training").removeClass('highlight');

		var id = $(this).attr("rel");
		var href = 'call/call.Training.display.php?id='+id;
		if ($(this).is("a"))
			href = $(this).attr("href");
		else
			$(this).addClass('highlight');

		$("#wait").fadeIn();
		$("ul.tabs li").removeClass("active");
		$("#tab_content").fadeTo(fadeSpeed, 0.01, function(){
			$("ul.tabs li#tabs_back").show().addClass("active");
			$("#tab_content_prev").html( $("#tab_content").html() );
			$("#tab_content").load(href, function(){
				$("#tab_content").fadeTo(fadeSpeed, 1, function(){
					$("#wait").fadeOut();
					$.scrollTo($("#tab_content"),800);
					ready();
					jRoundHighlighter();
				});
			});
		});
		
		return false;
	});
}

function jRoundHighlighter() {
	$("#trainingRounds tr").click(function() {
		$(this).toggleClass('highlight');
	});
}

function jPanelsConfig() {
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
		$(this).closest(".panel").find(".content").toggle(fadeSpeed);
		$.get("call/call.PluginPanel.clap.php", { id: $(this).attr("rel") }, function() {
			jPanelsConfig();
		});
		$("#wait").fadeOut();
	});

	$(".panel .config img.up").unbind("click").click(function(){
		$("#wait").fadeIn();
		$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
		$.get("call/call.PluginPanel.move.php", { mode: "up", id: $(this).attr("rel") }, function() {
			$("#wait").fadeOut();
		});
	});

	$(".panel .config img.down").unbind("click").click(function(){
		$("#wait").fadeIn();
		$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
		$.get("call/call.PluginPanel.move.php", { mode: "down", id: $(this).attr("rel") }, function() {
			$("#wait").fadeOut();
		});
	});
}

function jCalendar() {
	$('#widgetCalendar').DatePicker({
		flat: true,
		format: 'd B Y',
		date: [new Date(), new Date()],
		calendars: 3,
		mode: 'range',
		starts: 1,
		onChange: function(formated) {
			$('#calendarResult').get(0).innerHTML = formated.join(' - ');
		}
	});

	$('#calendar').hide();

	$('#calendarLink').unbind('click').bind('click', function(){
		$('#calendar').toggle();
	});

	$('#calendarSubmit').unbind('click').bind('click', function(){
		var text = $('#calendarResult').get(0).innerHTML;
		if (text.substring(0,1) == "W")
			return false;

		var pos   = text.indexOf('-');
		var start = text.substring(0, pos-1);
		var end   = text.substring(pos+1);
		start = Math.round(Date.parse(start)/1000);
		end   = Math.round(Date.parse(end)/1000) + 23*60*60+59*60+50;
		jLoadLink('daten', 'call/call.DataBrowser.display.php?start='+start+'&end='+end, null);
	});
}

function jTablesorter() {
	$("table.sortable").tablesorter({
		textExtraction: jTablesorterTextExtraction(),
		widgets:['zebra'],
		widgetZebra:{css:["a1","a2"]}
	});
}

function jTablesorterTextExtraction(node) {
	return $.trim($(node).text());
}

function jTablesorterAddParser() {
	$.tablesorter.addParser({
		id: 'germandate',
		is: function(s) {
			return false;
		},
		format: function(s) {
			var a = s.split('.');
			a[1] = a[1].replace(/^[0]+/g,"");
			return new Date(a.reverse().join("/")).getTime();
		},
		type: 'numeric'
	});

	$.tablesorter.addParser({
		id: 'distance',
		is: function(s) {
			return false;
		},
		format: function(s) {
			return s.replace(/[ km]/g, '').replace(/[,]/g, '.');
		},
		type: 'numeric'
	});

	$.tablesorter.addParser({
		id: 'temperature',
		is: function(s) {
			return false;
		},
		format: function(s) {
			if (isNaN(parseFloat(s)) || isFinite(s))
				return -99;
			return s.replace(/[ °C]/g, '');
		},
		type: 'numeric'
	});

	$.tablesorter.addParser({
		id: 'resulttime',
		is: function(s) {
			return false;
		},
		format: function(s) {
			var h,m,s;
			var ms  = s.split(',');
			var hms = ms[0].split(':');
			ms = (ms.length > 1) ? ms[1].replace(/[s]/g, '') : 0;
			if (hms.length == 3) {
				h = hms[0]; m = hms[1]; s = hms[2];
			} else if (hms.length == 2) {
				h = 0;      m = hms[0]; s = hms[1];
			} else {
				h = 0;      m = 0;      s = hms[0];
			}

			return h*60*60 + m*60 + s + ms/100;
		},
		type: 'numeric'
	});
}