(function() {
    d3.functor = function functor(v) {
        return typeof v === "function" ? v : function() {
                return v;
            };
    };

    d3.boxplot = function() {
        var height = 1,
            domain = null,
            whiskers = boxWhiskers,
            quartiles = boxQuartiles,
            tooltip = null,
            xAxis = null;

        function box(g) {
            g.each(function(data, i) {
                if (data[1].length == 0) return;

                var d = data[1].sort(d3.ascending);
                var g = d3.select(this);
                var n = d.length;
                var quartileData = d.quartiles = quartiles(d);
                var whiskerIndices = whiskers && whiskers.call(this, d, i);
                var whiskerData = whiskerIndices && whiskerIndices.map(function(i) { return d[i]; });
                var outlierIndices = whiskerIndices
                    ? d3.range(0, whiskerIndices[0]).concat(d3.range(whiskerIndices[1] + 1, n))
                    : d3.range(n);

                g.selectAll("line.center")
                    .data(whiskerData ? [whiskerData] : [])
                    .enter().insert("line", "rect")
                    .attr("class", "center")
                    .attr("y1", height / 2)
                    .attr("y2", height / 2)
                    .attr("x1", function(d) { return xAxis(d[0]); })
                    .attr("x2", function(d) { return xAxis(d[1]); });

                var rect = g.selectAll("rect.box")
                    .data([quartileData])
                    .enter().append("rect")
                    .attr("class", "box")
                    .attr("y", 0)
                    .attr("height", height)
                    .attr("x", function(d) { return Math.min(xAxis(d[0]), xAxis(d[2])); })
                    .attr("width", function(d) { return Math.abs(xAxis(d[2]) - xAxis(d[0])); });

                if (tooltip) {
                    rect.on('mouseover', tooltip.show)
                        .on('mouseout', tooltip.hide);
                }

                g.selectAll("line.median")
                    .data([quartileData[1]])
                    .enter().append("line")
                    .attr("class", "median")
                    .attr("y1", 0)
                    .attr("y2", height)
                    .attr("x1", xAxis)
                    .attr("x2", xAxis);

                g.selectAll("line.whisker")
                    .data(whiskerData || [])
                    .enter().insert("line", "circle, text")
                    .attr("class", "whisker")
                    .attr("y1", 0)
                    .attr("y2", height)
                    .attr("x1", xAxis)
                    .attr("x2", xAxis);

                g.selectAll("circle.outlier")
                    .data(outlierIndices, Number)
                    .enter().insert("circle", "text")
                    .attr("class", "outlier")
                    .attr("r", 2)
                    .attr("cy", height / 2)
                    .attr("cx", function(i) { return xAxis(d[i]); });
            });

            d3.timerFlush();
        }

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

        box.xAxis = function(x) {
            if (!arguments.length) return xAxis;
            xAxis = x;
            return box;
        };

        box.domain = function(x) {
            if (!arguments.length) return domain;
            domain = x == null ? x : d3.functor(x);
            return box;
        };

        box.whiskers = function(x) {
            if (!arguments.length) return whiskers;
            whiskers = x;
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
