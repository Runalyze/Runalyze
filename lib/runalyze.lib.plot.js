/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzePlot = {
			plots: {},
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

				if ($e.hasClass('trainingChart'))
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

			remove: function(idOfPlot) {
				if (idOfPlot in this.plots) {
					this.plots[idOfPlot].shutdown();

					delete this.plots[idOfPlot];
				} else if (this.options.debugging)
					console.log('Unable to remove plot unknown plot: ' + idOfPlot);

				return this;
			},

			addAnnotationTo: function(idOfPlot, x, y, text) {
				// TODO

				return this;
			},

			resizeTrainingCharts: function() {
				if ($("#trainingChartsAndMap").length == 0)
					return;

				var tabWidth = $("#tab_content").outerWidth(true),
					$boxes = $("#tab_content").children(".dataBox"),
					minWidth = this.trainingCharts.options.minWidthForContainer,
					widths = [], maxBoxWidth = 0, maxWidth, possibleWidth, tmpWidth;

				$boxes.each(function(i){ widths[i] = $(this).outerWidth(true); });
				maxBoxWidth = Math.max.apply(null, widths);

				maxWidth = tabWidth - maxBoxWidth;
				possibleWidth = tabWidth - widths[0];
				tmpWidth = possibleWidth;

				if (!this.trainingCharts.options.takeMaxWidth && widths.length > 1) {
					for (var i = 1; i <= widths.length; i++) {
						tmpWidth = possibleWidth - widths[i];

						if (minWidth <= tmpWidth)
							possibleWidth = tmpWidth;
						else
							break;
					}

					if (possibleWidth > this.trainingCharts.options.maxWidthForSmallSize)
						possibleWidth = this.trainingCharts.options.maxWidthForSmallSize;
				}

				if (possibleWidth > maxWidth)
					possibleWidth = maxWidth;
				//if (possibleWidth < minWidth && this.trainingCharts.options.takeMaxWidth)
				//	possibleWidth = minWidth;

				if ((possibleWidth - 83) != this.trainingCharts.options.width) {
					this.trainingCharts.options.width = possibleWidth - 83;
					this.resizeEachTrainingChart();
				}
			},

			resizeEachTrainingChart: function() {
				$("#trainingPlots .trainingChart").each(function(){
					if ($(this).width() != RunalyzePlot.trainingCharts.options.width) {
						$(this).width(RunalyzePlot.trainingCharts.options.width);
						RunalyzePlot.resize($(this).attr('id'));
					}
				});
			},

			changeChartWidther: function() {
				var $widther = $("#chartWidther");

				this.trainingCharts.options.takeMaxWidth = !$widther.hasClass('widtherIsBig');
				this.resizeTrainingCharts();
				RunalyzeGMap.resize();
				$widther.toggleClass('widtherIsBig');
			},

			initTrainingNavitation: function() {
				$("#checkForMultiplePlots").off('change').on('change', function(){
					if ($(this).is(':checked')) {
						RunalyzePlot.trainingCharts.options.multiple = true;
						Runalyze.changeConfig('TRAINING_PLOTS_BELOW', 'true');
					} else {
						$('#plotNavigation img.active:not(:first)').trigger('click');
						RunalyzePlot.trainingCharts.options.multiple = false;
						Runalyze.changeConfig('TRAINING_PLOTS_BELOW', 'false');
					}
				});

				if (!$("#checkForMultiplePlots").is(':checked')) {
					$('#plotNavigation img.active:not(:first)').trigger('click');
					RunalyzePlot.trainingCharts.options.multiple = false;
				}

				if (this.trainingCharts.options.takeMaxWidth)
					$("#chartWidther").addClass("widtherIsBig");
				else
					$("#chartWidther").removeClass("widtherIsBig");
			},

			toggleTrainingChart: function(key) {
				var $active = $("#plotNavigation img.plotToggler.active"),
					$element = $('#toggle-'+key),
					isActive, activeId, id;

				isActive = $element.hasClass('active');
				activeId = RunalyzePlot.togglerId($active);
				id = RunalyzePlot.togglerId($element);

				if ($active.length == 1) {
					if (!isActive) {
						if (!RunalyzePlot.trainingCharts.options.multiple)
							RunalyzePlot.hideTrainingChart(activeId);
						RunalyzePlot.showTrainingChart(id);
					}
				} else {
					if (!RunalyzePlot.trainingCharts.options.multiple)
						RunalyzePlot.hideTrainingChart(activeId);

					if (isActive)
						RunalyzePlot.hideTrainingChart(id);
					else
						RunalyzePlot.showTrainingChart(id);
				}
			},

			hideTrainingChart: function(key) {
				$('#toggle-'+key).removeClass('active');
				$('#plot-'+key).hide();
			},

			showTrainingChart: function(key) {
				$('#toggle-'+key).addClass('active');
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