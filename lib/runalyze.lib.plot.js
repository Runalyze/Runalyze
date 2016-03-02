/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var RunalyzePlot = (function($, parent){

	// Public

	var self = {};


	// Private

	var plots = {};
	var created = {};
	var plotSizes = {};
	var trainingCharts = {
		options: {
			takeMaxWidth:			true,
			minWidthForContainer:	450,
			maxWidthForSmallSize:	550,
			fixedWidth:				false,
			defaultWidth:			478,
			width:					478
		}
	};
	var initHooks = {};
	var options = {
		defaultPlotOptions: {
			showLegend:			true,
			enableCrosshair:	true,
			allowCrosshair:		true,
			enableSelection:	true,
			allowSelection:		true,
			enablePanning:		false,
			allowPanning:		true,
		},
		waitClass:				'wait-img'
	};
	var defaultOptions = {
		colors:					['#C61D17', '#E68617', '#8A1196', '#E6BE17', '#38219F'],
		legend: {
			backgroundColor:	'#000',
			backgroundOpacity:	0,
			margin:				[0, -25],
			noColumns:			99
		},
		series: {
			stack:				null,
			points: {
				radius:			1,
				lineWidth:		3
			},
			lines: {
				lineWidth:		1,
				steps:			false
			},
			bars: {
				lineWidth:		1,
				barWidth:		0.6,
				align:			'center',
				fill:			0.9
			},
			curvedLines: {
				active:			true,
				apply:			false,
				monotonicFit:	false,
				nrSplinePoints:	5
			}
		},
		yaxis: {
			color:				'rgba(0,0,0,0.1)'
		},
		xaxis: {
			color:				'rgba(0,0,0,0.1)',
			monthNames:			['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
		},
		grid: {
			color:				'#000',
			backgroundColor:	'rgba(255,255,255,0.5)',
			borderColor: {
				top:			'transparent',
				right:			'#999',
				bottom:			'#999',
				left:			'#999'
			},
			minBorderMargin:	5,
			borderWidth:		1,
			labelMargin:		5,
			axisMargin:			2,
			margin: {
				top:			30,
				right:			0,
				bottom:			0,
				left:			0
			}
		},
		canvas:					true,
		font:					'Verdana 9px'
	};


	// Private Methods

	function overwriteOptions(options) {
		options.legend.show = true;
		options.legend.position = "nw";
		options.legend.hideable = true;

		options.grid.hoverable = true;
		options.grid.autoHighlight = false;

		options.crosshair = { mode: "x" };

		if (!options.series.bars.show) {
			options.crosshair = {
				color: "rgba(0, 0, 0, 0.80)",
				lineWidth: 1,
				mode: "x"
			};

			options.selection = $.extend({}, options.selection, { mode: 'x', color: 'rgba(170, 0, 0, 0.5)' });
		} else {
			options.series.curvedLines.apply = false;
		}

		if (options.hooks && options.hooks.draw) {
			options.hooks.draw.push(drawHook);
		} else {
			options.hooks = {
				draw: [drawHook]
			};
		}

		//options.zoom = { interactive: true };
		//options.pan = { interactive: true };

		return options;
	}

	function drawHook(plot, canvascontext) {
		var key = plot.getPlaceholder().attr('id');

		if (typeof plots[key] !== "undefined") {
			repositionAnnotations(key);

			if (!plots[key].showLegend)
				$("#"+key+" .legend").hide();
		} else {
			var $legend = $("#"+key+" .legend");

			if ($legend.find('.legendLabel').length == 1)
				$legend.hide();
		}
	}

	function resizeEachTrainingChart() {
		$(".training-row-plot .flot").each(function(){
			if ($(this).width() != trainingCharts.options.width) {
				$(this).width(trainingCharts.options.width);
				resize($(this).attr('id'));
			}
		});
	}

	function redraw(key) {
		plots[key].setupGrid();
		plots[key].draw();
	}

	function resize(key) {
		if (key in plots && $("#"+key).is(":visible")) {
			plots[key].resize();
			plots[key].setupGrid();
			plots[key].draw();
			plotSizes[key] = $("#"+key).width();
		}
	}

	function finishAnnotations(key) {
		if (plots.hasOwnProperty(key)) {
			var ann = self.getPlot(key).annotations;
			for (var i in ann) {
				if (ann[i].x != null && ann[i].y != null) {
					ann[i].id = 'annotation-'+key+ann[i].x.toString().replace('.','')+ann[i].y.toString().replace('.','');

					$('#'+ key).append('<div id="'+ann[i].id+'" class="annotation">'+ann[i].text+'</div>');

					positionAnnotation(key, ann[i]);
				}
			}
		}
	}

	function positionAnnotation(key, ann) {
		var $e = $('#'+ ann.id),
			o = self.getPlot(key).pointOffset({'x':ann.x, 'y':ann.y});

		$e.css({'left':(o.left + ann.toX)+'px','top':(o.top + ann.toY)+'px'});

		if (o.top + ann.toY < 0)
			$e.hide();
	}

	function repositionAllAnnotations() {
		for (var key in plots)
			repositionAnnotations(key);
	}

	function repositionAnnotations(key) {
		for (var i in plots[key].annotations)
			positionAnnotation(key, plots[key].annotations[i]);
	}


	// Public Methods

	self.setOptions = function(opt) {
		options = $.extend({}, options, opt);

		return self;
	};

	self.resize = function(key) {
		resize(key);
	};

	self.resizeAll = function() {
		for (var key in plots)
			resize(key);

		return self;
	};

	self.clear = function() {
		for (var key in plots)
			self.remove(key);

		return self;
	};

	self.resizeTrainingCharts = function() {
		if ($(".training-row-plot:first").length == 0)
			return;

		trainingCharts.options.width = $(".training-row-plot:visible:first").width() - 24;
		resizeEachTrainingChart();
	};

	self.addInitHook = function(key, hook) {
		initHooks[key] = hook;
	};

	// General methods for Plots

	self.preparePlot = function(cssId, width, height, code) {
		if (created.hasOwnProperty(cssId)) {
			delete created[cssId];
		}

		if (plots.hasOwnProperty(cssId)) {
			plots[cssId].annotations = [];
		}

		$(document).off('createFlot.'+cssId).on('createFlot.'+cssId, function(){
			var $e = $('#'+cssId);

			if (!created.hasOwnProperty(cssId) && $e.width() > 0 && $e.is(':visible') && !$e.hasClass('flot-hide')) {
				created[cssId] = true;

				$e.width(width).height(height);

				code();

				finishAnnotations(cssId);
				$e.removeClass( options.waitClass );

				$(document).off('createFlot.'+cssId);
			}
		});
	};

	self.addPlot = function(cssId, data, opt, plotOptions, annotations) {
		var $e = $("#"+cssId);

		if (cssId in plots && $e.length == 0)
			self.remove(cssId);

		if ($(".training-row-plot").has($e).length)
			$e.width(trainingCharts.options.width);

		opt = $.extend(true, {}, defaultOptions, opt);
		opt = overwriteOptions(opt);

		plotSizes[cssId] = $e.width();
		plots[cssId] = $.plot(	$e, data, opt );
		plots[cssId].options = $.extend(true, {}, options.defaultPlotOptions, plotOptions);
		plots[cssId].showLegend = ($e.find('.legendLabel').length > 1);

		if (typeof annotations != "undefined")
			plots[cssId].annotations = annotations;
		else
			plots[cssId].annotations = [];

		//$e.children('.flot-overlay').dblclick(function(){ self.Saver.save(cssId); });

		for (var key in initHooks)
			initHooks[key](cssId);

		return self;
	};

	self.getPlot = function(key) {
		return plots[key];
	};

	self.remove = function(key) {
		if (key in plots) {
			plots[key].shutdown();

			delete plots[key];
		}

		return self;
	};

	// Interactions

	self.toggleLegend = function(key) {
		$("#"+key+" .legend").toggle();
		plots[key].showLegend = !plots[key].showLegend;
	};

	self.toggleSelection = function(key) {
		self.enableSelection(key, (self.getPlot(key).getOptions().selection.mode != 'x'));
	};

	self.enableSelection = function(key, enable) {
		self.getPlot(key).getOptions().selection.mode = !enable ? null : 'x';

		if (!enable) {
			this.getPlot(key).clearSelection();
			$("#"+key+" .map-tooltip").hide();
		}
	};

	self.toggleCrosshair = function(key) {
		self.enableCrosshair(key, (self.getPlot(key).getOptions().crosshair.mode == 'x'));
	};

	self.enableCrosshair = function(key, enable) {
		self.getPlot(key).getOptions().crosshair.mode = !enable ? null : 'x';

		if (!enable)
			$("#"+key+" .map-tooltip").hide();
	};

	self.togglePanning = function(key) {
		// TODO
	};

	self.enablePanning = function(key, enable) {
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

		resize(key);
	};

	self.setFullscreenSize = function() {
		var $e = $(".flot.fullscreen");

		if ($e.length) {
			$e.css({
				width: $(window).width() - ($e.outerWidth(true) - $e.width()),
				height: $(window).height() - ($e.outerHeight(true) - $e.height())
			});

			resize( $e.attr('id') );
		}
	};

	self.flotHookColorPoints = function(limits, colors, defaultColor) {
		return function(plot, ctx) {
			var data = plot.getData();
			var axes = plot.getAxes();
			var offset = plot.getPlotOffset();
			var lineWidth = plot.getOptions().series.points.lineWidth;
			var radius = lineWidth;

			for (var i = 0; i < data.length; i++) {
				var series = data[i];

				for (var j = 0; j < series.data.length; j++) {
					var d = (series.data[j]);
					var x = offset.left + axes.xaxis.p2c(d[0]);
					var y = offset.top + axes.yaxis.p2c(d[1]);

					var color = defaultColor;
					for (var l = 0; l < limits.length; l++) {
						if (d[1] > limits[l]) {
							color = colors[l];
							break;
						}
					}

					ctx.lineWidth = lineWidth;
					ctx.beginPath();
					ctx.arc(x, y, radius, 0, Math.PI*2, true);
					ctx.closePath();            
					ctx.fillStyle = color;
					ctx.fill();
				}    
			}
		};
	};

	return self;
})(jQuery, undefined);