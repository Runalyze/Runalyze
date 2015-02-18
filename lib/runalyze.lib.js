/*
 * Lib for general stuff in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var Runalyze = (function($, parent){

	// Public

	var self = {};


	// Private

	var options = {
		dontReloadForConfigFlag:	'dont-reload-for-config',
		dontReloadForTrainingFlag:	'dont-reload-for-training'
	};

	var $body;

	var initHooks = [];
	var loadHooks = [];

	var isReady = false;


	// Private Methods

	function mergeOptions(newOptions) {
		options = $.extend({}, options, newOptions);
	}

	function initObjects() {
		$body = $("body");
	}

	function setupAjax() {
		$.ajaxSetup({
			error: function(x,e) {
				if ("status" in x) {
					if (x.status == 404) {
						self.Log.add("ERROR", "There was an error 404: The requested resource could not be found.");
					} else if (x.status == 500) {
						self.Log.add("ERROR", "There was an error 500: Internal Server Error.");
					}
				}
			}
		});
	}

	function initResizer() {
		$(window).resize(function() {
			if (typeof RunalyzePlot != "undefined") {
				RunalyzePlot.resizeTrainingCharts();
				RunalyzePlot.setFullscreenSize();
			}
		});
	}

	function addOwnHooks() {
		self.addLoadHook('create-flot', self.createFlot);

		// TODO: move
		self.addLoadHook('resize-trainings', RunalyzePlot.resizeTrainingCharts);
	}

	function runInitHooks() {
		for (key in initHooks) {
			initHooks[key]();
		}
	}

	function runLoadHooks() {
		for (key in loadHooks) {
			loadHooks[key]();
		}
	}

	function reloadContentForConfig() {
		self.DataBrowser.reload();
		self.Statistics.reload();
		self.Panels.reloadAll( options.dontReloadForConfigFlag );
	};

	function reloadContentForTraining() {
		self.DataBrowser.reload();
		self.Statistics.reload();
		self.Panels.reloadAll( options.dontReloadForTrainingFlag );
	};


	// Public Methods

	self.addInitHook = function(key, hook) {
		initHooks[key] = hook;
	};

	self.addLoadHook = function(key, hook) {
		loadHooks[key] = hook;
	};

	self.init = function(newOptions) {
		mergeOptions(newOptions);
		initObjects();

		if (!isReady) {
			setupAjax();
			initResizer();
		}

		addOwnHooks();

		runInitHooks();
		runLoadHooks();

		isReady = true;
	};

	self.reinit = function() {
		runLoadHooks();
	};

	self.body = function() {
		return $body;
	};

	self.isReady = function() {
		return isReady;
	};

	self.hasContainer = function() {
		return ($("#container, #data-browser, #statistics-inner").length > 0);
	};

	self.createFlot = function() {
		$(document).trigger("createFlot");
	};

	self.flotChange = function(div, flot) {
		$(".flotChanger-"+div).addClass("unimportant");
		$(".flotChanger-id-"+flot).removeClass("unimportant");

		$("#"+div+" .flot").addClass("flot-hide");
		$("#"+div+" #"+flot).removeClass("flot-hide");

		self.createFlot();
		RunalyzePlot.resize(flot);
	};

	self.toggleFieldset = function(b,c,d,e) {
		b.blur();
		var $c = $("#"+c);

		if (d === true) {
			$c.siblings().addClass("collapsed");
			$c.removeClass("collapsed");
		} else
			$c.toggleClass("collapsed");

		if (e.length > 0)
			self.Config.setActivityFormLegend(e, !$c.hasClass("collapsed"));

		return false;
	};

	self.goToNextMultiEditor = function() {
		var $current = $("#ajax-navigation tr.highlight");

		if ($current.next().length) {
			$current.next().click();
		} else {
			var $next = $current.siblings(':not(.edited):first');

			if ($next.length)
				$next.click();
			else
				$current.click();
		}
	};

	self.reloadPage = function() {
		location.reload();
	};

	self.reloadContent = function() {
		var e = $("#container").addClass( self.Options.loadingClass() );
		var url = 'index.php?';
		var db = self.DataBrowser.currentTimes();

		if (self.Statistics.showsTraining()) {
			url = url + 'id=' + self.Statistics.currentId();
		} else {
			url = url + 'pluginid=' + self.Statistics.currentId();
		}

		url = url + '&start=' + db.start + '&end=' + db.end + ' #container > *';

		$.ajax({
			url: url
		}).done(function(data){
			var content = $('<div />').html(data).find('#container > *');
			$('#container').html(content);

			self.init();
			e.hide().removeClass( self.Options.loadingClass() ).fadeIn();
		}).fail(function(xhr){
			$('#container').html('<p class="error">There was an error: '+ xhr.status +' '+ xhr.statusText +'</p>');
		});
	};

	self.reloadAllPlugins = function(id) {
		if (typeof id == "undefined" || id == "") {
			self.Statistics.reload();
			self.Panels.reloadAll();
		} else {
			self.reloadPlugin(id);
		}
	};

	self.reloadDataBrowserAndTraining = function() {
		self.DataBrowser.reload();
		self.Training.reload();
	};

	self.reloadPlugin = function(id) {
		if (self.Statistics.shows(id))
			self.Statistics.reload();
		else
			self.Panels.load(id);
	};

	return self;
})(jQuery, undefined);


(function($, Runalyze){
	$.fn.extend({
		loadDiv: function(url, data, settings) {
			if (url == "#")
				return this;

			var e = this;

			return e.addClass( Runalyze.Options.loadingClass() ).load(url, data, function(response, status, xhr){
				if (status == "error") {
					e.html('<p class="error">There was an error: '+ xhr.status +' '+ xhr.statusText +'</p>');
				}

				if (e.attr('id') == "ajax") {
					Runalyze.reinit();
					Runalyze.Overlay.addCloseButton();
					e.removeClass( Runalyze.Options.loadingClass() );
				} else {
					Runalyze.reinit();
					e.hide().removeClass( Runalyze.Options.loadingClass() ).fadeIn();
				}
		
				if (settings) {
					if (settings.success) {
						settings.success();
					}
				}
			});
		}
	});
})(jQuery, Runalyze);