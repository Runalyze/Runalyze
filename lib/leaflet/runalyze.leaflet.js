/*
 * Additional features for Leaflet
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
var RunalyzeLeaflet = (function($){
	// @uses self.Layers

	// Public

	var self = {};


	// Private

	var id = '';
	var ready = false;
	var object = null;
	var options = {
		visible: {
			layers: true,
			scale: true
		},

		layer: "OpenStreetMap"
	};
	var mapOptions = {
		scrollWheelZoom: false
	};
	var controls = {
		layers: null, // Will be set later
		scale: L.control.scale({
			imperial: false
		})
	};


	// Private Methods

	function initLayers() {
		self.Layers = self.getNewLayers();
		controls.layers = L.control.layers( self.Layers );
	}

	function setMapOptions(opt) {
		mapOptions = $.extend({}, mapOptions, opt);
	}

	function initControls() {
		if (options.visible.layers && controls.layers) {
			controls.layers.addTo( self.map() );
		}

		if (options.visible.scale && controls.scale)
			controls.scale.addTo( self.map() );

		$('<a class="leaflet-control-zoom-full" href="javascript:RunalyzeLeaflet.toggleFullscreen();" title="Fullscreen"><i class="fa fa-expand"></i></a>').insertAfter('.leaflet-control-zoom-in');

		object.on('baselayerchange', function(e){
			self.setDefaultLayer(e.name);

			if (ready)
				Runalyze.Config.setLeafletLayer(e.name);
		});
	}

	function initTooltip() {
		object.on('mouseover', function(){
			$('<div id="polyline-info" class="tooltip right" style="display:none;"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>').appendTo($('#'+id));
		});
		object.on('mouseout', function(){
			$('#polyline-info').remove();
		});
	}


	// Public Methods

	self.init = function(newID, mapOptions) {
		if (object !== null) {
			self.Routes.removeAllRoutes();
			self.Routes.deletePosMarker();
			object.remove();
		}

		ready = false;
		initLayers();
		setMapOptions(mapOptions);
		id = newID;
		object = L.map(id, mapOptions).addLayer( self.Layers[options.layer] );

		initControls();
		initTooltip();
		ready = true;

		return self;
	};

	self.map = function() {
		return object;
	};

	self.id = function() {
		return id;
	};

	self.setDefaultLayer = function(layer) {
		options.layer = layer;

		return self;
	};

	self.toggleFullscreen = function() {
		if ($('#'+id).hasClass('fullscreen'))
			self.exitFullscreen();
		else
			self.enterFullscreen();
	};

	self.enterFullscreen = function() {
		$('#'+id).addClass('fullscreen');
		$(".leaflet-control-zoom-full > i").removeClass('fa-expand').addClass('fa-compress');

		object._onResize();
	};

	self.exitFullscreen = function() {
		$('#'+id).removeClass('fullscreen');
		$(".leaflet-control-zoom-full > i").addClass('fa-expand').removeClass('fa-compress');

		object._onResize();
	};

	return self;
})(jQuery);