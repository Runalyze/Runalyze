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
		urlSingle:	function(id) { return 'my/plugin/'+id; },
		urlReload:	'_internal/plugin/all-panels',
		urlClap:	function(id) { return '_internal/plugin/toggle/'+id; },
		urlMove:	function(id, up) { return '_internal/plugin/move/'+id+'/'+(up ? 'up' : 'down'); }
	};


	// Private Methods

	function bindConfigLinks() {
		$("#panels").find(".clap").unbind("click").click(function(){
			$(this).closest(".panel").find(".panel-content").toggle( Parent.Options.fadeSpeed(), function(){
				$(this).closest(".content").find(".flot").each(function(){
					RunalyzePlot.resize($(this).attr('id'));
				});
			});
			$.get(options.urlClap($(this).attr("rel")));
		});
	}

	function bindMoveLinks() {
		$("#panels .up, #panels .down").unbind("click").click(function(){
			if ($(this).hasClass("up")) {
				$(this).closest(".panel").after($(this).closest(".panel").prev(".panel"));
				$.get(options.urlMove($(this).attr("rel"), true));
			} else {
				$(this).closest(".panel").next(".panel").after($(this).closest(".panel"));
				$.get(options.urlMove($(this).attr("rel"), false));
			}
		});
	}


	// Public Methods

	self.init = function() {
		bindConfigLinks();
		bindMoveLinks();
	};

	self.load = function(id) {
		$("#panel-"+id).loadDiv(options.urlSingle(id));
	};

	self.reloadAll = function(dontclass) {
		if (typeof dontclass == "undefined") {
			$("#panels").loadDiv(options.urlReload);
		} else {
			dontclass = ":not(."+dontclass+")";

			$("#panels").find("div.panel"+dontclass).each(function(){
				self.load($(this).attr('id').substring(6));
			});
		}
	};

	Parent.addLoadHook('init-panels', self.init);

	return self;
})(jQuery, Runalyze);
