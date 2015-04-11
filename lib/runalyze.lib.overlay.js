/*
 * Overlay
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Overlay = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
		selectorContainer:	'#ajax',
		selectorBackground:	'#overlay',
		selectorAll:		'#overlay, #ajax, #ajax-navigation',
		selectorOuter:		'#ajax-outer',
		selectorNavigation:	'#ajax-navigation',
		classBig:			'big-window',
		classSmall:			'small-window',
		classFullscreen:	'fullscreen',
		classBody:			'overlay'
	};

	var $container;
	var $background;
	var $allElements;
	var $outer;


	// Private Methods

	function initObjects() {
		$container = $( options.selectorContainer );
		$background = $( options.selectorBackground );
		$allElements = $( options.selectorAll );
		$outer = $( options.selectorOuter );
	}

	function addBodyClass() {
		Parent.body().addClass( options.classBody );
	}

	function removeBodyClass() {
		Parent.body().removeClass( options.classBody );
	}

	function wrapEverything() {
		if (!$("#ajax-outer").length) {
			$("body > :not(#flot-loader, #copy, #error-toolbar, script)").wrapAll('<div id="ajax-outer"><div id="ajax" class="panel"></div></div>');
			Parent.body().prepend('<div id="overlay"></div>');

			$("#ajax").css('margin-top', '50px');
			$("#ajax, #overlay").show().fadeTo( Parent.Options.fadeSpeed(), 1);
		}

		addBodyClass();
	}

	function addObjects() {
		Parent.body().prepend('<div id="overlay"></div><div id="ajax-outer"><div id="ajax" class="panel"></div></div>');
	}

	function bindEsc() {
		$(document).keyup(function(e) {
			if (e.keyCode == 27)
				self.close();
		});
	}


	// Public Methods

	self.init = function() {
		if (!Parent.hasContainer()) {
			wrapEverything();
		} else if (!Parent.isReady()) {
			addObjects();
		}

		initObjects();
		bindEsc();

		$outer.click(function(e){
			if (e.target == e.currentTarget)
				self.close();
		});

		return self;
	};

	self.bindLinks = function() {
		$("a.window").unbind("click").click(function(e){
			e.preventDefault();
			self.load( $(this).attr("href"), { size: $(this).attr("data-size") } );

			return false;
		});
	};

	self.container = function() {
		return $container;
	};

	self.load = function(url, settings, data) {
		addBodyClass();

		if (!$background.is(':visible')) {
			$container.removeClass( options.classSmall ).removeClass( options.classBig );
		}

		if (settings && settings.size) {
			if (settings.size == 'small') {
				$container.removeClass( options.classBig );
				$container.addClass( options.classSmall );
			} else if (settings.size == 'big') {
				$container.removeClass( options.classSmall );
				$container.addClass( options.classBig );
			}
		}

		$background.show().fadeTo( Parent.Options.fadeSpeed(), 1 );
		$container.addClass( Parent.Options.loadingClass() ).show().fadeTo( Parent.Options.fadeSpeed(), 1 );

		$container.loadDiv(url, data, settings);
	};

	self.addCloseButton = function() {
		if ($('#close').length == 0) {
			$container.prepend('<div id="close" class="black-rounded-icon" onclick="Runalyze.Overlay.close()"><i class="fa fa-fw fa-times"></i></div>');
		}
	};

	self.close = function() {
		if (Parent.hasContainer()) {
			$allElements.fadeTo( Parent.Options.fadeSpeed(), 0, function(){
				$allElements.hide();
				self.removeClasses();
				removeBodyClass();
				$(options.selectorNavigation).remove();
			});
		}
	};

	self.toggleFullscreen = function() {
		$container.toggleClass( options.classFullscreen );
	};

	self.removeClasses = function() {
		$container.removeClass( options.classBig ).removeClass( options.classSmall ).removeClass( options.classFullscreen );
	};

	Parent.addInitHook('init-overlay', self.init);
	Parent.addLoadHook('init-overlay-links', self.bindLinks);

	return self;
})(jQuery, Runalyze);