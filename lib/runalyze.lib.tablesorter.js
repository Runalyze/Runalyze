/*
 * Additional features for tablesorter
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzeTablesorter = {
			init: function() {
				this.ids = [];

				addParserToTablesorter();
			},

			reInit: function() {
				for (var i = 0; i < this.ids.length; i++) {
					if ($("#"+this.ids[i]).length == 0)
						delete this.ids[i];
				}
			},

			textExtraction: function(node) {
				return $.trim($(node).text()).replace(/[\/km]/,"");
			},
	}

	if (!window.RunalyzeTablesorter)
		window.RunalyzeTablesorter = RunalyzeTablesorter;

	function addParserToTablesorter() {
		$.tablesorter.addParser({
			id: 'germandate',
			is: function(s) {
				return false;
			},
			format: function(s) {
				var a = s.split('.');
				if (a.length < 2)
					return 0;
				a[1] = a[1].replace(/^[0]+/g,"");
				return new Date(a.reverse().join("/")).getTime();
			},
			type: 'numeric'
		});

		$.tablesorter.addParser({
			id: 'distance',
			is: function(s) {
				return false;
			},
			format: function(s) {
				return s.replace(/[ km]/g, '').replace(/[.]/g, '').replace(/[,]/g, '.');
			},
			type: 'numeric'
		});

		$.tablesorter.addParser({
			id: 'temperature',
			is: function(s) {
				return false;
			},
			format: function(s) {
				if (isNaN(parseFloat(s)) || isFinite(s))
					return -99;
				return s.replace(/[ °C]/g, '');
			},
			type: 'numeric'
		});

		$.tablesorter.addParser({
			id: 'resulttime',
			is: function(s) {
				return false;
			},
			format: function(s) {
				var days,h,m,s;
				var ms  = s.split(',');
				var hms = ms[0].split(':');
				ms = (ms.length > 1) ? ms[1].replace(/[s]/g, '') : 0;
				if (hms.length == 3) {
					days = hms[0].split('d ');
					if (days.length == 2)
						h = 24*parseInt(days[0]) + parseInt(days[1]);
					else
						h = days[0];
					m = hms[1]; s = hms[2];
				} else if (hms.length == 2) {
					h = 0;      m = hms[0]; s = hms[1];
				} else {
					h = 0;      m = 0;      s = hms[0];
				}

				return h*60*60 + m*60 + s + ms/100;
			},
			type: 'numeric'
		});

		$.tablesorter.addParser({
			id: 'x',
			is: function(s) {
				return false;
			},
			format: function(s) {
				return s.replace(/[x]/g, '');
			},
			type: 'numeric'
		});
	}
})();

(function($){
	$.fn.extend({
		tablesorterAutosort: function() {
			var id = this.attr("id");

			if (jQuery.inArray(id, RunalyzeTablesorter.ids) != -1)
				return this;

			RunalyzeTablesorter.ids.push(id);

			return this.addClass('sortable').tablesorter({
				textExtraction: RunalyzeTablesorter.textExtraction(),
				widgets:['zebra'],
				widgetZebra:{css:["a1","a2"]}
			});
		},

		tablesorterWithPager: function() {
			var id = this.attr("id");

			if (jQuery.inArray(id, RunalyzeTablesorter.ids) != -1)
				return this;

			RunalyzeTablesorter.ids.push(id);

			return this.addClass('sortable').tablesorter({
				textExtraction: RunalyzeTablesorter.textExtraction(),
				widgets:['zebra'],
				widgetZebra:{css:["a2","a3"]}
			}).tablesorterPager({
				container: $("#pager"),
				seperator: " von ",
				positionFixed: false,
				size: 20
			});
		},
	});
})(jQuery);