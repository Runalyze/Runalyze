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
				overlayCloseString: '<div id="close" onclick="Runalyze.closeOverlay()"></div>',
				urlForStat: 'call/call.Plugin.display.php?id=',
				urlForPanel: 'call/call.Plugin.display.php?id=',
				urlForPanelClap: 'call/call.PluginPanel.clap.php',
				urlForPanelMove: 'call/call.PluginPanel.move.php',
				tabsCssId: 'statisticTabs',
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
				this.tabContent = $("#tab_content");
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
				if ($("#container, #tab_content, #ajax").length == 0) {
					$("body > :not(#flotLoader, #copy, #errorToolbar, script)").wrapAll('<div id="ajax" class="panel"></div>');
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
				$("ul.jbar:not(.jbar_finished)").jbar().addClass('jbar_finished');

				$("a.ajax").unbind("click").click(function(e){
					e.preventDefault();

					if ($(this).attr("target") == "tab_content")
						Runalyze.currentTabContentUrl = $(this).attr("href");

					$("#"+$(this).attr("target")).loadDiv($(this).attr("href"), $(this).attr("data-size"));
					return false;
				});

				$("a.window").unbind("click").click(function(e){
					e.preventDefault();
					var href = $(this).attr("href"),
						size = $(this).attr("data-size");

					if (size == "small") {
						Runalyze.ajax.removeClass('bigWin').addClass('smallWin');
					} else if (size == "big") {
						Runalyze.ajax.removeClass('smallWin').addClass('bigWin');
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
					$(this).closest(".panel").find(".content").toggle(Runalyze.options.fadeSpeed,function(){
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
						$("a.change[target="+$(this).attr("target")+"]:first").addClass('triggered');
				});

				$("a.change").unbind("click").click(function(e){
					$("a.change[target="+$(this).attr("target")+"]").removeClass('triggered');
					$(this).addClass('triggered');

					var  target = "#"+$(this).attr("target"),
						$target = $(target),
						$oldDiv = $(target+" > .change:visible, " + target + " > .stat-content-container > .change:visible"),
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
				$('#calendarLink').unbind('click').bind('click', function(){
					$('#calendar').toggle();
					Runalyze.initCalendar();
				});
			},

			initCalendar: function() {
				if ($("#widgetCalendar").text().trim().length > 0)
					return this;

				$('#widgetCalendar').DatePicker({
					flat: true,
					format: 'd B Y',
					date: [new Date(parseInt($("#calendarStart").val())), new Date(parseInt($("#calendarEnd").val()))],
					calendars: 3,
					mode: 'range',
					starts: 1
				});

				$('#calendarSubmit').unbind('click').bind('click', function(){
					var dates = $("#widgetCalendar").DatePickerGetDate(),
						start = Math.round(dates[0].getTime()/1000),
						end = Math.round(dates[1].getTime()/1000);
					$("#daten").loadDiv('call/call.DataBrowser.display.php?start='+start+'&end='+end);
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
				if ($("#container, #dataPanel").length > 0)
					$("#ajax, #overlay, #ajax-navigation").fadeTo(this.options.fadeSpeed, 0, function(){
						$("#overlay, #ajax, #ajax-navigation").hide();
						Runalyze.ajax.removeClass('smallWin').removeClass('bigWin').removeClass('fullscreen');
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

				$("#dataPanel tr.training").removeClass('highlight');
				$("#training_"+id).addClass('highlight');
			},

			flotChange: function(div, flot) {
				$(".flotChanger-"+div).addClass("unimportant");
				$(".flotChanger-id-"+flot).removeClass("unimportant");

				$("#"+div+" .flot").addClass("flotHide");
				$("#"+div+" #"+flot).removeClass("flotHide");
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
				if (this.currentTabContentUrl == this.options.urlForStat+id)
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
				$("#ajax-navigation tr.highlight").next().click();
			}
	}

	if (!window.Runalyze)
		window.Runalyze = Runalyze;
})();


(function($){
	$.fn.extend({
		loadDiv: function(url, data) {
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