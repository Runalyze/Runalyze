/*global Runalyze, d3, $*/

Runalyze.RacePerformanceChartView = function (selector, url, options) {
    var $plot = $(selector);

    options.mainDistanceKeys = options.mainDistances.map(function(d) { return d.toFixed(2); });

    var secondsToString = Runalyze.Formatter.formatRaceDuration;
    var distanceToString = function(d) {
        // TODO: read 'is_track'
        return Runalyze.Formatter.formatRaceDistance(d, d <= 3.0);
    };
    var closest = function(num, arr) {
        var curr = arr[0];
        var diff = Math.abs(num - curr);
        for (var val = 0; val < arr.length; val++) {
            var newdiff = Math.abs(num - arr[val]);
            if (newdiff < diff) {
                diff = newdiff;
                curr = arr[val];
            }
        }
        return curr;
    };
    var isPb = function(d) {
        return d.isPb;
    };
    var isNotPb = function(d) {
        return !d.isPb;
    };
    var filterRacesToPb = function(data, mainDistances) {
        var selected = [];
        mainDistances = mainDistances || [];
        return data.filter(function(d) {
            return selected.indexOf(d.distanceKey) !== -1 || (mainDistances.length && mainDistances.indexOf(d.distanceKey) === -1) ? false : !!selected.push(d.distanceKey);
        });
    };
    var filterRacesToVo2max = function(data, mainDistances, minDistance) {
        mainDistances = mainDistances || [];
        return data.filter(function(d) {
            return (mainDistances.length && mainDistances.indexOf(d.distanceKey) === -1) ? false : !isNaN(d.vo2maxByTime) && (!minDistance || d.distance >= minDistance);
        });
    };
    var wrapText = function(w, p) {
        p = p || 0;
        return function() {
            var self = d3.select(this),
                textLength = self.node().getComputedTextLength(),
                text = self.text();
            while (textLength > (w - 2 * p) && text.length > 0) {
                text = text.slice(0, -1);
                self.text(text + '\u2026');
                textLength = self.node().getComputedTextLength();
            }
        };
    };

    d3.json(url, function(raceData){
        try {
            var allRaces = raceData.filter(function(d) {
                return (+d.sport_id == options.sportId) && !(+d.is_fun);
            }).map(function(d) {
                return {
                    date: new Date(d.date),
                    distanceKey: (+d.distance).toFixed(2),
                    distance: +d.distance,
                    duration: +d.duration,
                    pace: +d.duration / (+d.distance),
                    vo2maxByHr: d.vo2max ? +d.vo2max : NaN,
                    vo2maxByTime: d.vo2max_by_time ? +d.vo2max_by_time : NaN,
                    name: d.name,
                    ageGrade: +d.age_grade,
                    isPb: false
                };
            }).sort(function(x, y){
                var sortOrder = d3.ascending(x.distance, y.distance);
                return sortOrder == 0 ? d3.ascending(x.duration, y.duration) : sortOrder;
            });

            if (allRaces.length == 0) {
                $plot.append('<p class="text c no-data-message">' + options.noDataMessage + '</p>');
            }

            var allRaceDistances = allRaces.map(function(d) { return d.distance; }).filter(function(value, index, self) { return self.indexOf(value) === index; });
            var allPbRaces = filterRacesToPb(allRaces);

            var currentRaceFixed = false;
            var currentHoverRace = false;
            var pbTimes = [];
            var data = {};
            data.ageStandards = [];

            for (var i = 0; i < options.ageStandardTimes.length; ++i) {
                data.ageStandards.push({
                    date: null,
                    distanceKey: options.mainDistances[i].toFixed(2),
                    distance: options.mainDistances[i],
                    duration: options.ageStandardTimes[i],
                    pace: options.ageStandardTimes[i] / options.mainDistances[i],
                    vo2maxByHr: NaN,
                    vo2maxByTime: options.mainDistances[i] >= 1.0 ? options.ageStandardVO2max[i] : NaN,
                    name: options.ageStandardLabel,
                    ageGrade: 1.00,
                    isPb: false
                });
            }

            $.each(allRaces, function(i,race){
                if (!pbTimes[race.distanceKey]) {
                    pbTimes[race.distanceKey] = race.duration;
                } else if (pbTimes[race.distanceKey] > race.duration) {
                    pbTimes[race.distanceKey] = race.duration;
                }
            });

            $.each(allRaces, function(i,race){
                if (options.mainDistances.includes(race.distance) && (race.duration == pbTimes[race.distanceKey])) {
                    allRaces[i].isPb = true;
                }
            });

            var plot = d3.runalyzeplot(allRaces);
            plot.margin({left: 70});
            plot.size(800, 400);
            plot.xValue = function(d) { return d.distance; };
            plot.yValue = function(d) { return d.pace; };
            plot.xScale = d3.scaleLog().base(10).range([0, plot.width()]);
            plot.yScale = d3.scaleLinear().range([plot.height(), 0]);
            plot.select(selector);
            plot.xAxis.tickFormat(distanceToString);
            plot.yAxisLabelsGroup = plot.svg().append("g").attr("class", "y-axis-labels y-axis-agegrade").attr("transform", "translate(-55,0)");
            plot.yAxisLabels = plot.yAxisLabelsGroup.append("text").attr("y", 0).attr("x", -plot.height() / 2).attr("transform", "rotate(270)");
            plot.yAxisLabels.append("tspan").text("Age grade").attr("class", "y-axis-label-agegrade");
            plot.yAxisLabels.append("tspan").attr("class", "separator").text("//").attr("dx", "8");
            plot.yAxisLabels.append("tspan").text("VO2max").attr("class", "y-axis-label-vo2max").attr("dx", "10");
            plot.yAxisLabels.append("tspan").attr("class", "separator").text("//").attr("dx", "8");
            plot.yAxisLabels.append("tspan").text("Pace").attr("class", "y-axis-label-pace").attr("dx", "10");
            plot.yAxisLabelsBox = plot.yAxisLabelsGroup.append("rect").attr("transform", "rotate(270)");

            var bbox = plot.yAxisLabels.node().getBBox();
            plot.yAxisLabelsBox.attr('x', bbox.x).attr('y', bbox.y).attr('width', bbox.width).attr('height', bbox.height);

            var drawCircles = function(d, c) {
                plot.drawCircles(d, c)
                    .on('mouseover', function(d) { currentHoverRace = d; })
                    .on('click', function(d) { d3.select(this).classed("clicked", true); currentRaceFixed = true; d3.event.stopPropagation(); })
                    .on('mouseout', function(d) { currentHoverRace = false; })
                    .attr("class", function(d) { return d.date ? "year-" + d.date.getFullYear() : ""; });
            };

            plot.svg().on('click', function() { plot.svg().selectAll("circle.clicked").classed("clicked", false); currentRaceFixed = false; });

            var drawPlot = function (d, wr, yunit) {
                var isPace = yunit == "pace";
                var isAgeGrade = yunit == "agegrade";
                var isVo2max = yunit == "vo2max";
                var ageStandardData = data.ageStandards;

                plot.gridArea().selectAll(':not(.plot-area)').remove();
                plot.plotArea().selectAll('*').remove();
                plot.axesArea().selectAll('*').remove();
                $plot.find(".no-data-message").remove();

                if (isAgeGrade) {
                    d = d.filter(function(d) { return !isNaN(d.ageGrade); });
                } else if (isVo2max) {
                    ageStandardData = ageStandardData.filter(function(d) { return !isNaN(d.vo2maxByTime); });
                    d = filterRacesToVo2max(d, wr ? ageStandardData.map(function(d) { return d.distanceKey; }) : [], 1.0);
                }

                if (d.length == 0) {
                    $plot.append('<p class="text c no-data-message">' + options.noDataMessage + '</p>');

                    return;
                }

                if (isPace) {
                    plot.yValue = function(d) { return d.pace; };
                    plot.yScale.domain([d3.max(d, plot.yValue), wr ? 90 : d3.min(d, plot.yValue)]).nice();
                    plot.yMap = function(d) { return plot.yScale(plot.yValue(d)); };
                    plot.yAxis = d3.axisLeft().scale(plot.yScale);
                    plot.yAxis.tickFormat(function(d) {
                        return Runalyze.Formatter.formatPace(d, 0, 'short');
                    });
                } else if (isAgeGrade) {
                    plot.yValue = function(d) { return d.ageGrade; };
                    plot.yScale.domain([0.9 * d3.min(d, plot.yValue), d3.max(d, plot.yValue) * 1.1]).nice();
                    plot.yMap = function(d) { return plot.yScale(plot.yValue(d)); };
                    plot.yAxis = d3.axisLeft().scale(plot.yScale);
                    plot.yAxis.tickFormat(function(d) { return (100 * d).toFixed(0) + "%"; });
                } else if (isVo2max) {
                    plot.yValue = function(d) { return d.vo2maxByTime; };
                    plot.yScale.domain([0.9 * d3.min(d, plot.yValue), d3.max([wr ? d3.max(ageStandardData, plot.yValue) : 0, d3.max(d, plot.yValue)]) * 1.1]).nice();
                    plot.yMap = function(d) { return plot.yScale(plot.yValue(d)); };
                    plot.yAxis = d3.axisLeft().scale(plot.yScale);
                    plot.yAxis.tickFormat(function(d) { return d.toFixed(1); });
                }

                var minDistance = d3.min(d, plot.xValue);
                var maxDistance = d3.max(d, plot.xValue);
                plot.xAxis.tickValues(options.mainDistanceTicks.filter(function(d) { return d >= minDistance && d <= maxDistance; }));
                plot.xScale.domain([0.9 * d3.min(d, plot.xValue), d3.max(d, plot.xValue) * 1.1]);

                plot.drawAxes();
                plot.drawXGrid(5);
                plot.drawYGrid();

                plot.drawLine(d.filter(isPb), "pb-main-distances", d3.curveCatmullRom);

                if (wr && !isAgeGrade) plot.drawLine(ageStandardData, "age-standards", d3.curveCatmullRom);

                if (isAgeGrade || isVo2max) {
                    var bestValue = d3.max(d, plot.yValue);
                    var bestResult = d.filter(function(d) { return (isAgeGrade ? d.ageGrade : d.vo2maxByTime) == bestValue; })[0];

                    if (bestResult) {
                        var makeAnnotations = d3.annotation()
                            .type(d3.annotationXYThreshold)
                            .accessors({
                                x: plot.xMap,
                                y: plot.yMap
                            })
                            .annotations([
                                {
                                    connector: {lineType: "vertical", end: "dot"},
                                    data: bestResult,
                                    note: {
                                        label: distanceToString(bestResult.distance) + ": " + secondsToString(bestResult.duration),
                                        title: "Best result: " + (isAgeGrade ? (100 * bestValue).toFixed(1) + "%" : bestValue.toFixed(1))
                                    },
                                    dy: plot.yMap(bestResult) < 65 ? 30 : -30,
                                    dx: plot.xMap(bestResult) < plot.xScale.range()[1] / 2 ? 0.1 : -0.1,
                                    subject: {
                                        x1: plot.xScale(0.9 * d3.min(d, plot.xValue)),
                                        x2: plot.xScale(1.1 * d3.max(d, plot.xValue))
                                    },
                                    color: "#328593"
                                }
                            ]);

                        plot.plotArea().append("g").attr("class", "annotation-group").call(makeAnnotations);

                        // TODO: it'd be great to see the "potential" for each distance, i.e. what's the time at the 'best result'-line?
                    }
                }

                if (isVo2max) {
                    // TODO: dashed line for current shape
                    // - dy for annotation should depend on shape < or > best
                    // - how to show prognosis for a distance? additional tooltip at that line?
                }

                drawCircles(d.filter(isNotPb), "race other-races");
                drawCircles(d.filter(isPb), "race pb-main-distances");
                if (wr && !isAgeGrade) drawCircles(ageStandardData, "age-standards");

                currentRaceFixed = false;

                plot.tooltip = {};
                plot.tooltip.area = plot.plotArea().append("g").attr("class", "tooltip-area");
                plot.tooltip.cross = plot.tooltip.area.append("g").attr("id", "crossbar").style("display", "none");
                plot.tooltip.crossLine = plot.tooltip.cross.append("line").attr("class", "crossbar").attr("x1", 0).attr("x2", 0).attr("y1", 0).attr("y2", plot.height());
                plot.tooltip.crossText = plot.tooltip.cross.append("text").attr("class", "crossbar-text").attr("x", 0).attr("y", 15).attr("text-anchor", "left");
                plot.tooltip.box = plot.tooltip.area.append("g").attr("id", "crossbar-info").style("display", "none");
                plot.tooltip.box.append("rect").attr("x", 0).attr("y", 20).attr("width", 150).attr("height", 90);
                plot.tooltip.infoName = plot.tooltip.box.append("text").attr("class", "crossbar-info-event").attr("x", 10).attr("y", 36).append("tspan").attr("class", "title");
                plot.tooltip.infoDate = plot.tooltip.box.append("text").attr("class", "crossbar-info-date").attr("x", 10).attr("y", 52);
                plot.tooltip.infoDate.append("tspan").attr("class", "label").text("Date: ");
                plot.tooltip.infoDateValue = plot.tooltip.infoDate.append("tspan").attr("class", "value");
                plot.tooltip.infoResult = plot.tooltip.box.append("text").attr("class", "crossbar-info-result").attr("x", 10).attr("y", 68);
                plot.tooltip.infoResult.append("tspan").attr("class", "label").text("Result: ");
                plot.tooltip.infoResultValue = plot.tooltip.infoResult.append("tspan").attr("class", "value");
                plot.tooltip.infoAG = plot.tooltip.box.append("text").attr("class", "crossbar-info-result").attr("x", 10).attr("y", 84);
                plot.tooltip.infoAG.append("tspan").attr("class", "label").text("Age grade: ");
                plot.tooltip.infoAGValue = plot.tooltip.infoAG.append("tspan").attr("class", "value");
                plot.tooltip.infoVO2max = plot.tooltip.box.append("text").attr("class", "crossbar-info-result").attr("x", 10).attr("y", 100);
                plot.tooltip.infoVO2max.append("tspan").attr("class", "label").text("VO2max: ");
                plot.tooltip.infoVO2maxValue = plot.tooltip.infoVO2max.append("tspan").attr("class", "value");
            };

            var getCurrentYUnit = function() {
                return currentYUnitCounter % 3 == 0 ? "agegrade" : (currentYUnitCounter % 3 == 1 ? "vo2max" : "pace");
            };

            var currentYUnitCounter = 0;
            var currentYUnit = "agegrade";
            var redraw = function() {
                currentYUnit = getCurrentYUnit();

                plot.yAxisLabelsGroup.classed("y-axis-agegrade", currentYUnit == "agegrade");
                plot.yAxisLabelsGroup.classed("y-axis-pace", currentYUnit == "pace");
                plot.yAxisLabelsGroup.classed("y-axis-vo2max", currentYUnit == "vo2max");

                drawPlot(
                    options.gui.showOnlyPb.is(':checked') ? allRaces.filter(isPb) : allRaces,
                    options.gui.showAgeStandard.is(':checked'),
                    currentYUnit
                );

                updateYearSelection();
            };
            var updateYearSelection = function() {
                var y = options.gui.year.val();

                if (y == "all") {
                    $plot.removeClass("with-year-selection");
                    $plot.find(".current-year").removeClass("current-year");
                } else {
                    $plot.addClass("with-year-selection");
                    $plot.find("circle:not(.year-"+ y +")").removeClass("current-year");
                    $plot.find("circle.year-"+ y).addClass("current-year");
                }
            };

            redraw();

            var showCrossbar = function() {
                if (currentRaceFixed) {
                    return;
                }

                var pos = d3.mouse(this);
                var dist = closest(plot.xScale.invert(pos[0]), currentYUnit == "pace" ? allRaceDistances : options.mainDistances);
                var x = plot.xScale(dist);
                var orientationLeft = x + 160 > plot.width();
                var pbRace = currentHoverRace || allPbRaces.filter(function(d) {
                        return d.distance == dist;
                    })[0];

                plot.tooltip.cross.style("display", "initial");
                plot.tooltip.crossLine.attr("x1", x + .5).attr("x2", x + .5);
                plot.tooltip.crossText.attr("x", orientationLeft ? x - 10 : x + 10).text(distanceToString(dist)).attr("text-anchor", orientationLeft ? "end" : "start");
                plot.tooltip.box.style("display", "initial").attr("transform", "translate("+ (orientationLeft ? x - 160 : x + 10) +",0)");
                plot.tooltip.infoName.text(pbRace.name).each(wrapText(150, 10));
                plot.tooltip.infoDateValue.text(Runalyze.Formatter.formatDate(pbRace.date));
                plot.tooltip.infoResultValue.text(secondsToString(pbRace.duration));
                plot.tooltip.infoAGValue.text(isNaN(pbRace.ageGrade) ? "-" : (100 * pbRace.ageGrade).toFixed(1) + "%");
                plot.tooltip.infoVO2maxValue.text(isNaN(pbRace.vo2maxByTime) ? "-" : pbRace.vo2maxByTime.toFixed(1));
            };

            plot.gridArea()
                .on('mouseover', showCrossbar)
                .on('mousemove', showCrossbar)
                .on('mouseout', function() { if (currentRaceFixed) return; plot.tooltip.cross.style("display", "none"); plot.tooltip.box.style("display", "none"); });
            plot.plotArea()
                .on('mouseover', showCrossbar)
                .on('mousemove', showCrossbar)
                .on('mouseout', function() { if (currentRaceFixed) return; plot.tooltip.cross.style("display", "none"); plot.tooltip.box.style("display", "none"); });

            plot.yAxisLabelsBox.on('click', function() {
                ++currentYUnitCounter;
                redraw();
            });

            options.gui.showOnlyPb.click(redraw);
            options.gui.showAgeStandard.click(redraw);
            options.gui.year.on('change', updateYearSelection);
        } catch (e) {
            console.log(e);

            $plot.html('<p class="text"><em>' + options.errorMessage + '</em></p>');
        } finally {
            $plot.removeClass('loading');
        }
    });
};
