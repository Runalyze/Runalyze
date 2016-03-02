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

	function initInlineDropdownLinksForActivities() {
		$('#data-browser').find('.submenu .link').unbind('click').click(function(e){
			e.stopPropagation();

			if ($(this).data('action') == 'delete') {
				self.deleteActivity($(this).data('activityid'), $(this).data('confirm'));
			} else if ($(this).data('action') == 'privacy') {
				self.changePrivacyOfActivity($(this).data('activityid'));
			}
		});
	}


	// Public Methods

	self.init = function() {
		initObjects();
		initInlineDropdownLinksForActivities();
	};

	self.reinit = function() {
		initInlineDropdownLinksForActivities();
	};

	self.reload = function() {
		$( options.selectorReload ).trigger('click');
	};

	self.currentTimes = function() {
		var href = $( options.selectorReload ).attr('href');
		var params;
		var start;
		var end;

		href = href.substr(href.indexOf('?')+1);
		params = href.split('&');

		for (var i = 0; i < params.length; i++) {
			var val = params[i].split('=');

			if (val[0] == 'start') {
				start = val[1];
			} else if (val[0] == 'end') {
				end = val[1];
			}
		}

		return {
			start: start,
			end: end
		};
	};

	self.deleteActivity = function(id, confirmMsg) {
		if (confirmMsg) {
			if (!window.confirm(confirmMsg)) {
				return false;
			}
		}

		Parent.Overlay.load(
			Parent.Training.url(id) + "&action=delete",
			{ size: 'small' }
		);
	};

	self.changePrivacyOfActivity = function(id) {
		$("#data-browser-inner").addClass('loading');
		$.ajax(Parent.Training.url(id) + "&action=changePrivacy&silent=true").done(function(){
			self.reload();
		});
	};

	Parent.addInitHook('init-databrowser', self.init);
	Parent.addLoadHook('init-databrowser', self.reinit);

	return self;
})(jQuery, Runalyze);