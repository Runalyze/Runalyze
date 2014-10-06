/*
 * DataBrowser
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.DataBrowser = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
		selectorContainer:	'#statistics-nav',
		selectorReload:		'#refreshDataBrowser'
	};

	var $container;


	// Private Methods

	function initObjects() {
		$container = $( options.selectorContainer );
	}


	// Public Methods

	self.init = function() {
		initObjects();
	};

	self.reload = function() {
		$( options.selectorReload ).trigger('click');
	};

	Parent.addInitHook('init-databrowser', self.init);

	return self;
})(jQuery, Runalyze);