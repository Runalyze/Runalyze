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
	var $reloadLink;


	// Private Methods

	function initObjects() {
		$container = $( options.selectorContainer );
		$reloadLink = $( options.selectorReload );
	}


	// Public Methods

	self.init = function() {
		initObjects();
	};

	self.reload = function() {
		$reloadLink.trigger('click');
	};

	Parent.addInitHook('init-databrowser', self.init);

	return self;
})(jQuery, Runalyze);