/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/. */

/*
 * Plugin to hide series in flot graphs.
 *
 * To activate, set legend.hideable to true in the flot options object.
 *
 * Example:
 *
 *     plot = $.plot($("#placeholder"), plotdata, {
 *         legend: {
 *             hideable: true
 *         }
 *     });
 */
/*
 * Edited by Hannes Christiansen for Runalyze
 */
(function ($) {
    var options = { };
    var drawnOnce = false;

    function init(plot) {
        function findPlotSeries(label) {
            var plotdata = plot.getData();
            for (var i = 0; i < plotdata.length; i++) {
                if (plotdata[i].label == label) {
                    return plotdata[i];
                }
            }
            return null;
        }

        function plotLabelClicked(label, mouseOut) {
            var series = findPlotSeries(label);
            if (!series) {
                return;
            }

			if (typeof series.lines.showDefault == "undefined") {
				series.lines.showDefault = series.lines.show;
				series.points.showDefault = series.points.show;
			}

			if (series.lines.show || series.points.show) {
				series.lines.show = false;
				series.points.show = false;
			} else {
				series.lines.show = series.lines.showDefault;
				series.points.show = series.points.showDefault;
			}

            // HACK: Reset the data, triggering recalculation of graph bounds
            plot.setData(plot.getData());

            plot.setupGrid();
            plot.draw();
        }

        function plotLabelHandlers(plot, options) {
            $(".graphlabellink").click(function() { plotLabelClicked($(this).parent().text()); });
        }

        function checkOptions(plot, options) {
            if (!options.legend.hideable) {
                return;
            }

            options.legend.labelFormatter = function(label, series) {
				return '<a class="graphlabellink" style="cursor:pointer;">' + label + '</a>';
            };

            // Really just needed for initial draw; the mouse-enter/leave functions will
            // call plotLabelHandlers() directly, since they only call setupGrid().
            plot.hooks.draw.push(function (plot, ctx) {
                plotLabelHandlers(plot, options);
            });
        }

        plot.hooks.processOptions.push(checkOptions);
    }

    $.plot.plugins.push({
        init: init,
        options: options,
        name: 'hiddenGraphs',
        version: '1.0hc'
    });

})(jQuery);
