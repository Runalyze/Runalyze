/*
 * Statistics container
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Statistics = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
		selectorMenu:		'#statistics-nav',
		selectorMenuItems:	'#statistics-nav li',
		selectorContent:	'#statistics-inner',
		activeClass:		'active',
		urlStat:			'call/call.Plugin.display.php?id='
	};

	var $menu;
	var $menuItems;
	var $content;

	var currentUrl;


	// Private Methods

	function initObjects() {
		$menu = $( options.selectorMenu );
		$menuItems = $( options.selectorMenuItems );
		$content = $( options.selectorContent );

		if (!Parent.isReady()) {
			self.resetUrl();
		} else {
			$menuItems.removeClass( options.activeClass );
			$menuItems.children("a[href='"+self.currentTabContentUrl+"']").parent().addClass("active");
		}
	}

	function bindLinks() {
		$menuItems.unbind('click').click(function(e) {
			e.preventDefault();

			Parent.Training.removeHighlighting();
			$menuItems.removeClass( options.activeClass );

			$(this).addClass( options.activeClass );

			self.load( $(this).find('a').attr('href') );

			return false;
		});
	}


	// Public Methods

	self.init = function() {
		initObjects();
		bindLinks();
	};

	self.currentUrl = function() {
		return currentUrl;
	};

	self.setUrl = function(url) {
		currentUrl = url;
	};

	self.resetUrl = function() {
		currentUrl = $( options.selectorMenuItems + ':first a' ).attr('href');
	};

	self.showsTraining = function() {
		return Parent.Training.isUrl( currentUrl );
	};

	self.shows = function(id) {
		return (currentUrl.lastIndexOf( options.urlStat + id.toString(), 0 ) === 0);
	};

	self.currentId = function() {
		if (self.showsTraining())
			return currentUrl.substr( Parent.Training.url('').length );

		return currentUrl.substr( options.urlStat.length );
	};

	self.reload = function() {
		if (currentUrl != '') {
			var $link = $menuItems.children("a[href='"+self.currentTabContentUrl+"']");

			if ($link.length) {
				Parent.Training.removeHighlighting();

				$menuItems.removeClass( options.activeClass );
				$link.addClass( options.activeClass );
			}

			self.load( currentUrl );
		}
	};

	self.load = function(url) {
		$content.loadDiv( url );
		currentUrl = url;
	};

	self.loadTraining = function(url) {
		$menuItems.removeClass( options.activeClass );

		self.load(url);
	};

	Parent.addInitHook('init-statistics', self.init);

	return self;
})(jQuery, Runalyze);