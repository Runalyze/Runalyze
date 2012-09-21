/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzePlot = {
			plots: {},
			annotations: {},
			plotSizes: {},
			trainingCharts: {
				options: {
					multiple: true,
					takeMaxWidth: true,
					minWidthForContainer: 450,
					maxWidthForSmallSize: 550,
					fixedWidth: false,
					defaultWidth: 478,
					width: 478
				}
			},

			options: {
				debugging: false,
				cssClassWaiting: 0
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			clear: function() {
				for (key in this.plots)
					this.remove(key);

				return this;
			},

			addPlot: function(cssId, data, options) {
				var $e = $("#"+cssId);

				if (cssId in this.plots && $e.length == 0)
					this.remove(cssId);

				if ($e.hasClass('training-chart'))
					$e.width(this.trainingCharts.options.width);

				this.plotSizes[cssId] = $e.width();
				this.plots[cssId] = $.plot(	$e, data, options );

				return this;
			},

			getPlot: function(idOfPlot) {
				return this.plots[idOfPlot];
			},

			redraw: function(idOfPlot) {
				this.plots[idOfPlot].draw();

				return this;
			},

			resize: function(idOfPlot) {
				if (idOfPlot in this.plots && $("#"+idOfPlot).is(":visible")) {
					this.plots[idOfPlot].resize();
					this.plots[idOfPlot].setupGrid();
					this.plots[idOfPlot].draw();
					this.plotSizes[idOfPlot] = $("#"+idOfPlot).width();
				}

				return this;
			},

			resizeAll: function() {
				for (idOfPlot in this.plots)
					this.resize(idOfPlot);

				return this;
			},

			remove: function(idOfPlot) {
				if (idOfPlot in this.plots) {
					this.plots[idOfPlot].shutdown();

					delete this.plots[idOfPlot];
				} else if (this.options.debugging)
					console.log('Unable to remove plot unknown plot: ' + idOfPlot);

				return this;
			},

			addAnnotationTo: function(idOfPlot, x, y, text, toX, toY) {
				var $e = $("#"+idOfPlot),
					k = 'annotation-'+idOfPlot+x.toString().replace('.','')+y.toString().replace('.','');

				this.annotations[k] = {plot: idOfPlot, x: x, y: y, text: text, toX: toX, toY: toY};
				$e.append('<div id="'+k+'" class="annotation">'+text+'</div>');

				return this.positionAnnotation(k);
			},

			positionAnnotation: function(key) {
				var $e = $("#"+key),
					a = this.annotations[key],
					o = this.getPlot(a.plot).pointOffset({'x':a.x, 'y':a.y});

				$e.css({'left':(o.left+a.toX)+'px','top':(o.top+a.toY)+'px'});

				if (o.top+a.toY < 0)
					$e.hide();

				return this;
			},

			repositionAllAnnotations: function() {
				for (key in this.annotations) {
					this.positionAnnotation(key);
				}

				return this;
			},

			enableZoomingFor: function(idOfPlot) {
				$('<div class="arrow" style="right:20px;top:20px">zoom out</div>').appendTo('#'+idOfPlot).click(function (e) {
					e.preventDefault();
					RunalyzePlot.getPlot(idOfPlot).zoomOut();
				});
			},

			resizeTrainingCharts: function() {
				if ($("#training-plots-and-map").length == 0)
					return;

				$("#training-plots-and-map").width( $("#tab_content").width() - $("#training-table").outerWidth(true) - 20 );
				this.trainingCharts.options.width = $("#training-plots").width() - 4;
				this.resizeEachTrainingChart();
				this.repositionAllAnnotations();
			},

			resizeEachTrainingChart: function() {
				$("#training-plots .training-chart").each(function(){
					if ($(this).width() != RunalyzePlot.trainingCharts.options.width) {
						$(this).width(RunalyzePlot.trainingCharts.options.width);
						RunalyzePlot.resize($(this).attr('id'));
					}
				});
			},

			initTrainingNavitation: function() {
				this.resizeTrainingCharts();
				RunalyzeGMap.resize();
			},

			toggleTrainingChart: function(key) {
				var $element = $('#toggle-'+key),
					isActive = $element.hasClass('checked'),
					id = RunalyzePlot.togglerId($element);

				$element.parent().toggleClass('unimportant');

				if (isActive)
					RunalyzePlot.hideTrainingChart(id);
				else
					RunalyzePlot.showTrainingChart(id);

				if (key == "pace") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_PACE", !isActive);
				} else if (key == "pulse") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_PULSE", !isActive);
				} else if (key == "elevation") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_ELEVATION", !isActive);
				} else if (key == "splits") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_SPLITS", !isActive);
				}

				RunalyzePlot.resizeTrainingCharts();
			},

			hideTrainingChart: function(key) {
				$('#toggle-'+key).removeClass('checked');
				$('#plot-'+key).hide();
			},

			showTrainingChart: function(key) {
				$('#toggle-'+key).addClass('checked');
				$('#plot-'+key).show(0, function(){
					if (RunalyzePlot.plotSizes[key] != RunalyzePlot.trainingCharts.options.width)
						RunalyzePlot.resize( $(this).children("div:first").attr("id") );
				});

			},

			togglerId: function(e) {
				return e.attr('id').substr(7);
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzePlot)
		window.RunalyzePlot = RunalyzePlot;
})();