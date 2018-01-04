Runalyze.ClimbScoreView = function (stream, unitSystem, errorMessage) {
    var $plot = $("#hill-score-elevation-plot");
    var data = [], untransformedData = [];

    for (var i = 1; i < stream.distance.length - 1; ++i) {
        untransformedData.push([stream.distance[i], stream.elevation[i] * 1000]);
        data.push([
            unitSystem.transformer.distance(stream.distance[i]),
            unitSystem.transformer.elevation(stream.elevation[i])
        ]);
    }

    var score = ClimbScore(untransformedData);
    var hillScore = score.totalScore.weightedSumClimbs;

    var $circleScore = $("#hill-score-progress");

    Runalyze.Feature.radialProgress($circleScore, hillScore / 10.0, hillScore);

    var $flatOrHilly = $("#hill-score-hilly-or-not");
    var $flatOrHillyScore = $flatOrHilly.find(".hilly-score-hilly-or-not-score");
    var $flatOrHillyBar = $flatOrHilly.find(".hilly-score-hilly-or-not-bar");

    $($flatOrHillyScore[0]).text(score.percentageHilly.toFixed(0) + "%");
    $($flatOrHillyScore[1]).text(score.percentageFlat.toFixed(0) + "%");
    $($flatOrHillyBar[0]).css('width', (0.99 * score.percentageHilly).toFixed(0) + "%");
    $($flatOrHillyBar[1]).css('width', (0.99 * score.percentageFlat).toFixed(0) + "%");

    var $table = $("#climb-table");

    if (score.climbs.length) {
        $table.find("tr").remove();

        var margin = {top: 10, right: 10, bottom: 30, left: 50},
            height = 200,
            width = 690 - margin.left - margin.right;

        $.each(score.climbs, function (i, climb) {
            score.climbs[i].duration = stream.time[score.climbs[i].indexEnd] - stream.time[score.climbs[i].indexStart];

            $('<tr class="climb-details">' +
                '<td>' + unitSystem.formatter.distance(climb.distanceStart) + '</td>' +
                '<td>' + unitSystem.formatter.elevation(climb.elevation) + '</td>' +
                '<td>' + unitSystem.formatter.distance(climb.distance) + '</td>' +
                '<td>' + climb.gradient.toFixed(1) + ' &#37;</td>' +
                '<td class="c">' + climb.category + '</td>' +
                '<td>' + climb.scoreFietsSum.toFixed(1) + '</td>' +
                '<td>' + d3.utcFormat(climb.duration >= 3600 ? '%-H:%M:%S' : '%-M:%S')(climb.duration*1000) + '</td>' +
                '<td>' + unitSystem.formatter.pace(unitSystem.transformer.pace(climb.duration / climb.distance)) + '</td>' +
                '<td>' + unitSystem.formatter.elevation(Math.round(climb.elevation / (climb.duration/3600))) + '/h</td>' +
                '</tr>')
                .appendTo($table)
                .on('mouseover', function(){ $(this).addClass('highlight'); $plot.find('.climb:eq('+i+')').addClass('hover'); })
                .on('mouseout', function(){ $(this).removeClass('highlight'); $plot.find('.climb:eq('+i+')').removeClass('hover'); })
                .on('click', function(){
                    var closeEvent = $(this).find('.highlight').length;

                    $table.find('.highlight').removeClass('highlight');
                    $table.find('.climb-profile').fadeOut();
                    $plot.find('.climb.clicked').removeClass('clicked');

                    if (closeEvent) {
                        return;
                    }

                    $(this).find('td').addClass('highlight');
                    $plot.find('.climb:eq('+i+')').addClass('clicked');

                    if ($(this).next().hasClass('climb-profile')) {
                        $(this).next().fadeIn();
                    } else {
                        $('<tr class="climb-profile">' +
                            '<td colspan="9">' +
                            '<div id="hill-score-climb-profile-' + i + '" class="svg-container loading" style="min-height:200px;margin-left:auto"></div>' +
                            '</td></tr>').insertAfter($(this));

                        try {
                            var climbData = data.slice(climb.indexStart, climb.indexEnd + 1).map(function(v){ return [v[0] - data[climb.indexStart][0], v[1]]; }),
                                segWidth = Math.max(0.1, Math.ceil(climbData[climbData.length - 1][0] / 2) / 10),
                                numSegs = Math.ceil(climbData[climbData.length - 1][0] / segWidth),
                                segStart = NaN, segEnd = 0, segGrade = NaN, segAlpha = NaN, segData = [];

                            var climbPlot = d3.runalyzeplot(climbData).size(690, 200);
                            climbPlot.yValue = function(d) { return d[1]; };

                            var yDomainClimb = [d3.min(climbData, climbPlot.yValue), d3.max(climbData, climbPlot.yValue)],
                                h = climbPlot.height();

                            climbPlot.yScale = d3.scaleLinear().range([plot.height(), 0]).domain([yDomainClimb[0] - 0.1*(yDomainClimb[1]-yDomainClimb[0]), yDomainClimb[1]]).nice();
                            climbPlot.select("#hill-score-climb-profile-"+i);

                            climbPlot.xAxis.tickFormat(unitSystem.formatter.distance);
                            climbPlot.yAxis.tickFormat(unitSystem.formatter.elevation).ticks(6);

                            climbPlot.drawAxes();
                            climbPlot.drawYGrid(6);

                            for (var seg = 0; seg < numSegs; ++seg) {
                                if (segStart !== segEnd) {
                                    segStart = segEnd;
                                }

                                while (climbData[segEnd][0] < (seg + 1) * segWidth && segEnd < climbData.length - 1) {
                                    ++segEnd;
                                }

                                if (segStart === segEnd) {
                                    continue;
                                }

                                segData = climbData.slice(segStart, segEnd + 1);
                                segGrade = (climbData[segEnd][1] - climbData[segStart][1]) / (climbData[segEnd][0] - climbData[segStart][0]) / 10;
                                segAlpha = Math.min(1.0, Math.max(0.1, 0.1 + segGrade / 20)).toFixed(2);

                                climbPlot.plotArea().append("path")
                                    .datum(segData)
                                    .attr("class", "area")
                                    .attr("stroke-width", "0")
                                    .attr("fill", d3.interpolateOrRd(segAlpha))
                                    .attr("d", d3.area().x(climbPlot.xMap).y0(h).y1(climbPlot.yMap));

                                climbPlot.plotArea().append("path")
                                    .attr("fill", "none")
                                    .attr("stroke", "#999")
                                    .attr("stroke-width", "2px")
                                    .attr("d", d3.line().x(climbPlot.xMap).y(climbPlot.yMap)(segData));

                                if (seg < numSegs - 1 || segData[segData.length-1][0] - segData[0][0] > 0.8*segWidth) {
                                    climbPlot.plotArea().append("text")
                                        .attr("class", "c")
                                        .attr("x", climbPlot.xMap([(segData[segData.length-1][0] + segData[0][0])/2, 0]))
                                        .attr("y", h-5)
                                        .text(segGrade.toFixed(1)+'%');
                                }
                            }
                        } catch (e) {
                            console.log(e);

                            $('#hill-score-climb-profile-'+i).html('<p class="text"><em>' + errorMessage + '</em></p>');
                        } finally {
                            $('#hill-score-climb-profile-'+i).removeClass('loading');
                        }
                    }
                });
        });
    }

    try {
        var plot = d3.runalyzeplot(data).size(690, 200);
        plot.yValue = function(d) { return d[1]; };

        var yDomain = [d3.min(data, plot.yValue), d3.max(data, plot.yValue)];

        plot.yScale = d3.scaleLinear().range([plot.height(), 0]).domain([Math.max(Math.min(0, yDomain[0]), yDomain[0]-.2*(yDomain[1]-yDomain[0])), yDomain[1]+.2*(yDomain[1]-yDomain[0])]).nice();
        plot.select("#hill-score-elevation-plot");

        plot.xAxis.tickFormat(unitSystem.formatter.distance);
        plot.yAxis.tickFormat(unitSystem.formatter.elevation).ticks(6);

        plot.drawAxes();
        plot.drawYGrid(6);
        plot.drawArea(data, "elevation");

        var climbArea = plot.svg().append("g");

        $.each(score.climbs, function (i, c) {
            var g = climbArea.append("g").attr("class", "climb"),
                x0 = plot.xMap([c.distanceStart, 0]),
                y0 = plot.yMap([0, c.altitudeTop - c.elevation]),
                x = plot.xMap([c.distanceStart + c.distance, 0]),
                y = plot.yMap([0, c.altitudeTop]),
                h = plot.height(),
                b = g.append("g").attr("class", "climb-badge")
                    .attr("transform", "translate(" + x + "," + (y - 10) + ")");

            b.append("circle").attr("r", "7");
            b.append("text").text(c.category);

            g.append("path").attr("class", "climb-profile")
                .datum(data.slice(c.indexStart, c.indexEnd + 1))
                .attr("d", d3.area().x(plot.xMap).y0(h).y1(plot.yMap));

            g.append("line").attr("class", "climb-start").attr("x1", x0).attr("x2", x0).attr("y1", h).attr("y2", y0);
            g.append("line").attr("class", "climb-end").attr("x1", x).attr("x2", x).attr("y1", h).attr("y2", y);

            g.on('mouseover', function (){ $table.find('tr.climb-details:eq('+ i +')').addClass('highlight') });
            g.on('mouseout', function (){ $table.find('tr.climb-details:eq('+ i +')').removeClass('highlight') });
        });
    } catch (e) {
        console.log(e);

        $plot.html('<p class="text"><em>' + errorMessage + '</em></p>');
    } finally {
        $plot.removeClass('loading');
    }
};
