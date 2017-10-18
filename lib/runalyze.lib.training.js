/*
 * Training
 *
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Training = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
		url:				'activity/',
		urlSaveTCX:			'call/ajax.saveTcx.php',
		highlightClass:		'highlight'
	};


	// Private Methods


	// Public Methods

	self.initLinks = function() {
		$("a.training").unbind("click").click(function(e){
			e.preventDefault();
			self.load($(this).attr("rel"));
			return false;
		});
	};

	self.load = function(id, sharedUrl, event) {
		if (event) {
			event.stopPropagation();
		}

		var $w = $(window);

		if ($w.scrollTop() + $w.height() < $("#statistics-inner").offset().top) {
			$('html, body').stop().animate({
				scrollTop: $("#statistics").offset().top - 43
			}, 1000);
		}

		Parent.Statistics.load( sharedUrl || self.url(id) );

		self.removeHighlighting();
		self.addHighlighting(id);
	};

	self.reload = function() {
		if (Parent.Statistics.showsTraining())
			Parent.Statistics.reload();
	};

	self.isUrl = function(urlToCheck) {
		return (urlToCheck.lastIndexOf( options.url, 0 ) === 0);
	};

	self.url = function(id) {
		return options.url + id.toString();
	};

	self.removeHighlighting = function() {
		$("#data-browser").find("tr.training").removeClass( options.highlightClass );
	};

	self.addHighlighting = function(id) {
		$("#training_"+id).addClass( options.highlightClass );
	};

	self.saveTcx = function(xml, activityId, index, total, activities) {
		$.post(options.urlSaveTCX, {'activityId': activityId, 'data': xml}, function(){
			if (index == total) {
				self.loadSavedTcxs(activities);
			}
		});
	};

	self.loadSavedTcxs = function(activityIds) {
		Parent.Overlay.container().loadDiv($("form#training").attr("action"), {'activityIds': activityIds, 'data': 'FINISHED'});
	};

	self.loadXML = function(xml) {
		Parent.Overlay.container().loadDiv($("form#training").attr("action"), {'data': xml});
	};

	self.deleteActivity = function(id, confirmMsg, settings) {
		if (confirmMsg) {
			if (!window.confirm(confirmMsg)) {
				return false;
			}
		}

		settings = $.extend({}, {size:'small', useOverlay: false}, settings);

		if (settings.useOverlay) {
			Parent.Overlay.load(
				Parent.Training.url(id) + "/delete",
				settings
			);

			return false;
		}

		$.ajax(self.url(id) + "/delete", settings);
	};


	self.changePrivacyOfActivity = function(id, settings) {
		$.ajax(self.url(id) + "?action=changePrivacy&silent=true", settings);
	};

	Parent.addLoadHook('init-training-links', self.initLinks);

	return self;
})(jQuery, Runalyze);
