/*
 * JS-library for Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var Runalyze = {
			options: {
				sharedView: false, // Only to hide links and requests for performance reasons - don't trust JS!
				fadeSpeed: 200,
				useTooltip: false,
				useRoundHighlighter: true,
				overlayCloseString: '<div id="close" class="black-rounded-icon" onclick="Runalyze.closeOverlay()"><i class="fa fa-fw fa-times"></i></div>',
				urlForStat: 'call/call.Plugin.display.php?id=',
				urlForPanel: 'call/call.Plugin.display.php?id=',
				urlForPanelClap: 'call/call.PluginPanel.clap.php',
				urlForPanelMove: 'call/call.PluginPanel.move.php',
				tabsCssId: 'statistics-nav',
				dontReloadForConfigFlag: 'dont-reload-for-config',
				dontReloadForTrainingFlag: 'dont-reload-for-training'
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			changeConfig: function(key,value,add) {
				if (!this.options.sharedView) {
					var url = 'call/ajax.change.Config.php?key='+key+'&value='+value;

					if (typeof add !== "undefined")
						url = url+'&add';

					$.ajax(url);
				}
			},

			init: function(opt) {
				RunalyzeLog.init();

				this.setOptions(opt);
				this.initProperties();
				this.setupAjax();

				this.initOverlay();
				this.initResizer();
				this.initAjaxLinks();
				this.initTabNavigation();
				this.initPanelsConfig();
				this.initTooltip();
				this.initToolbars();
				this.initToggle();
				this.initChangeDiv();
				this.initCalendarLink();
				this.initFormulars();

				RunalyzeTablesorter.init();

				$(document).trigger("createFlot");

				return this;
			},

			initForOverlay: function() {
				this.initAjaxLinks();
				this.initTooltip();
				this.initToolbars();
				this.initToggle();
				this.initChangeDiv();
				this.initFormulars();
				RunalyzeTablesorter.reInit();

				$(document).trigger("createFlot");
			},

			initForDivLoad: function() {
				this.initAjaxLinks();
				this.initTooltip();
				this.initToolbars();
				this.initRoundHighlighter();
				this.initToggle();
				this.initChangeDiv();
				this.initCalendarLink();
				this.initFormulars();
				this.initPanelsConfig();

				RunalyzePlot.resizeTrainingCharts();
				RunalyzeTablesorter.reInit();

				$(document).trigger("createFlot");
			},

			initProperties: function() {
				this.body       = $("body");
				this.tabs       = $("#"+this.options.tabsCssId+" li");
				this.tabContent = $("#statistics-inner");
				this.currentTabContentUrl = $("#"+this.options.tabsCssId+" li:first a").attr('href');
			},

			setupAjax: function() {
				$.ajaxSetup({
					error: function(x,e) {
						if ("status" in x) {
							if (x.status == 404) {
								RunalyzeLog.add("ERROR", "There was an error 404: The requested resource could not be found.");
							} else if (x.status == 500) {
								RunalyzeLog.add("ERROR", "There was an error 500: Internal Server Error.");
							}
						}
					}
				});
			},

			initOverlay: function() {
				if ($("#container, #statistics-inner, #ajax").length == 0) {
					$("body > :not(#flot-loader, #copy, #error-toolbar, script)").wrapAll('<div id="ajax" class="panel"></div>');
					this.body.prepend('<div id="overlay"></div>');
					$("#ajax, #overlay").show().fadeTo(this.options.fadeSpeed, 1);
					this.initForOverlay();
				} else {
					this.body.prepend('<div id="overlay"></div><div id="ajax" class="panel"></div>');					
				}

				this.overlay    = $("#overlay");
				this.ajax       = $("#ajax");

				this.overlay.click(function(){ Runalyze.closeOverlay(); });
			},

			initResizer: function() {
				$(window).resize(function() {
					if (typeof RunalyzePlot != "undefined") {
						RunalyzePlot.resizeTrainingCharts();
						RunalyzePlot.setFullscreenSize();
					}

					if (typeof RunalyzeGMap != "undefined")
						RunalyzeGMap.resize();
				});
			},

			initAjaxLinks: function() {
				$("a.ajax").unbind("click").click(function(e){
					e.preventDefault();

					if ($(this).attr("href") == "#")
						return false;

					if ($(this).attr("target") == "statistics-inner")
						Runalyze.currentTabContentUrl = $(this).attr("href");

					$("#"+$(this).attr("target")).loadDiv($(this).attr("href"), $(this).attr("data-size"));
					return false;
				});

				$("a.window").unbind("click").click(function(e){
					e.preventDefault();
					var href = $(this).attr("href"),
						size = $(this).attr("data-size");

					if (size == "small") {
						Runalyze.ajax.removeClass('big-window').addClass('small-window');
					} else if (size == "big") {
						Runalyze.ajax.removeClass('small-window').addClass('big-window');
					}

					Runalyze.loadOverlay(href);
					return false;
				});

				$("a.training").unbind("click").click(function(e){
					e.preventDefault();
					Runalyze.loadTraining($(this).attr("rel"));
					return false;
				});
			},

			initTabNavigation: function() {
				this.tabs.unbind("click").click(function(e) {
					e.preventDefault();

					$("tr.training").removeClass('highlight');
					Runalyze.tabs.removeClass("active");
					$(this).addClass("active");

					Runalyze.tabContent.loadDiv($(this).find("a").attr("href"), $(this).find("a").attr("data-size"));
					Runalyze.currentTabContentUrl = $(this).find("a").attr("href");
					
					return false;
				});
			},

			initPanelsConfig: function() {
				this.initPanelsConfigLinks();
				this.initPanelsConfigMoveLinks();

				return this;
			},

			initPanelsConfigLinks: function() {
				$("#panels .clap").unbind("click").click(function(){
					$(this).closest(".panel").find(".panel-content").toggle(Runalyze.options.fadeSpeed,function(){
						$(this).closest(".content").find(".flot").each(function(){
							RunalyzePlot.resize($(this).attr('id'));
						});
					});
					$.get(Runalyze.options.urlForPanelClap, { id: $(this).attr("rel") });
				});
			},

			initPanelsConfigMoveLinks: function() {
				$("#panels .up, #panels .down").unbind("click").click(function(){
					if ($(this).hasClass("up")) {
						$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
						$.get(Runalyze.options.urlForPanelMove, { mode: "up", id: $(this).attr("rel") } );
					} else {
						$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
						$.get(Runalyze.options.urlForPanelMove, { mode: "down", id: $(this).attr("rel") });
					}
				});
			},

			initTooltip: function() {
				if (!this.options.useTooltip)
					return this;

				$(".tooltip").remove(); // Remove old tooltips
				$("[rel=tooltip].atLeft").tooltip({animation: true, placement: 'left'});
				$("[rel=tooltip].atRight").tooltip({animation: true, placement: 'right'});
				$("[rel=tooltip]:not(.atLeft):not(.atRight)").tooltip({animation: true});

				return this;
			},

			initRoundHighlighter: function() {
				if (this.options.useRoundHighlighter)
					$("#trainingRounds tr").click(function() { $(this).toggleClass('highlight'); });

				return this;
			},

			initToggle: function() {
				$(".toggle").unbind("click").click(function(e){
					e.preventDefault();
					$("#"+$(this).attr("rel")).animate({opacity: 'toggle'});
				}); 
			},

			initToolbars: function() {
				$(".toolbar-opener").unbind("click").click(function(){
					$(this).parent().parent().toggleClass('open');
				});
			},

			initChangeDiv: function() {
				$("a.change").each(function(){
					if ($("a.change[target="+$(this).attr("target")+"].triggered").length == 0)
						$("a.change[target="+$(this).attr("target")+"]:first").addClass('triggered').parent().addClass('triggered');
					else
						$("a.change[target="+$(this).attr("target")+"].triggered").parent().addClass('triggered');
				});

				$("a.change").unbind("click").click(function(e){
					e.preventDefault();

					$("a.change[target="+$(this).attr("target")+"]").removeClass('triggered').parent().removeClass('triggered');
					$(this).addClass('triggered').parent().addClass('triggered');

					var  target = "#"+$(this).attr("target"),
						$target = $(target),
						$oldDiv = $(target+" > .change:visible, " + target + " > .panel-content > .change:visible, " + target + " > .statistics-container > .change:visible"),
						$newDiv = $("#"+ $(this).attr("href").split('#').pop());

					$target.addClass('loading');
					$oldDiv.fadeOut(Runalyze.options.fadeSpeed, function(){
						$newDiv.fadeTo(Runalyze.options.fadeSpeed, 1, function(){
							$target.hide().removeClass('loading').fadeIn();
							$(document).trigger("createFlot");
							RunalyzePlot.resizeAll();
						});
					})

					return false;
				});
			},

			initCalendarLink: function() {
				$('#calendar-link').unbind('click').bind('click', function(){
					$('#data-browser-calendar').toggle();
					Runalyze.initCalendar();
				});
			},

			initCalendar: function() {
				if ($("#widget-calendar").text().trim().length > 0)
					return this;

				$('#widget-calendar').DatePicker({
					flat: true,
					format: 'd B Y',
					date: [new Date(parseInt($("#calendar-start").val())), new Date(parseInt($("#calendar-end").val()))],
					calendars: 3,
					mode: 'range',
					starts: 1
				});

				$('#calendar-submit').unbind('click').bind('click', function(){
					var dates = $("#widget-calendar").DatePickerGetDate(),
						start = Math.round(dates[0].getTime()/1000),
						end = Math.round(dates[1].getTime()/1000);
					$("#data-browser-inner").loadDiv('call/call.DataBrowser.display.php?start='+start+'&end='+end);
				});

				return this;
			},

			initFormulars: function() {
				$(".chzn-select").chosen();
				$(".pick-a-date").each(function(){
					var $t = $(this);

					$t.DatePicker({
						//eventName: 'click focusin',
						format: 'd.m.Y',
						date: $t.val(),
						current: $t.val(),
						calendars: 1,
						starts: 1,
						position: 'bottom',
						mode: 'single',
						onBeforeShow: function(){ $t.DatePickerSetDate($t.val(), true); },
						onChange: function(formated, dates){ $t.val(formated); $t.DatePickerHide(); }
					});
				});

				// Warning: Does only work for formulars in #ajax
				$("#ajax form.ajax").unbind("submit").submit(function(e){
					e.preventDefault();

					if ($(this).children(":submit").hasClass('debug')) {
						window.alert($(this).serialize());
						return false;
					}

					var formID = $(this).attr("id"),
						noreload = $(this).hasClass('no-automatic-reload'),
						data = $(this).serializeArray();

					data.push({name:'submit',value:'submit'});

					if (formID == "search" && $("form.ajax input[name=send_to_multiEditor]:checked").length == 0) {
						$("#searchResult").loadDiv($(this).attr("action")+'?pager=true', data);
						return false;
					}

					Runalyze.ajax.addClass('loading');
					Runalyze.ajax.load($(this).attr("action"), data, function(response, status, xhr){
						if (status == "error")
							Runalyze.ajax.html('<p class="error">There was an error: '+ xhr.status +' '+ xhr.statusText +'</p>');

						Runalyze.initForOverlay();

						$("#submit-info").fadeIn().delay(4000).fadeOut();
						$(this).prepend(Runalyze.options.overlayCloseString).removeClass('loading');

						if (formID != "search" && formID != "tcxUpload" && !noreload)
							Runalyze.reloadContent();
					});

					return false;
				});
			},



			loadOverlay: function(url) {
				this.ajax.css({'margin-top':($(window).scrollTop()+25-41)+'px'});

				if (url == "call/window.search.php" && $("#ajax h1:first").text() == "Suche") {
					$("#ajax, #overlay").show().fadeTo(this.options.fadeSpeed, 1);
				} else {
					this.overlay.show().fadeTo(this.options.fadeSpeed, 1);
					this.ajax.addClass('loading').show().fadeTo(this.options.fadeSpeed, 1);

					this.ajax.load(url, function(response, status, xhr){
						if (status == "error")
							Runalyze.ajax.html('<p class="error">There was an error: '+ xhr.status +' '+ xhr.statusText +'</p>');

						Runalyze.initForOverlay();
						$(this).prepend(Runalyze.options.overlayCloseString).removeClass('loading');
					});
				}
			},

			closeOverlay: function() {
				if ($("#container, #data-browser, #statistics-inner").length > 0)
					$("#ajax, #overlay, #ajax-navigation").fadeTo(this.options.fadeSpeed, 0, function(){
						$("#overlay, #ajax, #ajax-navigation").hide();
						Runalyze.ajax.removeClass('small-window').removeClass('big-window').removeClass('fullscreen');
					});
			},

			toggleOverlayFullscreen: function() {
				Runalyze.ajax.toggleClass('fullscreen');
			},

			loadTraining: function(id, sharedUrl) {
				var url = 'call/call.Training.display.php?id='+id;

				if (typeof sharedUrl != 'undefined')
					url = sharedUrl;

				this.tabContent.loadDiv(url);
				this.currentTabContentUrl = url;
				this.tabs.removeClass('active');

				$("#data-browser tr.training").removeClass('highlight');
				$("#training_"+id).addClass('highlight');
			},

			flotChange: function(div, flot) {
				$(".flotChanger-"+div).addClass("unimportant");
				$(".flotChanger-id-"+flot).removeClass("unimportant");

				$("#"+div+" .flot").addClass("flot-hide");
				$("#"+div+" #"+flot).removeClass("flot-hide");

				$(document).trigger("createFlot");
				RunalyzePlot.resize(flot);
			},

			setTabUrlToFirstStatistic: function() {
				Runalyze.currentTabContentUrl = Runalyze.tabs.first().children('a').attr('href');

				return this;
			},

			reloadPage: function() {
				location.reload();
			},

			reloadContent: function() {
				this.reloadDataBrowser();
				this.reloadCurrentTab();
				this.reloadAllPanels();
			},

			reloadAllPlugins: function(id) {
				if (typeof id == "undefined" || id == "") {
					Runalyze.reloadCurrentTab();
					Runalyze.reloadAllPanels();
				} else {
					Runalyze.reloadPlugin(id);
				}
			},

			reloadContentForConfig: function() {
				this.reloadDataBrowser();
				this.reloadCurrentTab();
				this.reloadAllPanels(this.options.dontReloadForConfigFlag);
			},

			reloadContentForTraining: function() {
				this.reloadDataBrowser();
				this.reloadCurrentTab();
				this.reloadAllPanels(this.options.dontReloadForTrainingFlag);
			},

			reloadDataBrowserAndTraining: function() {
				this.reloadDataBrowser();
				this.reloadTraining();
			},

			reloadDataBrowser: function() {
				$("#refreshDataBrowser").trigger('click');
			},

			reloadTraining: function() {
				if ($("#training-display").length)
					this.reloadCurrentTab();
			},

			reloadCurrentTab: function() {
				if (this.currentTabContentUrl != '') {
					var $currentLink = $('#'+this.options.tabsCssId+' a[href$="'+Runalyze.currentTabContentUrl+'"]');
					if ($currentLink.length) {
						$("tr.training").removeClass('highlight');
						Runalyze.tabs.removeClass("active");
						$currentLink.addClass("active");
					}

					this.tabContent.loadDiv(this.currentTabContentUrl);
				}
			},

			reloadAllPanels: function(dontclass) {
				if (typeof dontclass == "undefined")
					dontclass = "";
				else
					dontclass = ":not(."+dontclass+")";

				$("#panels div.panel"+dontclass).each(function(){
					var id = $(this).attr('id');
					Runalyze.loadPanel( id.substring(6) );
				});
			},

			reloadPlugin: function(id) {
				if (this.currentTabContentUrl.indexOf(this.options.urlForStat+id) >= 0)
					this.reloadCurrentTab();
				else
					this.loadPanel(id);
			},

			loadPanel: function(id) {
				$("#panel-"+id).loadDiv( Runalyze.options.urlForPanel + id );
			},

			saveTcx: function(xml, activityId, index, total, activities) {
				$.post('call/ajax.saveTcx.php', {'activityId': activityId, 'data': xml}, function(){
					if (index == total)
						Runalyze.loadSavedTcxs(activities)
				});
			},

			loadSavedTcxs: function(activityIds) {
				this.ajax.loadDiv($("form#training").attr("action"), {'activityIds': activityIds, 'data': 'FINISHED'});
			},

			loadXML: function(xml) {
				this.ajax.loadDiv($("form#training").attr("action"), {'data': xml});
			},

			toggleFieldset: function(b,c,d,e) {
				b.blur();
				var $c = $("#"+c);

				if (d === true) {
					$c.siblings().addClass("collapsed");
					$c.removeClass("collapsed");
				} else
					$c.toggleClass("collapsed");

				if (e.length > 0)
				Runalyze.changeConfig(e, !$c.hasClass("collapsed"));

				return false;
			},

			toggleView: function(key) {
				if (key == "details" || key == "zones" || key == "rounds" || key == "graphics" || key == "map") {
					var $e,k;

					if (key == "details") {
						$e = $('#training-table-extra');
						k = "TRAINING_SHOW_DETAILS";
					} else if (key == "zones") {
						$e = $('.training-zones');
						k  = "TRAINING_SHOW_ZONES";
					} else if (key == "rounds") {
						$e = $('#training-rounds');
						k  = "TRAINING_SHOW_ROUNDS";
					} else if (key == "graphics") {
						$e = $('#training-plots-and-map');
						k  = "TRAINING_SHOW_GRAPHICS";
					} else if (key == "map") {
						$e = $('#training-map');
						k  = "TRAINING_SHOW_MAP";
						$("#training-view-toggler-map").toggleClass("unimportant");
					}

					$e.toggle();
					Runalyze.changeConfig(k, $e.is(":visible"));

					if (key == "graphics") {
						$(document).trigger("createFlot");
						RunalyzePlot.resizeTrainingCharts();
					}
					if (key == "graphics" || key == "map") {
						RunalyzeGMap.resize();
					}
				}
			},

			searchPageBack: function() {
				var $i = $("#search input[name='page']");
				$i.val( parseInt($i.val()) - 1 );

				$("#search").submit();
			},

			searchPageNext: function() {
				var $i = $("#search input[name='page']");
				$i.val( parseInt($i.val()) + 1 );

				$("#search").submit();
			},

			goToNextMultiEditor: function() {
				var $current = $("#ajax-navigation tr.highlight");

				if ($current.next().length) {
					$current.next().click();
				} else {
					$next = $current.siblings(':not(.edited):first');

					if ($next.length)
						$next.click();
					else
						$current.click();
				}
			}
	}

	if (!window.Runalyze)
		window.Runalyze = Runalyze;
})();


(function($){
	$.fn.extend({
		loadDiv: function(url, data) {
			if (url == "#")
				return this;

			var e = this;

			return e.addClass('loading').load(url, data, function(response, status, xhr){
				if (status == "error")
					e.html('<p class="error">There was an error: '+ xhr.status +' '+ xhr.statusText +'</p>');

				if ($(this).attr('id') == "ajax") {
					Runalyze.initForOverlay();
					$(this).prepend(Runalyze.options.overlayCloseString).removeClass('loading');
				} else {
					Runalyze.initForDivLoad();
					e.hide().removeClass('loading').fadeIn();
				}
			});
		}
	});
})(jQuery);