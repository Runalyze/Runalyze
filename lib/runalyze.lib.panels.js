/*
 * Panels
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Panels = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
		urlSingle:	'call/call.Plugin.display.php?id=',
		urlReload:	'call/call.ContentPanels.php',
		urlClap:	'call/call.PluginPanel.clap.php',
		urlMove:	'call/call.PluginPanel.move.php'
	};


	// Private Methods

	function bindConfigLinks() {
		$("#panels .clap").unbind("click").click(function(){
			$(this).closest(".panel").find(".panel-content").toggle( Parent.Options.fadeSpeed(), function(){
				$(this).closest(".content").find(".flot").each(function(){
					RunalyzePlot.resize($(this).attr('id'));
				});
			});
			$.get( options.urlClap, { id: $(this).attr("rel") });
		});
	}

	function bindMoveLinks() {
		$("#panels .up, #panels .down").unbind("click").click(function(){
			if ($(this).hasClass("up")) {
				$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
				$.get( options.urlMove, { mode: "up", id: $(this).attr("rel") } );
			} else {
				$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
				$.get( options.urlMove, { mode: "down", id: $(this).attr("rel") });
			}
		});
	}


	// Public Methods

	self.init = function() {
		bindConfigLinks();
		bindMoveLinks();
	};

	self.load = function(id) {
		$("#panel-"+id).loadDiv( options.urlSingle + id );
	};

	self.reloadAll = function(dontclass) {
		if (typeof dontclass == "undefined") {
			$("#panels").loadDiv( options.urlReload );
		} else {
			dontclass = ":not(."+dontclass+")";

			$("#panels div.panel"+dontclass).each(function(){
				self.load( $(this).attr('id').substring(6) );
			});
		}
	};

	Parent.addLoadHook('init-panels', self.init);

	return self;
})(jQuery, Runalyze);