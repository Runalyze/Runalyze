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
		url:				'call/call.Training.display.php?id=',
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

	self.load = function(id, sharedUrl) {
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
		$("#data-browser tr.training").removeClass( options.highlightClass );
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

	Parent.addLoadHook('init-training-links', self.initLinks);

	return self;
})(jQuery, Runalyze);