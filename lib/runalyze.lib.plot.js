/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var RunalyzePlot = (function($, parent){

	// Public

	var self = {};


	// Private

	var _plots = {},
		_plotSizes = {},
		//_annotations = {},
		_trainingCharts = {
			options: {
				takeMaxWidth:			true,
				minWidthForContainer:	450,
				maxWidthForSmallSize:	550,
				fixedWidth:				false,
				defaultWidth:			478,
				width:					478
			}
		},
		_initHooks = {},
		_options = {
			defaultPlotOptions: {
				showLegend:			true,
				enableCrosshair:	true,
				enableSelection:	true,
				enablePanning:		false
			}
		};


	// Private Methods

	function _resizeEachTrainingChart() {
		$("#statistics-inner .training-chart").each(function(){
			if ($(this).width() != _trainingCharts.options.width) {
				$(this).width(_trainingCharts.options.width);
				_resize($(this).attr('id'));
			}
		});
	}

	function _redraw(key) {
		_plots[key].setupGrid();
		_plots[key].draw();
	}

	function _resize(key) {
		if (key in _plots && $("#"+key).is(":visible")) {
			_plots[key].resize();
			_plots[key].setupGrid();
			_plots[key].draw();
			_plotSizes[key] = $("#"+key).width();
		}
	}

	function _positionAnnotation(key, ann) {
		var $e = $('#'+ ann.id),
			o = self.getPlot(key).pointOffset({'x':ann.x, 'y':ann.y});

		$e.css({'left':(o.left + ann.toX)+'px','top':(o.top + ann.toY)+'px'});

		if (o.top + ann.toY < 0)
			$e.hide();
	}

	function _repositionAllAnnotations() {
		for (key in _plots)
			for (ann in _plots[key].annotations)
				_positionAnnotation(key, ann);
	}


	// Public Methods

	self.setOptions = function(opt) {
		_options = $.extend({}, _options, opt);

		return self;
	};

	self.resize = function(key) {
		_resize(key);
	};

	self.resizeAll = function() {
		for (key in _plots)
			_resize(key);

		return self;
	};

	self.clear = function() {
		for (key in _plots)
			self.remove(key);

		return self;
	};

	self.resizeTrainingCharts = function() {
		if ($(".training-row-plot:first").length == 0)
			return;

		_trainingCharts.options.width = $(".training-row-plot:first").width() - 24;
		_resizeEachTrainingChart();
		_repositionAllAnnotations();
	};

	self.addInitHook = function(key, hook) {
		_initHooks[key] = hook;
	};

	// General methods for Plots

	self.addPlot = function(cssId, data, options, plotOptions, annotations) {
		var $e = $("#"+cssId);

		if (cssId in _plots && $e.length == 0)
			self.remove(cssId);

		if ($e.hasClass('training-chart'))
			$e.width(_trainingCharts.options.width);

		// Quick & Dirty: Overwrite some options
		options.legend.show = true;
		options.legend.position = "nw";
		options.legend.hideable = true;

		options.grid.hoverable = true;
		options.grid.autoHighlight = false;

		if (!options.series.bars.show)
			options.crosshair = {
				color: "rgba(0, 0, 0, 0.80)",
				lineWidth: 1,
				mode: "x"
			}

		if (!options.series.bars.show)
			options.selection = $.extend({}, options.selection, { mode: 'x', color: 'rgba(170, 0, 0, 0.5)' });

		//options.zoom = { interactive: true };
		//options.pan = { interactive: true };

		_plotSizes[cssId] = $e.width();
		_plots[cssId] = $.plot(	$e, data, options );
		_plots[cssId].options = $.extend({}, _options.defaultPlotOptions, plotOptions);

		if (typeof annotations != "undefined")
			_plots[cssId].annotations = annotations;
		else
			_plots[cssId].annotations = [];

		//$e.children('.flot-overlay').dblclick(function(){ self.Saver.save(cssId); });

		for (key in _initHooks)
			_initHooks[key](cssId);

		return self;
	};

	self.getPlot = function(key) {
		return _plots[key];
	};

	self.remove = function(key) {
		if (key in _plots) {
			_plots[key].shutdown();

			delete _plots[key];
		}

		return self;
	};

	// Methods for annotations

	self.finishAnnotations = function(key) {
		var ann = self.getPlot(key).annotations;
		for (i in ann) {
			if (ann[i].x != null && ann[i].y != null) {
				ann[i].id = 'annotation-'+key+ann[i].x.toString().replace('.','')+ann[i].y.toString().replace('.','');

				$('#'+ key).append('<div id="'+ann[i].id+'" class="annotation">'+ann[i].text+'</div>');

				_positionAnnotation(key, ann[i]);
			}
		}
	};

	/*self.addAnnotationTo = function(key, x, y, text, toX, toY) {
		var $e = $("#"+key),
			k = 'annotation-'+key+x.toString().replace('.','')+y.toString().replace('.','');

		_annotations[k] = {plot: key, x: x, y: y, text: text, toX: toX, toY: toY};
		$e.append('<div id="'+k+'" class="annotation">'+text+'</div>');

		return _positionAnnotation(k);
	};*/

	// Interactions

	self.toggleSelection = function(key) {
		var hide = self.getPlot(key).getOptions().selection.mode == 'x';
		self.getPlot(key).getOptions().selection.mode = hide ? null : 'x';

		if (hide) {
			this.getPlot(key).clearSelection();
			$("#"+key+" .map-tooltip").hide();
		}
	};

	self.toggleCrosshair = function(key) {
		var isActive = (self.getPlot(key).getOptions().crosshair.mode == 'x');

		self.getPlot(key).getOptions().crosshair.mode = isActive ? null : 'x';

		if (isActive)
			$("#"+key+" .map-tooltip").hide();
	};

	self.togglePanning = function(key) {
		// TODO
	};

	self.toggleFullscreen = function(key) {
		var $e = $("#"+key);

		$e.toggleClass('fullscreen');
		$("#"+key+" .flot-settings-fullscreen, #"+key+" .flot-settings-fullscreen-hide").toggleClass('hide');

		if ($e.hasClass('fullscreen')) {
			$e.attr('data-width', $e.width());
			$e.attr('data-height', $e.height());

			self.setFullscreenSize();
		} else {
			$e.css({
				width: $e.attr('data-width'),
				height: $e.attr('data-height')
			});
		}

		_resize(key);
		_repositionAllAnnotations();
	};

	self.setFullscreenSize = function() {
		var $e = $(".flot.fullscreen");

		if ($e.length) {
			$e.css({
				width: $(window).width() - ($e.outerWidth(true) - $e.width()),
				height: $(window).height() - ($e.outerHeight(true) - $e.height())
			});

			_resize($e.attr('id'));
			_repositionAllAnnotations();
		}
	}

	return self;
})(jQuery, undefined);