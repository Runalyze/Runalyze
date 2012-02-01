/*
 * JS-library for Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var Runalyze = {
			options: {
				fadeSpeed: 200,
				minimumWindowSize: 1024,
				useTooltip: false,
				useRoundHighlighter: false,
				useConfigHoverLinks: false,
				overlayCloseString: '<div id="close" onclick="Runalyze.closeOverlay()"></div>',
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			init: function(opt) {
				this.setOptions(opt);
				this.initProperties();

				this.initOverlay();
				this.initResizer();
				this.initAjaxLinks();
				this.initTabNavigation();
				this.initPanelsConfig();
				this.initTooltip();
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
				this.initToggle();
				this.initChangeDiv();
				this.initFormulars();

				$(document).trigger("createFlot");
			},

			initForDivLoad: function() {
				this.initAjaxLinks();
				this.initTooltip();
				this.initRoundHighlighter();
				this.initToggle();
				this.initChangeDiv();
				this.initCalendarLink();
				this.initFormulars();

				$(document).trigger("createFlot");
			},

			initProperties: function() {
				this.body       = $("body");
				this.tabs       = $("#statisticTabs li");
				this.tabContent = $("#tab_content");
			},

			initOverlay: function() {
				this.body.prepend('<div id="overlay"></div><div id="ajax" class="panel"></div>');

				this.overlay    = $("#overlay");
				this.ajax       = $("#ajax");

				this.overlay.click(function(){ Runalyze.closeOverlay(); });
			},

			initResizer: function() {
				this.resizeAjaxOverlay();

				$(window).resize(function() {
					Runalyze.resizeAjaxOverlay();
				});
			},

			resizeAjaxOverlay: function() {
				if (this.body.width() <= Runalyze.options.minimumWindowSize && !this.ajax.hasClass('smallWin'))
					this.ajax.addClass('tooBig');
				else
					this.ajax.removeClass('tooBig');
			},

			initAjaxLinks: function() {
				$("a.ajax").unbind("click").click(function(e){
					e.preventDefault();
					$("#"+$(this).attr("target")).loadDiv($(this).attr("href"), $(this).attr("rel"));
					return false;
				});

				$("a.window").unbind("click").click(function(e){
					e.preventDefault();
					var href = $(this).attr("href"),
						rel = $(this).attr("rel");

					if (rel == "small") {
						Runalyze.ajax.removeClass('bigWin').addClass('smallWin');
					} else if (rel == "big") {
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

					Runalyze.tabContent.loadDiv($(this).find("a").attr("href"), $(this).find("a").attr("rel"));
					
					return false;
				});
			},

			initPanelsConfig: function() {
				this.initPanelsConfigLinks();

				if (!this.options.useConfigHoverLinks)
					return this;

				this.initPanelsConfigHover();
				this.initPanelsConfigHoverLinks();
			},

			initPanelsConfigLinks: function() {
				$("#panels .panel .clap").unbind("click").click(function(){
					$(this).closest(".panel").find(".content").toggle(Runalyze.options.fadeSpeed);
					$.get("call/call.PluginPanel.clap.php", { id: $(this).attr("rel") });
				});
			},

			initPanelsConfigHover: function() {
				$("#panels div.panel").unbind("hover").hover(function(){
					if ($(this).find(".content").css("display") != "none")
						$(this).find("div.config").fadeIn();
				}, function(){
					$(this).find("div.config").fadeOut();
				});
			},

			initPanelsConfigHoverLinks: function() {
				$("#panels .panel .config img.link").unbind("click").click(function(){
					if ($(this).hasClass("up")) {
						$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
						$.get("call/call.PluginPanel.move.php", { mode: "up", id: $(this).attr("rel") } );
					} else {
						$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
						$.get("call/call.PluginPanel.move.php", { mode: "down", id: $(this).attr("rel") });
					}
				});
			},

			initTooltip: function() {
				if (!this.options.useTooltip)
					return this;

				$("a img").tooltip({
					track: true,
					delay: 0,
					showURL: false
				});

				return this;
			},

			initRoundHighlighter: function() {
				if (this.options.useRoundHighlighter)
					$("#trainingRounds tr").click(function() { $(this).toggleClass('highlight'); });

				return this;
			},

			initToggle: function() {
				$(".toggle").unbind("click").click(function(){
					$("#"+$(this).attr("rel")).animate({opacity: 'toggle'});
				}); 
			},

			initChangeDiv: function() {
				$("a.change").unbind("click").click(function(){
					var id = $(this).attr("href").split('#').pop(),
						target = "#"+$(this).attr("target");

					$(target+" div.change").each(function(){
						if ($(this).css("display") == "block")
							$(this).fadeOut(Runalyze.options.fadeSpeed, function(){
								$(target+" div#"+id).fadeTo(Runalyze.options.fadeSpeed, 1, function(){
									$(target+" div#"+id+" div.change:first-child").show();
									$(document).trigger("createFlot");
								});
							});
					});
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
					date: [new Date(), new Date()],
					calendars: 3,
					mode: 'range',
					starts: 1,
					onChange: function(formated) { $('#calendarResult').get(0).innerHTML = formated.join(' - '); }
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
					$("#daten").loadDiv('call/call.DataBrowser.display.php?start='+start+'&end='+end);
				});
			},

			initFormulars: function() {
				// Warning: Does only work for formulars in #ajax
				$("#ajax form.ajax").unbind("submit").submit(function(e){
					e.preventDefault();

					if ($(this).children(":submit").hasClass('debug')) {
						window.alert($(this).serialize());
						return false;
					}

					var formID = $(this).attr("id");
					if (formID == "search" && $("form.ajax input[name=send_to_multiEditor]:checked").length == 0) {
						$("#searchResult").loadDiv($(this).attr("action")+'?pager=true', $(this).serializeArray());
						return false;
					}

					Runalyze.ajax.addClass('loading');
					Runalyze.ajax.load($(this).attr("action"), $(this).serializeArray(), function(){
						Runalyze.initForOverlay();

						$("#submit-info").fadeIn().delay(2000).fadeOut();
						$(this).prepend(Runalyze.options.overlayCloseString).removeClass('loading');

						if (formID != "search" && formID != "tcxUpload")
							Runalyze.reloadContent();
					});

					return false;
				});
			},



			loadOverlay: function(url) {
				this.ajax.css({'margin-top':($(window).scrollTop()+25)+'px'});

				if (url == "call/window.search.php" && $("#ajax h1:first").text() == "Suche") {
					$("#ajax, #overlay").show().fadeTo(this.options.fadeSpeed, 1);
				} else {
					this.overlay.show().fadeTo(this.options.fadeSpeed, 1);
					this.ajax.addClass('loading').show().fadeTo(this.options.fadeSpeed, 1);

					this.ajax.load(url, function(){
						Runalyze.initForOverlay();
						$(this).prepend(Runalyze.options.overlayCloseString).removeClass('loading');
					});
				}
			},

			closeOverlay: function() {
				$("#ajax, #overlay").fadeTo(this.options.fadeSpeed, 0, function(){
					$("#overlay, #ajax").hide();
					Runalyze.ajax.removeClass('smallWin').removeClass('bigWin');
				});
			},

			loadTraining: function(id) {
				this.tabContent.loadDiv('call/call.Training.display.php?id='+id);
				this.tabs.removeClass('active');
				$("#daten tr.training").removeClass('highlight');
				$("#training_"+id).addClass('highlight');
			},

			flotChange: function(div, flot) {
				$("#"+div+" .flot").addClass("flotHide");
				$("#"+div+" #"+flot).removeClass("flotHide");
			},

			reloadContent: function() {
				$("div#daten h1 span.right a:first").trigger('click');
				$("ul.tabs li.active a").trigger('click');

				$("#panels div.panel").each(function(){
					$(this).loadDiv( 'call/call.Plugin.display.php?id='+$(this).attr('id').substring(6) );
				});
			},

			loadXML: function(xml) {
				this.ajax.loadDiv($("form#newtraining").attr("action"), {'data': xml});
			},
	}

	if (!window.Runalyze)
		window.Runalyze = Runalyze;
})();


(function($){
	$.fn.extend({
		loadDiv: function(url, data) {
			var e = this;

			return e.addClass('loading').load(url, data, function(){
				Runalyze.initForDivLoad();
				e.removeClass('loading');
			});
		}
	});
})(jQuery);