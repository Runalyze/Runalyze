Runalyze.Notifications = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
	    // TODO:
        // - urls (or should they be read from dom somehow?)
        // - elements: templates (message with/-out link)
        // - element for 'No new notifications'
	};


	// Private Methods

	function initElements() {
	    // TODO:
		// - set template for message with link (read existing element and remove that from dom)
        // - set template for message without link (read existing element and remove that from dom)
	}

	function initCronjob() {
	    // TODO:
        // - set current timestamp (maybe use some dirty hack to use null as timestamp for tpl.Frontend.header.php as it won't know notification repository)
        // - set timeout
	}

	function bindNotificationLinks() {
	    // TODO:
        // - clicking a notification in the drop down menu shoud mark it as read
    }

    function checkForNewMessages() {

    }

	// Public Methods

	self.init = function() {
		initElements();
		initCronjob();
	};

	Parent.addLoadHook('init-notifications', self.init);

	return self;
})(jQuery, Runalyze);
