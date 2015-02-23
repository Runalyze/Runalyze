/*
 * Lib for configurations in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Config = (function($, Parent){

	// Public

	var self = {};


	// Private


	// Private Methods

	function update(key, value) {
		if (!Parent.Options.isSharedView()) {
			$.ajax('call/ajax.change.Config.php?key='+key+'&value='+value);
		}
	}


	// Public Methods

	self.ignoreActivityID = function(id) {
		update('garmin-ignore', id);
	};

	self.setLeafletLayer = function(layer) {
		update('leaflet-layer', layer);
	};

	self.setActivityFormLegend = function(name, flag) {
		update('show-'+name, flag);
	};

	return self;
})(jQuery, Runalyze);