function bindFlotForQTipPoints(elem, plot) {
	elem.append('<div class="map-tooltip"></div>');

	elem.bind('plothover', function(event, coords, item) {
		if (plot.getOptions().crosshair.mode == null)
			return;

		var self = $(this),
		axes = plot.getAxes(),
		content = "";

        if (item) {
		    var x = item.datapoint[0],
		    	y = item.datapoint[1];

			content = content + tipLineFor("bei", axes.xaxis.tickFormatter(Math.round(x*100)/100, axes.xaxis));
			content = content + tipLineFor(item.series.label, item.series.yaxis.tickFormatter(Math.round(y*100)/100, item.series.yaxis));

			var pX = 15 + item.pageX - $(document).scrollLeft();
			var pY = 15 + item.pageY - $(document).scrollTop();
			$("#"+self.attr('id')+" .map-tooltip").show().html(content).css('top',pY).css('left',pX);
        } else {
			$("#"+self.attr('id')+" .map-tooltip").hide();    
        }
	});
}

function bindFlotForQTipBars(elem, plot) {
	elem.append('<div class="map-tooltip"></div>');

	elem.bind('plothover', function(event, coords, item) {
		if (plot.getOptions().crosshair.mode == null)
			return;

		var self = $(this),
		axes = plot.getAxes(),
		content = "";

        if (item) {
		    var x = item.datapoint[0],
		    	y = item.datapoint[1] - item.datapoint[2];

			if (plot.getData().length > 0)
				content = item.series.label + ": ";

			content = content + "<em>"+item.series.yaxis.tickFormatter(Math.round(y*100)/100, item.series.yaxis)+"</em>";

			var pX = item.pageX - $(document).scrollLeft() - 15;
			var pY = item.pageY - $(document).scrollTop() - 15;
			$("#"+self.attr('id')+" .map-tooltip").show().html(content).css('top',pY).css('left',pX);
        } else {
			$("#"+self.attr('id')+" .map-tooltip").hide();    
        }
	});
}

function bindFlotForQTip(elem, plot) {
	elem.append('<div class="map-tooltip"></div>');

	elem.bind('plothover', function(event, coords, item) {
		if (plot.selection == true || plot.getOptions().crosshair.mode == null)
			return;

		var self = $(this),
			axes = plot.getAxes(),
			content = "";

		if (coords.x < axes.xaxis.min || coords.x > axes.xaxis.max || coords.y < axes.yaxis.min || coords.y > axes.yaxis.max) {
			$("#"+$(this).attr('id')+" .map-tooltip").hide();
			return;
		}

		content = content + tipLineFor("bei", axes.xaxis.tickFormatter(Math.round(coords.x*100)/100, axes.xaxis));

		var i, j, dataset = plot.getData();
		for (i = 0; i < dataset.length; ++i) {
			var series = dataset[i];

			if (series.data.length == 0)
				break;

			for (j = 0; j < series.data.length; ++j)
				if (series.data[j][0] > coords.x)
					break;

			var y, p1 = series.data[j - 1], p2 = series.data[j];
			if (p1 == null)
				y = p2[1];
			else if (p2 == null || Math.abs(p2[0] - coords.x) > Math.abs(coords.x - p1[0]))
				y = p1[1];
			else
				y = p2[1];

			content = content + tipLineFor(series.label, series.yaxis.tickFormatter(Math.round(y*100)/100, series.yaxis));
		}

		var pX = 15 + coords.pageX - $(document).scrollLeft();
		var pY = 15 + coords.pageY - $(document).scrollTop();
		$("#"+$(this).attr('id')+" .map-tooltip").show().html(content).css('top',pY).css('left',pX);
	});
}

function bindFlotForSelection(elem, plot, rangeCalculation) {
	plot.selection = false;

	elem.bind('plotselected', function(event, ranges) {
		plot.selection = true;

		var axes = plot.getAxes(),
			content = "",
			from = parseFloat(ranges.xaxis.from.toFixed(1)),
			to = parseFloat(ranges.xaxis.to.toFixed(1)),
			o = plot.pointOffset({x:to, y:from+(to-from)/2});

		if (rangeCalculation)
			content = content + tipLineFor(axes.xaxis.tickFormatter(from, axes.xaxis)+" - "+axes.xaxis.tickFormatter(to, axes.xaxis), axes.xaxis.tickFormatter(Math.round((to-from)*10)/10, axes.xaxis));
		else
			content = content + '<em>' + axes.xaxis.tickFormatter(from, axes.xaxis)+" - "+axes.xaxis.tickFormatter(to, axes.xaxis) + '</em><br />';

		// TODO: Think if min/max value is of interest too
		var i, j, dataset = plot.getData();
		for (i = 0; i < dataset.length; ++i) {
			var series = dataset[i], num = 0, sum = 0;

			for (j = 0; j < series.data.length; ++j)
				if (series.data[j][0] >= from && series.data[j][0] <= to){
					sum = sum + series.data[j][1];
					num = num + 1;
				}

			content = content + tipLineFor(series.label, "&oslash; "+series.yaxis.tickFormatter(Math.round((sum/num)*100)/100, series.yaxis));
		}

		var pY = 15 + event.pageY;
		var pX = 15 + event.pageX;
		$("#"+$(this).attr('id')+" .map-tooltip").show().html(content).css('top',pY).css('left',pX);
	});

	elem.bind('plotunselected', function(event) {
		plot.selection = false;
	});
}

function tipLineFor(label, value) {
	return label + ": <em>"+value+"</em><br />";
}