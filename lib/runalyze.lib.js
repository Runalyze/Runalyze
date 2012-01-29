/*
 * JS-library for Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var Runalyze = {
			options: {
				useTooltip: false,
				useRoundHighlighter: false,
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			init: function(opt) {
				this.setOptions(opt);

				if (this.options.useTooltip)
					this.initTooltip();

				return this;
			},

			initTooltip: function() {
				$("a img").tooltip({
					track: true,
					delay: 0,
					showURL: false
				});
				return this;
			},

			loadDataBrowser: function() {
				
			},

			loadStatistic: function(id) {
				
			},

	}

	if (!window.Runalyze)
		window.Runalyze = Runalyze;
})();