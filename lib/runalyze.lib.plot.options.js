/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzePlot.Options = (function($, parent){

	// Public

	var self = {};


	// Private

	var options = {
		cssClass:		'flot-options',
		cssClassOption:	'flot-option'
	};


	// Private Methods

	function container(key) {
		return $("#"+ key + " ."+ options.cssClass);
	}

	function addOptionsPanel(key) {
		$("#"+key).append('<div class="'+ options.cssClass +'"/>');

		addFullscreenLink(key);
		addSaveLink(key);
		addLegendLink(key);
		addAnnotationsLink(key);
		addCrosshairLink(key);
		addSelectionLink(key);
		//addPanningLink(key);
		//addZoomingLinks(key);
	}

	function addLink(key, cssClass, tooltip, callback, afterCallback) { // rel="tooltip"
		var $elem = $('<div class="'+ options.cssClassOption +'"><i class="'+ cssClass +'" title="'+ tooltip +'"/></div>')
			.appendTo( container(key) )
			.click(function(){
				callback(this);
			});

		if (afterCallback)
			afterCallback($elem, key);
	}

	function addFullscreenLink(key) {
		addLink(key, 'fa fa-fw fa-expand', 'Fullscreen mode', function(){
			parent.toggleFullscreen(key);
		});
	}

	function addSaveLink(key) {
		addLink(key, 'fa fa-fw fa-floppy-o', 'Save plot', function(){
			parent.Saver.save(key);
		});
	}

	function addLegendLink(key) {
		addLink(key, 'fa fa-fw fa-list-ul', 'Toggle legend', function(elem){
			parent.toggleLegend(key);
			$(elem).toggleClass('option-toggled');
		}, function($elem){
			var $legend = $("#"+key+" .legend");
			if (!$legend.length)
				$elem.remove();
			else if (!$legend.is(':visible'))
				$elem.addClass('option-toggled');
		});
	}

	function addAnnotationsLink(key) {
		addLink(key, 'fa fa-fw fa-tag', 'Toggle annotations', function(elem){
			$(elem).toggleClass('option-toggled');
			$("#"+key+" .annotation").toggle();
		}, function($elem){
			if (!parent.getPlot(key).annotations.length)
				$elem.remove();
		});
	}

	function addCrosshairLink(key) {
		addLink(key, 'fa fa-fw fa-crosshairs', 'Toggle crosshair', function(elem){
			$(elem).toggleClass('option-toggled');
			parent.toggleCrosshair(key);
		}, function($elem){
			if (parent.getPlot(key).getOptions().series.bars.show)
				$elem.remove();
			else if (!parent.getPlot(key).options.enableCrosshair)
				$elem.addClass('option-toggled');
		});
	}

	function addSelectionLink(key) {
		addLink(key, 'fa fa-fw fa-crop', 'Toggle selection', function(elem){
			$(elem).toggleClass('option-toggled');
			parent.toggleSelection(key);
		}, function($elem){
			if (parent.getPlot(key).getOptions().series.bars.show)
				$elem.remove();
			else if (!parent.getPlot(key).options.enableSelection)
				$elem.addClass('option-toggled');
		});
	}

	function addPanningLink(key) {
		addLink(key, 'fa fa-fw fa-arrows', 'Toggle panning', function(elem){
			$("#"+key+" .zooming-link").parent().toggle();
			$(elem).toggleClass('option-toggled');
			parent.togglePanning(key);
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.addClass('option-toggled');
		});
	}

	function addZoomingLinks(key) {
		addLink(key, 'fa fa-fw fa-search-plus zooming-link', 'Zoom in', function(elem){
			parent.getPlot(key).zoom();
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.hide();
		});

		addLink(key, 'fa fa-fw fa-search-minus zooming-link', 'Zoom out', function(elem){
			parent.getPlot(key).zoomOut();
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.hide();
		});
	}


	// Public Methods

	self.init = function(key) {
		addOptionsPanel(key);

		return self;
	};

	parent.addInitHook('init-options', self.init);

	return self;
})(jQuery, RunalyzePlot);