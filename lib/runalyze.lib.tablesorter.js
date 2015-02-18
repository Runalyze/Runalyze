/*
 * Additional features for tablesorter
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Tablesorter = (function($, Parent){

	// Public

	var self = {};


	// Private

	var ids = [];


	// Public Methods

	self.update = function() {
		for (var i = 0; i < ids.length; i++) {
			if ($("#"+ids[i]).length == 0)
				delete ids[i];
		}

		return self;
	};

	self.has = function(id) {
		return ($.inArray(id, ids) != -1);
	};

	self.add = function(id) {
		ids.push(id);
	};

	Parent.addLoadHook('update-tablesorter', self.update);

	return self;
})(jQuery, Runalyze);

/*
 * Extend $.tablesorter
 */
(function($, parent, RunalyzeSorter){
	var textExtraction = function (node) {
		return $.trim($(node).text()).replace(/[\/km]/,"");
	};

	$.fn.extend({
		tablesorterAutosort: function(reinit) {
			var id = this.attr("id");

			if (typeof reinit == "undefined") {
				if (RunalyzeSorter.has(id)) {
					this.trigger('update');
					return this;
				}

				RunalyzeSorter.add(id);
			}

			return this.addClass('sortable').tablesorter({
				textExtraction: textExtraction
			});
		},

		tablesorterWithPager: function(reinit) {
			var id = this.attr("id");

			if (typeof reinit == "undefined") {
				if (RunalyzeSorter.has(id)) {
					this.trigger('update');
					return this;
				}

				RunalyzeSorter.add(id);
			}

			return this.addClass('sortable').tablesorter({
				textExtraction: textExtraction
			}).tablesorterPager({
				container: $("#pager"),
				output: "{page} / {totalPages}",
				positionFixed: false,
				size: 20
			});
		}
	});

	parent.addParser({
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

	parent.addParser({
		id: 'distance',
		is: function(s) {
			return false;
		},
		format: function(s) {
			if (s.indexOf("km") !== -1)
				return parseFloat(s.replace(/[ km]/g, '').replace(/[.]/g, '').replace(/[,]/g, '.'));
			if (s.indexOf("m") !== -1)
				return parseFloat(s.replace(/[m]/g, '').replace(/[.]/g, '')) / 1000;

			return parseFloat(s);
		},
		type: 'numeric'
	});

	parent.addParser({
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

	parent.addParser({
		id: 'resulttime',
		is: function(s) {
			return false;
		},
		format: function(s) {
			var days,h,m;
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

	parent.addParser({
		id: 'x',
		is: function(s) {
			return false;
		},
		format: function(s) {
			return s.replace(/[x]/g, '');
		},
		type: 'numeric'
	});

	parent.addParser({
		id: 'order',
		is: function(s) {
			return false;
		},
		format: function(s) {
			return s.replace(/[(]/g, '99999').replace(/[.)]/g, '');
		},
		type: 'numeric'
	});
})(jQuery, jQuery.tablesorter, Runalyze.Tablesorter);