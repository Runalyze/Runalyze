(function($) {
    $.interpolate = function(norder, steps, noNegative) {
        //Norder is an array of array's with x,y coordiantes..
        norder = norder || [];
        steps = steps || 10;
        noNegative = noNegative || false;  //Set this to true to avoid negative values.. 

        //Minimum of 4 nodes is needed to interpolate..
        if (norder.length < 4) return norder;

        var points = [];
        var val, prevVal, nextVal;

        //Setup Lagrange polynomial -------------------------------------------- 

        //Read in Data Values 
        for (var i = 0; i < norder.length; i++) {
            points.push({ x: norder[i][0], y: norder[i][1], A: 0, B: 0, C: 0, D: 0 })
        }

        //Determine the width of the ith interval 
        for (var i = 0; i < points.length - 1; i++) {
            points[i].i = points[i + 1].x - points[i].x;
        }


        for (var i = 1; i < points.length - 1; i++) {
            val = points[i]; prevVal = points[i - 1]; nextVal = points[i + 1];
            prevVal.D = 2 * (prevVal.i + val.i);
            prevVal.A = val.i;
            prevVal.B = prevVal.i;
            prevVal.C = 6 * ((nextVal.y - val.y) / val.i - (val.y - prevVal.y) / prevVal.i);
        }

        var R;
        for (var i = 1; i < points.length - 2; i++) {
            val = points[i]; prevVal = points[i - 1]; nextVal = points[i + 1];
            R = val.B / prevVal.D;
            val.D = val.D - (R * prevVal.A);
            val.C = val.C - (R * prevVal.C);
        }
        points[points.length - 3].C = points[points.length - 3].C / points[points.length - 3].D;

        for (var i = norder.length - 4; i >= 0; i--) {
            val = points[i]; nextVal = points[i + 1];
            val.C = (val.C - val.A * nextVal.C) / val.D;
        }

        for (var i = 1; i < norder.length - 1; i++) {
            val = points[i]; prevVal = points[i - 1];
            val.S = prevVal.C;
        }
        points[0].S = 0;
        points[points.length - 1].S = 0;


        for (var i = 0; i < norder.length - 1; i++) {
            val = points[i]; prevVal = points[i - 1]; nextVal = points[i + 1];
            val.A = (nextVal.S - val.S) / (6 * val.i);
            val.B = val.S / 2;
            val.C = (nextVal.y - val.y) / val.i - (2 * val.i * val.S + val.i * nextVal.S) / 6;
            val.D = val.y;
        }


        var interPolated = [];
        var xs, ys, u;
        for (var i = 0; i < norder.length - 1; i++) {
            val = points[i];
            for (var j = 0; j < steps; j++) {
                xs = val.x + (val.i / steps) * j;
                u = xs - val.x;
                ys = (val.A * (u * u * u)) + (val.B * (u * u)) + (val.C * u) + val.D;

                if (ys < 0) ys = 0;
                
                interPolated.push([xs, ys]);
            }
        }
        val = points[points.length - 1];
        interPolated.push([val.x, val.y]);
        return interPolated;
    }
})(jQuery);



(function($) {
    var options = {series: { "interpolate": true, "interpolateSteps": 10 }};

    init = function(plot) {
        
        interpolate = function(plot, s, datapoints) {
            if (!s.interpolate) return;
            s.data = $.interpolate(s.data, s.interpolateSteps);
        }

        plot.hooks.processRawData.push(interpolate);
    }

    $.plot.plugins.push({
        init: init,
        options: options,
        name: 'QubicInterpolation',
        version: '1.0'
    });

})(jQuery);