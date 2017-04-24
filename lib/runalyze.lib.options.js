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
		loadingClass:	'loading',
		nokiaAuth:		{ app: 'YOUR-APP-ID', token: 'YOUR-APP-TOKEN' },
		thunderforestApikey:	'',
		MapboxApikey: ''
	};


	// Private Methods


	// Public Methods

	self.setNokiaLayerAuth = function(appId, token) {
		options.nokiaAuth.app = appId;
		options.nokiaAuth.token = token;

		return self;
	};

	self.nokiaAuth = function() {
		return options.nokiaAuth;
	};

	self.setThunderforestLayerAuth = function(apikey) {
		options.thunderforestApikey = apikey;

		return self;
	};

	self.setMapboxLayerAuth = function(accesstoken) {
		options.MapboxApikey = accesstoken;

		return self;
	};

	self.MapboxAuth = function() {
		return options.MapboxApikey;
	};

	self.thunderforestAuth = function() {
		return options.thunderforestApikey;
	};

	self.setSharedView = function(flag) {
		options.sharedView = flag || true;

		return self;
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
