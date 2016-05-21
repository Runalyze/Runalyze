/**
 * Flot plugin to extend options
 */
(function ($) {
	var options = {
		xaxis: {
			label: ''
		}
	};

	function init(plot) {
		// Nothing to do here
	}

	$.plot.plugins.push({
		init: init,
		options: options,
		name: 'flot.extend.options',
		version: '1.0'
	});
})(jQuery);

