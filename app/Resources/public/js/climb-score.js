
var DouglasPeucker = function (points, epsilon) {
    /*
     * Based on code by Marius Karthaus, www.lowvoice.nl
     * @see https://karthaus.nl/rdp/js/rdp2.js
     */
    function RDPsd(points, epsilon) {
        var firstPoint = points[0];
        var lastPoint = points[points.length - 1];

        if (points.length < 3){
            return points;
        }

        var index = -1;
        var dist = 0;

        for (var i = 1; i < points.length - 1; i++){
            var cDist = distanceFromPointToLine(points[i], firstPoint, lastPoint);

            if (cDist > dist){
                dist = cDist;
                index = i;
            }
        }

        if (dist > epsilon) {
            var l1 = points.slice(0, index + 1);
            var l2 = points.slice(index);
            var r1 = RDPsd(l1, epsilon);
            var r2 = RDPsd(l2, epsilon);

            return r1.slice(0, r1.length - 1).concat(r2);
        } else {
            return [firstPoint, lastPoint];
        }
    }


    var distanceFromPointToLine = function (p, a, b){
        return Math.sqrt(distanceFromPointToLineSquared(
            {x: p[0], y: p[1]},
            {x: a[0], y: a[1]},
            {x: b[0], y: b[1]}
        ));
    };

    var distanceFromPointToLineSquared = function (p, i, j) {
        var lineLength = pointDistance(i, j);

        if (lineLength == 0) {
            return pointDistance(p, a);
        }

        var t = ((p.x - i.x) * (j.x - i.x) + (p.y - i.y) * (j.y - i.y)) / lineLength;

        if (t < 0) {
            return pointDistance(p, i);
        } else if (t > 1) {
            return pointDistance(p, j);
        }

        return pointDistance(p, {
            x: i.x + t * (j.x - i.x),
            y: i.y + t * (j.y - i.y)
        });
    };

    var pointDistance = function (i, j){
        return (i.x - j.x) * (i.x - j.x) + (i.y - j.y) * (i.y - j.y);
    };

    return RDPsd(points, epsilon);
};

var ClimbScore = (function ($) {
    /**
     * @param array data [[distance_1, elevation_1], ...]
     * @param float epsilon parameter for douglas-peucker, default 1.1
     */
    return function (data, epsilon) {
        if (typeof epsilon === "undefined") {
            epsilon = 1.1;
        }

        var result = {
            reducedProfile:     [],
            gradientProfile:    [],
            gradientHistogram:  [],
            climbs:             [],
            totalScore:         0.0,
            percentageFlat:     0.0,
            percentageHilly:    0.0
        };

        result.reducedProfile = DouglasPeucker(data, epsilon);
        result.gradientProfile = getGradient(result.reducedProfile);
        result.gradientHistogram = getGradientHistogram(result.gradientProfile);

        var sumUpFlatDown = countUpFlatDown(result.gradientHistogram);
        var sumTotal = sumUpFlatDown[0] + sumUpFlatDown[1] + sumUpFlatDown[2];

        result.percentageHilly = 100.0 * (sumUpFlatDown[0] + sumUpFlatDown[2]) / sumTotal;
        result.percentageFlat = 100.0 * (sumUpFlatDown[1]) / sumTotal;

        var totalScore = 0.0;
        var totalScoreLog = 0.0;
        var totalScoreAvg = 0.0;
        var totalScoreAvg2 = 1.0;
        var totalDist = result.reducedProfile[result.reducedProfile.length - 1][0] - result.reducedProfile[0][0];

        for (var i = 1; i < result.reducedProfile.length; ++i) {
            var dist = result.reducedProfile[i][0] - result.reducedProfile[i - 1][0];
            var vm = result.reducedProfile[i][1] - result.reducedProfile[i - 1][1];
            var gradient = 100 * (vm / 1000) / dist;

            if (vm > 0 && dist > 0.1) {
                var categoryFiets = false;
                var scores = calculateScores(result.reducedProfile[i - 1], result.reducedProfile[i]);
                var fiets = scores[1];

                totalScore = totalScore + fiets;
                totalScoreLog = totalScoreLog + Math.log(1.0 + fiets);
                //totalScoreAvg = totalScoreAvg + fiets * dist / totalDist;
                totalScoreAvg = totalScoreAvg + fiets / Math.pow(Math.max(1.0, totalDist / 10), 0.75) / 10;
                //totalScoreAvg2 = totalScoreAvg2 + (fiets * dist / totalDist) * (fiets * dist / totalDist);
                if (gradient >= 2.0) totalScoreAvg2 = totalScoreAvg2 + Math.pow(fiets, 1.0) / Math.max(1.0, Math.sqrt(totalDist / 20));

                // Alternative score similar to TdF (second condition requires gradient > 6.5:
                // hc: >1500m (1000m), 1: >800 (600), 2: >400 (300), 3: >200 (150), 4: >100 (100), 5: >100 (50)

                // Alternative score by Strava (requires gradient > 2.0)
                // hc: > 80, 1: > 64, 2: > 32, 3: > 16, 4: > 8 [, 5: > 4 && gradient > 3.0]

                if (fiets >= 6.5) {
                    categoryFiets = 'hc';
                } else if (fiets >= 5.0) {
                    categoryFiets = '1';
                } else if (fiets >= 3.5) {
                    categoryFiets = '2';
                } else if (fiets >= 2.0) {
                    categoryFiets = '3';
                } else if (fiets >= 0.5) {
                    categoryFiets = '4';
                } else if (fiets >= 0.25) {
                    categoryFiets = '5';
                }

                if (categoryFiets !== false) {
                    result.climbs.push({
                        reducedStartIndex: i - 1,
                        distanceStart: result.reducedProfile[i - 1][0],
                        elevation: vm,
                        altitudeTop: result.reducedProfile[i][1],
                        distance: dist,
                        gradient: gradient,
                        category: categoryFiets,
                        scoreFietsSum: scores[0],
                        scoreFiets: scores[1],
                        scoreCbb: scores[2],
                        scoreSalite: scores[3],
                        scoreCodifava: scores[4]
                    });
                }
            }
        }

        var flatCompensation = 1 - result.percentageFlat * result.percentageFlat / 10000;

        result.totalScore = {
            sum: Math.min(10.0, Math.max(0.0, 2.0 * Math.log(totalScore * flatCompensation))),
            weightedSum: Math.min(10.0, Math.max(0.0, 2.0 * Math.log2(1.0 + totalScoreAvg * flatCompensation))),
            weightedSumClimbs: Math.min(10.0, Math.max(0.0, 2.0 * Math.log2(0.5 + totalScoreAvg2 * flatCompensation)))
        };

        function getGradient(data) {
            var g = JSON.parse(JSON.stringify(data));
            g[0][1] = 0.0;

            for (var i = 1; i < data.length; ++i) {
                g[i][1] = (data[i][0] != data[i - 1][0]) ? 100.0 * (data[i][1] - data[i - 1][1]) / (data[i][0] - data[i - 1][0]) / 1000.0 : 0.0;
            }

            return g;
        }

        function countUpFlatDown(hist) {
            var up = 0.0;
            var flat = 0.0;
            var down = 0.0;

            for (var histClass in hist) {
                if (histClass >= 2.0) {
                    up += hist[histClass];
                } else if (histClass < -2.0) {
                    down += hist[histClass];
                } else {
                    flat += hist[histClass];
                }
            }

            return [up, flat, down];
        }

        function getGradientHistogram(gradient) {
            var hist = [];
            var histClass = 0;
            var dist = 0;

            for (var i = 0; i < gradient.length; ++i) {
                histClass = (Math.floor(gradient[i][1] / 1.0) * 1.0).toString();
                dist = i > 0 ? gradient[i][0] - gradient[i - 1][0] : gradient[i][0];

                if (histClass in hist) {
                    hist[histClass] += dist;
                } else {
                    hist[histClass] = dist;
                }
            }

            return hist;
        }

        function calculateScores(pointFrom, pointTo) {
            var d = pointTo[0] - pointFrom[0]; // [km]
            var h = pointTo[1] - pointFrom[1]; // [m]
            var t = pointTo[1]; // [m]
            var g = h / d / 10; // [%]

            var fietsSum = NaN;
            var fiets = h * h / (d * 10000) + Math.max(0, (t - 1000) / 1000);
            var cbb = 2 * g + h * h / (1000 * d) + d + Math.max(0, (t - 1000) / 100);
            var salite = g * g * d; // Should be a sum for all segments, now it's approx. 100*fiets
            var codifava = (h + 400) / (10 * h) * (g * g * d / 10); // Last part should be a sum

            // TODO: winzige Anstiege (3 hm auf 140m = 1.9% gilt gerade als Anstieg, nur weil der "Gipfel" bei ca. 1600m liegt)
            fiets = Math.min(1.5 * h * h / (d * 10000), fiets);

            return [fietsSum, fiets, cbb, salite, codifava];
        }

        return result;
    }
})(jQuery);
