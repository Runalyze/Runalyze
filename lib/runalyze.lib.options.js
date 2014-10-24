/*
 * Options
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Options = (function($, parent){

	// Public

	var self = {};


	// Private

	var options = {
		sharedView:		false,
		fadeSpeed:		200,
		loadingClass:	'loading'
	};


	// Private Methods


	// Public Methods

	self.setSharedView = function(flag) {
		options.sharedView = flag || true;
	};

	self.isSharedView = function() {
		return options.sharedView;
	};

	self.fadeSpeed = function() {
		return options.fadeSpeed;
	};

	self.loadingClass = function() {
		return options.loadingClass;
	};

	return self;
})(jQuery, Runalyze);