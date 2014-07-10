/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzePlot.Saver = (function($, parent){

	// Public

	var self = {};


	// Private

	var _isReady = false,
		_options = {
			bgColor:				"#ffffff",
			annotationBgColor:		'#000000',
			annotationTextColor:	'#ffffff',
			filename:				'Plot.png',
			callback:				'call/savePng.php',
			errorMessage:			'Sorry, your browser is not able to save images from canvas.'
		};


	// Private Methods

	function _init() {
		if (!_isReady) {
			$('body').append(
				'<form style="display:none;" action="'+ _options.callback +'" method="post" target="save-png-frame" id="save-png-form">'
					+'<input type="hidden" name="filename" value="'+ _options.filename +'">'
					+'<input type="hidden" name="image" id="save-png-input" value="">'
				+'</form><iframe style="display:none;" name="save-png-frame" src="" width="1" height="1"></iframe>');

			_isReady = true;
		}
	}

	function _redraw(obj, flag) {
		obj.getOptions().grid.canvasText.show = flag;

		obj.setupGrid();
		obj.draw();
	}


	// Public Methods

	self.save = function(key) {
		var obj = parent.getPlot(key),
			plot = $("#"+key+" canvas.flot-base")[0],
			canvas = document.createElement('canvas');

		_init();

		if (plot.getContext) {
			_redraw(obj, true);

			var img = canvas.getContext('2d'),
				h = plot.height,
				w = plot.width;

			canvas.height = h;
			canvas.width = w;
			img.height = h;
			img.width = w;

			img.fillStyle = _options.bgColor;
			img.fillRect(0, 0, w, h);
			img.drawImage(plot, 0, 0);

			$("#"+key+" .annotation").each(function(){
				var pos = $(this).position(),
					aw  = $(this).width(),
					ah  = $(this).height(),
					text = $(this).text();

				img.fillStyle = _options.annotationBgColor;
				img.fillRect(pos.left + 2, pos.top + 2, aw, ah);

				img.fillStyle = _options.annotationTextColor;
				img.textAlign = "left";
				img.fillText(text, pos.left + 2, pos.top + ah - 1);
			});

			$("#save-png-input").val( canvas.toDataURL("image/png") );
			$("#save-png-form").submit();

			_redraw(obj, false);
		} else {
			window.alert( _options.errorMessage );
		}
	}

	return self;
})(jQuery, RunalyzePlot);