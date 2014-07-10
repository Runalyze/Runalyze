/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzePlot.Options = (function($, parent){

	// Public

	var self = {};


	// Private

	var _options = {
		cssClass:		'flot-options',
		cssClassOption:	'flot-option'
	};


	// Private Methods

	function _container(key) {
		return $("#"+ key + " ."+ _options.cssClass);
	}

	function _addOptionsPanel(key) {
		$("#"+key).append('<div class="'+ _options.cssClass +'"/>');

		_addFullscreenLink(key);
		_addSaveLink(key);
		_addLegendLink(key);
		_addAnnotationsLink(key);
		_addCrosshairLink(key);
		_addSelectionLink(key);
		//_addPanningLink(key);
		//_addZoomingLinks(key);
	}

	function _addLink(key, cssClass, tooltip, callback, afterCallback) { // rel="tooltip"
		var $elem = $('<div class="'+ _options.cssClassOption +'"><i class="'+ cssClass +'" title="'+ tooltip +'"/></div>')
			.appendTo( _container(key) )
			.click(function(){
				callback(this);
			});

		if (afterCallback)
			afterCallback($elem, key);
	}

	function _addFullscreenLink(key) {
		_addLink(key, 'fa fa-fw fa-expand', 'Fullscreen mode', function(){
			parent.toggleFullscreen(key);
		});
	}

	function _addSaveLink(key) {
		_addLink(key, 'fa fa-fw fa-floppy-o', 'Save plot', function(){
			parent.Saver.save(key);
		});
	}

	function _addLegendLink(key) {
		_addLink(key, 'fa fa-fw fa-list-ul', 'Toggle legend', function(elem){
			$(elem).toggleClass('option-toggled');
			$("#"+key+" .legend").toggle();
		}, function($elem){
			var $legend = $("#"+key+" .legend");
			if (!$legend.length)
				$elem.remove();
			else {
				if ($legend.find('.legendLabel').length == 1)
					$legend.hide();

				if (!$legend.is(':visible'))
					$elem.addClass('option-toggled');
			} 
		});
	}

	function _addAnnotationsLink(key) {
		_addLink(key, 'fa fa-fw fa-tag', 'Toggle annotations', function(elem){
			$(elem).toggleClass('option-toggled');
			$("#"+key+" .annotation").toggle();
		}, function($elem){
			if (!parent.getPlot(key).annotations.length)
				$elem.remove();
		});
	}

	function _addCrosshairLink(key) {
		_addLink(key, 'fa fa-fw fa-crosshairs', 'Toggle crosshair', function(elem){
			$(elem).toggleClass('option-toggled');
			parent.toggleCrosshair(key);
		}, function($elem){
			if (parent.getPlot(key).getOptions().series.bars.show)
				$elem.remove();
			else if (!parent.getPlot(key).options.enableCrosshair)
				$elem.addClass('option-toggled');
		});
	}

	function _addSelectionLink(key) {
		_addLink(key, 'fa fa-fw fa-crop', 'Toggle selection', function(elem){
			$(elem).toggleClass('option-toggled');
			parent.toggleSelection(key);
		}, function($elem){
			if (parent.getPlot(key).getOptions().series.bars.show)
				$elem.remove();
			else if (!parent.getPlot(key).options.enableSelection)
				$elem.addClass('option-toggled');
		});
	}

	function _addPanningLink(key) {
		_addLink(key, 'fa fa-fw fa-arrows', 'Toggle panning', function(elem){
			$("#"+key+" .zooming-link").parent().toggle();
			$(elem).toggleClass('option-toggled');
			parent.togglePanning(key);
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.addClass('option-toggled');
		});
	}

	function _addZoomingLinks(key) {
		_addLink(key, 'fa fa-fw fa-search-plus zooming-link', 'Zoom in', function(elem){
			parent.getPlot(key).zoom();
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.hide();
		});

		_addLink(key, 'fa fa-fw fa-search-minus zooming-link', 'Zoom out', function(elem){
			parent.getPlot(key).zoomOut();
		}, function($elem){
			if (!parent.getPlot(key).options.enablePanning)
				$elem.hide();
		});
	}


	// Public Methods

	self.init = function(key) {
		_addOptionsPanel(key);

		return self;
	}

	parent.addInitHook('init-options', self.init);

	return self;
})(jQuery, RunalyzePlot);