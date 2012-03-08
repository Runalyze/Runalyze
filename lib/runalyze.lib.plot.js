/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzePlot = {
			plots: {},

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
				if (cssId in this.plots && $("#"+cssId).length == 0)
					this.remove(cssId);

				this.plots[cssId] = $.plot(	$("#"+cssId), data, options );

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
				this.plots[idOfPlot].resize();
				this.plots[idOfPlot].setupGrid();
				this.plots[idOfPlot].draw();

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
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzePlot)
		window.RunalyzePlot = RunalyzePlot;
})();