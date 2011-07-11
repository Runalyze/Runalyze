var dontScroll = false;

jQuery.noConflict();

jQuery(document).ready(function() {
	jQuery("div#overlay").click(function(){ closeOverlay(); });
	ready();
});

function closeOverlay() {
	jQuery("#wait").fadeIn();
	jQuery("#ajax, #overlay").fadeTo(400,0,function(){
		jQuery("#overlay, #ajax").hide();
		jQuery("#ajax").css({'width':'800px','margin-left':'-400px'});
		jQuery("#wait").fadeOut();
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
	jQuery("#r div.panel").each(function(){
		var id = jQuery(this).attr('id');
		jLoadLink(id, 'inc/class.Panel.display.php?id='+id.substring(6), null)
	})

	dontScroll = true;
	jQuery("div#daten h1 span.right a:first").click();
	jQuery("ul.tabs li.active a").click(function(){
		dontScroll = false;
	});
}

function jWindow() {
	jQuery("a.window").unbind("click").click(function(){
		jQuery("#wait").fadeIn();
		var href = jQuery(this).attr("href"),
			rel = jQuery(this).attr("rel");
		if (rel == "small") {
			jQuery("div#ajax").css({'width':'400px','margin-left':'-200px'});
		} else if (rel == "big") {
			jQuery("div#ajax").css({'width':'90%','margin-left':'-45%'});
		}

		if (href == "inc/tpl/window.search.php" && jQuery("#ajax h1:first").text() == "Suche") {
			jQuery("#ajax, #overlay").show(function(){
				jQuery("#ajax, #overlay").fadeTo(400,1,function(){
					jQuery("#wait").fadeOut();
				});
			});
		} else {
			jQuery("#ajax").load(href,function(){
				jQuery("#ajax, #overlay").show(function(){
					jQuery("#ajax, #overlay").fadeTo(400,1,function(){
						jWindow();
						jLinks();
						jChange();
						jToggle();
						jImgChange();
						jSubmit();
						jCalendar();
						jQuery("#wait").fadeOut();
					});
				});
			});
		}

		return false;
	});
}

function jSubmit() {
	jQuery("form.ajax").unbind("submit").submit(function(e){
		e.preventDefault();

		jQuery("#wait").fadeIn();
		jQuery(this).fadeOut();
		var form_id = jQuery(this).attr("id");

		if (form_id != "training" && form_id != "search" && form_id != "newtraining" && form_id != "sportler" && form_id != "config" && form_id != "schuhe" && form_id != "schuhe_edit") {
			window.alert(jQuery(this).serialize());
			jQuery("#wait").fadeOut();
			jQuery(this).fadeIn();
			return;
		}

		jQuery(this).children(":submit").attr("value", "Bitte warten");

		jQuery("#ajax").load(jQuery(this).attr("action"), jQuery(this).serializeArray(), function(){
			jSubmit();
			jQuery("#wait").fadeOut();
			jQuery(this).fadeIn();
			jQuery("#submit-info").fadeIn().delay(2000).fadeOut();

			if (form_id != "search" && form_id != "config")
				jReloadContent();

			ready();
         });
	});
}

function jLinks() {
	jQuery("a.ajax").unbind("click").click(function(){
		jLoadLink(jQuery(this).attr("target"), jQuery(this).attr("href"), jQuery(this).attr("rel"));
		return false;
	});
}

function jLoadLink(id, href, data) {
	id = '#'+id;
	jQuery("#wait").fadeIn();
	jQuery(id).fadeTo(400, 0.01, function(){
		jQuery(id).load(href, data, function(){
			jQuery(id).fadeTo(400, 1, function(){
				jQuery("#wait").fadeOut();
				ready();
			});
		});
	});
}

function jToggle() {
	jQuery(".toggle").unbind("click").click(function(){
		jQuery("#"+jQuery(this).attr("rel")).animate({opacity: 'toggle'});
	});  
}

function jChange() {
	jQuery("a.change").unbind("click").click(function(){
		var id = jQuery(this).attr("href"),
			target = "#"+jQuery(this).attr("target");
		jQuery("#wait").fadeIn();
		jQuery(target+" div.change").each(function(){
			if (jQuery(this).css("display") == "block")
				jQuery(this).fadeOut(400, function(){
					jQuery(target+" div#"+id).fadeTo(400, 1, function(){
						jQuery("#wait").fadeOut();
					});
				});
		});
		return false;
	});
}

function jImgChange() {
	jQuery("a.jImg").unbind("click").click(function(){
		var href = jQuery(this).attr("href"),
			rel = jQuery(this).attr("rel");
		jQuery("#wait").fadeIn();
		jQuery("img#"+rel).fadeOut(200,function(){
			jQuery("img#"+rel).attr("src",href).load(function(){
				jQuery(this).fadeIn(200);
			});
		});
		jQuery("#wait").fadeOut();

		return false;
	});
}

function jTabs() {
	jQuery("ul.tabs li").unbind("click").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery("ul.tabs li#tabs_back").hide();

		if (jQuery(this).attr("id") == "tabs_back") {
			jQuery("#tab_content").fadeTo(400, 0.01, function(){
				jQuery("#tab_content").html( jQuery("#tab_content_prev").html() );
				jQuery("#tab_content").fadeTo(400, 1, function(){
					jQuery("#wait").fadeOut();
					if (!dontScroll)
						jQuery.scrollTo(jQuery("ul.tabs"),800);
					ready();
				});
			});
		} else {
			var load = false,
				href = jQuery(this).find("a").attr("href"),
				rel = jQuery(this).find("a").attr("rel");
			jQuery("#wait").fadeIn();
			jQuery(this).addClass("active");
			jQuery("#tab_content").fadeTo(400, 0.01, function(){
				jQuery("#tab_content").load(href, function(){
					jQuery("#tab_content").fadeTo(400, 1, function(){
						jQuery("#wait").fadeOut();
						if (!dontScroll)
							jQuery.scrollTo(jQuery("ul.tabs"),800);
						ready();
					});
				});
			});
		}
		
		return false;
	});
}

function jTraining() {
	jQuery("#daten tr.training, a.training").unbind("click").click(function() {
		var id = jQuery(this).attr("rel");
		var href = 'inc/class.Training.display.php?id='+id;
		if (jQuery(this).is("a"))
			href = jQuery(this).attr("href");

		jQuery("#wait").fadeIn();
		jQuery("ul.tabs li").removeClass("active");
		jQuery("#tab_content").fadeTo(400, 0.01, function(){
			jQuery("ul.tabs li#tabs_back").show().addClass("active");
			jQuery("#tab_content_prev").html( jQuery("#tab_content").html() );
			jQuery("#tab_content").load(href, function(){
				jQuery("#tab_content").fadeTo(400, 1, function(){
					jQuery("#wait").fadeOut();
					jQuery.scrollTo(jQuery("#tab_content"),800);
					ready();
				});
			});
		});
		
		return false;
	});
}

function jPanelsConfig() {
	// Show config buttons on mouseover if panel is unclapped
	jQuery("#r .panel").unbind("hover").hover(function(){
		if (jQuery(this).find(".content").css("display") != "none")
			jQuery(this).find("div.config").fadeIn();
	}, function(){
		jQuery(this).find("div.config").fadeOut();
	});	
}

function jPanels() {
	jPanelsConfig();

	jQuery(".panel .clap").unbind("click").click(function(){
		jQuery("#wait").fadeIn();
		jQuery(this).closest(".panel").find(".content").toggle(400);
		jQuery.get("class.Panel.clap.php", { id: jQuery(this).attr("rel") }, function() {
			jPanelsConfig();
		});
		jQuery("#wait").fadeOut();
	});

	jQuery(".panel .config img.up").unbind("click").click(function(){
		jQuery("#wait").fadeIn();
		jQuery(this).closest(".panel").after(jQuery(this).closest(".panel").prev(".panel"));
		jQuery.get("inc/class.Panel.move.php", { mode: "up", id: jQuery(this).attr("rel") }, function() {
			jQuery("#wait").fadeOut();
		});
	});

	jQuery(".panel .config img.down").unbind("click").click(function(){
		jQuery("#wait").fadeIn();
		jQuery(this).closest(".panel").next(".panel").after(jQuery(this).closest(".panel"));
		jQuery.get("inc/class.Panel.move.php", { mode: "down", id: jQuery(this).attr("rel") }, function() {
			jQuery("#wait").fadeOut();
		});
	});
}

function jCalendar() {
	if ( !jQuery('#widgetCalendar').length )
		return;

	jQuery('#widgetCalendar').DatePicker({
		flat: true,
		format: 'd B Y',
		date: [new Date(), new Date()],
		calendars: 3,
		mode: 'range',
		starts: 1,
		onChange: function(formated) {
			jQuery('#widgetField span').get(0).innerHTML = formated.join(' - ');
		}
	});

	jQuery('#widgetCalendar').stop().animate({height: jQuery('#widgetCalendar div.datepicker').get(0).offsetHeight}, 500);

	jQuery('#widgetField>a').unbind('click').bind('click', function(){
		var text = jQuery('#widgetField span').get(0).innerHTML;
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
	jQuery('#widgetCalendar div.datepicker').css('position', 'absolute');
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