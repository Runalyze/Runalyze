/*
 * ...
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var Lib = (function($){

	// Public

	var self = {};


	// Private

	var _id = '',
		_options = {
		};


	// Private Methods

	function _private() {
	}


	// Public Methods

	self.init = function(id, opt) {
		_id = id;
		_options = $.extend({}, _options, opt);

		return self;
	};

	return self;
})(jQuery);