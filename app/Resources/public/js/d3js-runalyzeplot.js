String.prototype.hashCode = function () {
    var text = "";
    var possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    for (var i = 0; i < 15; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
};

(function() {
    /* global d3 */
    d3.runalyzeplot = function(plotData) {
        var data = plotData,
            svg = [],
            id = [],
            height = 200,
            width = 690,
            margin = {
                top: 10,
                right: 10,
                bottom: 30,
                left: 50
            },
            plot = {
                gridArea: [],
                plotArea: [],
                axesArea: []
            },
            emptyFun = function() {};

        var self = {
            xValue: emptyFun, xScale: emptyFun, xMap: emptyFun, xAxis: emptyFun,
            yValue: emptyFun, yScale: emptyFun, yMap: emptyFun, yAxis: emptyFun
        };

        self.size = function(w, h) {
            if (!arguments.length) return [width, height];
            width = w - margin.left - margin.right;
            height = h;
            return self;
        };

        self.height = function(px) {
            if (!arguments.length) return height;
            height = px;
            return self;
        };

        self.width = function(px) {
            if (!arguments.length) return width;
            width = px - margin.left - margin.right;
            return self;
        };

        self.margin = function(def) {
            if (!arguments.length) return margin;
            margin = Object.assign(margin, def);
            return self;
        };

        self.select = function(selector) {
            id = selector.hashCode();
            svg = d3.select(selector).append("svg")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .attr("class", "d3js")
                .append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            svg.append('clipPath').attr('id', 'clip-'+id).append('rect').attr('x', 0).attr('y',0).attr('width', width).attr('height', height);

            plot.gridArea = svg.append("g").attr('clip-path', 'url(#clip-'+id+')');
            plot.gridArea.append('rect').attr("class", "plot-area").attr('x', 0).attr('y', 0).attr('width', width).attr('height', height);
            plot.plotArea = svg.append("g").attr('clip-path', 'url(#clip-'+id+')');
            plot.axesArea = svg.append("g");

            self.xValue = self.xValue !== emptyFun ? self.xValue : function(d) { return d[0]; };
            self.xScale = self.xScale !== emptyFun ? self.xScale : d3.scaleLinear().range([0, width]).domain([d3.min(data, self.xValue), d3.max(data, self.xValue)]);
            self.xMap = self.xMap !== emptyFun ? self.xMap : function(d) { return self.xScale(self.xValue(d)); };
            self.xAxis = self.xAxis !== emptyFun ? self.xAxis : d3.axisBottom().scale(self.xScale);

            self.yValue = self.yValue !== emptyFun ? self.yValue : function(d) { return d[1]; };
            self.yScale = self.yScale !== emptyFun ? self.yScale : d3.scaleLinear().range([height, 0]).domain([d3.min(data, self.yValue), d3.max(data, self.yValue)]);
            self.yMap = self.yMap !== emptyFun ? self.yMap : function(d) { return self.yScale(self.yValue(d)); };
            self.yAxis = self.yAxis !== emptyFun ? self.yAxis : d3.axisLeft().scale(self.yScale);

            return self;
        };

        self.svg = function() {
            return svg;
        };

        self.gridArea = function() {
            return plot.gridArea;
        };

        self.plotArea = function() {
            return plot.plotArea;
        };

        self.axesArea = function() {
            return plot.axesArea;
        };

        self.drawAxes = function() {
            plot.yAxis = plot.axesArea.append("g")
                .attr("class", "y axis")
                .call(self.yAxis);

            plot.xAxis = plot.axesArea.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(self.xAxis);
        };

        self.drawXGrid = function(ticks) {
            ticks = ticks || self.xScale.ticks().length;

            plot.gridArea.selectAll("line.grid.x").data(self.xScale.ticks(ticks)).enter()
                .append("line")
                .attr("class", "grid")
                .attr("x1", function(d){ return self.xScale(d) + 0.5; })
                .attr("x2", function(d){ return self.xScale(d) + 0.5; })
                .attr("y1", 0)
                .attr("y2", height);
        };

        self.drawYGrid = function(ticks) {
            ticks = ticks || self.yScale.ticks().length;

            plot.gridArea.selectAll("line.grid.y").data(self.yScale.ticks(ticks)).enter()
                .append("line")
                .attr("class", "grid")
                .attr("y1", function(d){ return self.yScale(d) + 0.5; })
                .attr("y2", function(d){ return self.yScale(d) + 0.5; })
                .attr("x1", 0)
                .attr("x2", width);
        };

        self.drawLine = function(d, c, interpolation) {
            d = d || data;
            c = c || "";
            interpolation = interpolation || d3.curveBasis;

            return plot.plotArea.append("path")
                .datum(d)
                .attr("class", "line "+ c)
                .attr("d", d3.line()
                    .x(self.xMap)
                    .y(self.yMap)
                    .curve(interpolation)
                )
            ;
        };

        self.drawArea = function(d, c) {
            d = d || data;
            c = c || "";

            return [
                plot.plotArea.append("path")
                    .datum(d)
                    .attr("class", "area "+ c)
                    .attr("d", d3.area().x(self.xMap).y0(height).y1(self.yMap)),
                plot.plotArea.append("path")
                    .attr("class", c)
                    .attr("d", d3.line().x(self.xMap).y(self.yMap)(d))
            ];
        };

        self.drawCircles = function(d, c, r) {
            d = d || data;
            c = c || "";
            r = r || 3.5;

            return plot.plotArea.append("g").attr("class", c).selectAll("circle")
                .data(d)
                .enter().append("circle")
                .attr("r", r)
                .attr("cx", self.xMap)
                .attr("cy", self.yMap);
        };

        return self;
    };
})();
