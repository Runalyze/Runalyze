(function() {
    d3.functor = function functor(v) {
        return typeof v === "function" ? v : function() {
                return v;
            };
    };

    d3.boxplot = function() {
        var width = 1,
            height = 1,
            domain = null,
            value = Number,
            whiskers = boxWhiskers,
            quartiles = boxQuartiles,
            tooltip = null;

        function box(g) {
            g.each(function(data, i) {
                if (data[1].length == 0) return;

                var d = data[1].sort(d3.ascending);

                var g = d3.select(this),
                    n = d.length,
                    min = d[0],
                    max = d[n - 1];

                var quartileData = d.quartiles = quartiles(d);
                var whiskerIndices = whiskers && whiskers.call(this, d, i);
                var whiskerData = whiskerIndices && whiskerIndices.map(function(i) { return d[i]; });
                var outlierIndices = whiskerIndices
                    ? d3.range(0, whiskerIndices[0]).concat(d3.range(whiskerIndices[1] + 1, n))
                    : d3.range(n);

                var x1 = d3.scaleLinear()
                    .domain(domain && domain.call(this, d, i) || [min, max])
                    .range([0, width]);

                var center = g.selectAll("line.center")
                    .data(whiskerData ? [whiskerData] : []);

                center.enter().insert("line", "rect")
                    .attr("class", "center")
                    .attr("y1", height / 2)
                    .attr("y2", height / 2)
                    .attr("x1", function(d) { return x1(d[0]); })
                    .attr("x2", function(d) { return x1(d[1]); });

                var box = g.selectAll("rect.box")
                    .data([quartileData]);

                var rect = box.enter().append("rect")
                    .attr("class", "box")
                    .attr("y", 0)
                    .attr("height", height)
                    .attr("x", function(d) { return x1(d[0]); })
                    .attr("width", function(d) { return x1(d[2]) - x1(d[0]); });

                if (tooltip) {
                    rect.on('mouseover', tooltip.show)
                        .on('mouseout', tooltip.hide);
                }

                var medianLine = g.selectAll("line.median")
                    .data([quartileData[1]]);

                medianLine.enter().append("line")
                    .attr("class", "median")
                    .attr("y1", 0)
                    .attr("y2", height)
                    .attr("x1", x1)
                    .attr("x2", x1);

                var whisker = g.selectAll("line.whisker")
                    .data(whiskerData || []);

                whisker.enter().insert("line", "circle, text")
                    .attr("class", "whisker")
                    .attr("y1", 0)
                    .attr("y2", height)
                    .attr("x1", x1)
                    .attr("x2", x1);

                var outlier = g.selectAll("circle.outlier")
                    .data(outlierIndices, Number);

                outlier.enter().insert("circle", "text")
                    .attr("class", "outlier")
                    .attr("r", 2)
                    .attr("cy", height / 2)
                    .attr("cx", function(i) { return x1(d[i]); });
            });
            d3.timerFlush();
        }

        box.width = function(x) {
            if (!arguments.length) return width;
            width = x;
            return box;
        };

        box.height = function(x) {
            if (!arguments.length) return height;
            height = x;
            return box;
        };

        box.tooltip = function(x) {
            if (!arguments.length) return tooltip;
            tooltip = x;
            return box;
        };

        box.domain = function(x) {
            if (!arguments.length) return domain;
            domain = x == null ? x : d3.functor(x);
            return box;
        };

        box.value = function(x) {
            if (!arguments.length) return value;
            value = x;
            return box;
        };

        box.whiskers = function(x) {
            if (!arguments.length) return whiskers;
            whiskers = x;
            return box;
        };

        box.quartiles = function(x) {
            if (!arguments.length) return quartiles;
            quartiles = x;
            return box;
        };

        return box;
    };

    function boxWhiskers(d) {
        return [0, d.length - 1];
    }

    function boxQuartiles(d) {
        return [
            d3.quantile(d, .25),
            d3.quantile(d, .5),
            d3.quantile(d, .75)
        ];
    }
})();
