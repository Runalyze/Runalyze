/*
 * Lib for using Plots in Runalyze
 *
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzePlot.Events = (function($, parent){

	// Public

	var self = {};


	// Private

	var options = {
		atString:			'at',
		completeString:		'Total',
		tooltipClass:		'tooltip',
		tooltipInnerClass:	'tooltip-inner'
	};


	// Private Methods

	function tooltip(key) {
		return $('#'+ key +' .'+ options.tooltipClass);
	}

	function addTooltip(key) {
		$('#'+key).append('<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>');
	}

	function line(label, value) {
		return label + ": <span>"+value+"</span><br>";
	}

	function bindTooltip(key) {
		$('#'+key).bind('plothover', onHoverTooltip(key));
		$('#'+key).bind('mouseleave', unsetMapMarker);
	}

	function bindSelection(key) {
		var	plot = parent.getPlot(key);

		plot.selection = false;

		$('#'+key).bind('plotselected', onSelectionTooltip(key) ).bind('plotunselected', function(event) {
			plot.selection = false;
			tooltip(key).removeClass('in').removeClass('right');
		});
	}

	function onHoverTooltip(key) {
		return function(event, coords, item){
			var	plot = parent.getPlot(key),
				opt = plot.getOptions(),
				axes = plot.getAxes(),
				pos = {},
				content = '',
				posClass = '',
				x, y, axisRange;

			if (opt.crosshair.mode != "x" || plot.selection)
				return;

			if (opt.series.points.show) {
				if (item) {
					pos.x = item.pageX;
					pos.y = item.pageY;
					posClass = 'top';

					x = item.datapoint[0];
					y = item.datapoint[1];

					axisRange = axes.xaxis.max - axes.xaxis.min;

					if (axes.xaxis.options.ticks != null && axes.xaxis.options.ticks.length >= x+1) {
						x = axes.xaxis.options.ticks[x][1];
					} else if (axes.xaxis.options.mode == "time" && axisRange/1000 > 86400*2) {
						x = (new Date(x)).toLocaleDateString();
					} else {
						if (axes.xaxis.tickFormatter) {
							x = axes.xaxis.tickFormatter(Math.round(x*100)/100, axes.xaxis);
						}
					}

					content = content + line(
						options.atString,
						x
					) + line(
						item.series.label,
						item.series.yaxis.tickFormatter(Math.round(y*100)/100, item.series.yaxis)
					);
				}
			} else if (opt.series.bars.show) {
		        if (item) {
					if (opt.series.stack) {
						pos.x = coords.pageX + 15;
						pos.y = coords.pageY + 10;
						posClass = 'right';

						var dataset = plot.getData(),
							sum = 0, num = 0;

						for (var i = 0; i < dataset.length; ++i) {
							var series = dataset[i];

							if (series.data.length == 0 || !series.bars.show)
								continue;

							for (var j = 0; j < series.data.length; ++j)
								if (series.data[j][0] > coords.x)
									break;

							var p1 = series.data[j - 1],
								p2 = series.data[j];

							if (p1 == null)
								y = p2[1];
							else if (p2 == null || Math.abs(p2[0] - coords.x) > Math.abs(coords.x - p1[0]))
								y = p1[1];
							else
								y = p2[1];

							num = num + 1;
							sum = sum + parseFloat(y);
							content = content + line(
								series.label,
								series.yaxis.tickFormatter(Math.round(y*100)/100, series.yaxis)
							);
						}

						if (num > 1) {
							content = line(
								options.completeString,
								series.yaxis.tickFormatter(Math.round(sum*100)/100, series.yaxis)
							) + content;
						}
					} else {
						pos.x = item.pageX;
						pos.y = item.pageY;
						posClass = 'top';

						y = item.datapoint[1] - item.datapoint[2];

						if (plot.getData().length > 0)
							content = item.series.label + ': ';

						content = content + '<span>' + item.series.yaxis.tickFormatter(Math.round(y*100)/100, item.series.yaxis) + '</span>';
					}
				}
			} else {
				if (coords.x >= axes.xaxis.min && coords.x <= axes.xaxis.max && coords.y >= axes.yaxis.min && coords.y <= axes.yaxis.max) {
					pos.x = coords.pageX + 15;
					pos.y = coords.pageY + 10;
					posClass = 'right';

					axisRange = axes.xaxis.max - axes.xaxis.min;

					if (axes.xaxis.options.mode == "time" && axisRange/1000 > 86400*2) {
						x = (new Date(coords.x)).toLocaleDateString();
					} else {
						if (axes.xaxis.tickFormatter) {
							x = axes.xaxis.tickFormatter(Math.round(coords.x*100)/100, axes.xaxis);
						}
					}

					if (x) {
						content = content + line(
							options.atString,
							x
						);
					}

					var dataset = plot.getData();

					for (var i = 0; i < dataset.length; ++i) {
						var series = dataset[i];

						if (series.data.length == 0)
							continue;

						for (var j = 0; j < series.data.length; ++j)
							if (series.data[j][0] > coords.x)
								break;

						var p1 = series.data[j - 1],
							p2 = series.data[j];

						if (p1 == null)
							y = p2[1];
						else if (p2 == null || Math.abs(p2[0] - coords.x) > Math.abs(coords.x - p1[0]))
							y = p1[1];
						else
							y = p2[1];

						content = content + line(
							series.label,
							series.yaxis.tickFormatter(Math.round(y*100)/100, series.yaxis)
						);
					}
				}
			}

			if ($(".training-row-plot").has($("#"+key)).length && !opt.series.bars.show) {
				if (plot.getOptions().xaxis.mode != 'time') {
					moveMapMarker(coords.x);
				} else {
					var dataset = plot.getData();
					var distanceSeries = dataset[dataset.length - 1];
					for (j = 0; j < distanceSeries.data.length; ++j)
						if (distanceSeries.data[j][0] > coords.x)
							break;
					moveMapMarker(distanceSeries.data[j][1]);
				}
			}

			show(key, pos, content, posClass);
		};
	}

	function moveMapMarker(pos) {
		if (RunalyzeLeaflet && RunalyzeLeaflet.Routes) {
			RunalyzeLeaflet.Routes.movePosMarker(pos);
		}
	}

	function unsetMapMarker() {
		if (RunalyzeLeaflet && RunalyzeLeaflet.Routes) {
			RunalyzeLeaflet.Routes.unsetPosMarker();
		}
	}

	function onSelectionTooltip(key) {
		return function(event, ranges, third){
			var	plot = parent.getPlot(key),
				rangeCalculation = true;

            rangeCalculation = (plot.getOptions().xaxis.mode != 'time');

			plot.selection = true;

			var axes = plot.getAxes(),
				content = "",
				from = parseFloat(ranges.xaxis.from.toFixed(1)),
				to = parseFloat(ranges.xaxis.to.toFixed(1));

			if (axes.xaxis.tickFormatter) {
				if (rangeCalculation)
					content = content + line(
						axes.xaxis.tickFormatter(from, axes.xaxis)+" - "+axes.xaxis.tickFormatter(to, axes.xaxis),
						axes.xaxis.tickFormatter(Math.round((to-from)*10)/10, axes.xaxis)
					);
				else
					content = content + '<span>' + axes.xaxis.tickFormatter(from, axes.xaxis)+" - "+axes.xaxis.tickFormatter(to, axes.xaxis) + '</span><br>';
			}

			// TODO: Think if min/max value is of interest too
			var i, j, dataset = plot.getData();
			for (i = 0; i < dataset.length; ++i) {
				var series = dataset[i], num = 0, sum = 0;

				for (j = 0; j < series.data.length; ++j)
					if (series.data[j][0] >= from && series.data[j][0] <= to){
						sum = sum + series.data[j][1];
						num = num + 1;
					}

				content = content + line(
					series.label,
					"&oslash; "+series.yaxis.tickFormatter(Math.round(sum*100.0/num)/100, series.yaxis)
				);
			}

			// TODO: event.pageY/X do not exist?
			var pos = {
				y: 15 + event.pageY,
				x: 15 + event.pageX
			};

			show(key, pos, content, 'right');
		};
	}

	function show(key, pos, content, posClass) {
		if (pos.hasOwnProperty('x') && pos.hasOwnProperty('y')) {
			tooltip(key).children('.'+ options.tooltipInnerClass)
						.html(content);

			if (posClass == 'top') {
				pos.x = pos.x - tooltip(key).width()/2;
				pos.y = pos.y - tooltip(key).height() - 10;
			}

			tooltip(key).css('top', pos.y - $(document).scrollTop())
						.css('left', pos.x - $(document).scrollLeft())
						.children('.'+ options.tooltipInnerClass)
						.html(content)
						.parent()
						.addClass(posClass)
						.addClass('in');
		} else {
			tooltip(key).removeClass('in').removeClass(posClass);
		}
	}


	// Public Methods

	self.init = function(key) {
		addTooltip(key);
		bindTooltip(key);
		bindSelection(key);

		return self;
	};

	parent.addInitHook('init-events', self.init);

	return self;
})(jQuery, RunalyzePlot);
