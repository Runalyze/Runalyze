function bindFlotForQTipPoints(elem, plot) {
	elem.append('<div class="hoverTip"></div>');

	elem.bind('plothover', function(event, coords, item) {
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
			$("#"+self.attr('id')+" .hoverTip").show().html(content).css('top',pY).css('left',pX);
        } else {
			$("#"+self.attr('id')+" .hoverTip").hide();    
        }
	});
}

function bindFlotForQTip(elem, plot) {
	elem.append('<div class="hoverTip"></div>');

	elem.bind('plothover', function(event, coords, item) {
		var self = $(this),
			axes = plot.getAxes(),
			content = "";

		if (coords.x < axes.xaxis.min || coords.x > axes.xaxis.max || coords.y < axes.yaxis.min || coords.y > axes.yaxis.max) {
			$("#"+$(this).attr('id')+" .hoverTip").hide();
			return;
		}

		content = content + tipLineFor("bei", axes.xaxis.tickFormatter(Math.round(coords.x*100)/100, axes.xaxis));

		var i, j, dataset = plot.getData();
		for (i = 0; i < dataset.length; ++i) {
			var series = dataset[i];

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
		$("#"+$(this).attr('id')+" .hoverTip").show().html(content).css('top',pY).css('left',pX);
	});
}

function tipLineFor(label, value) {
	return label + ": <em>"+value+"</em><br />";
}