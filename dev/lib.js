/*
 * ...
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var Lib = (function($){

	// Public

	var self = {};


	// Private

	var id = '';

	var options = {
		
	};


	// Private Methods

	function private() {
		
	}


	// Public Methods

	self.init = function(newId, opt) {
		id = newId;
		options = $.extend({}, options, opt);

		return self;
	};

	return self;
})(jQuery);