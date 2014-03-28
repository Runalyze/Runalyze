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

	var _id = '',
		_ready = false,
		_object = null,
		_options = {
			visible: {
				layers: true,
				scale: true
			},

			layer: "OpenStreetMap"
		},
		_mapOptions = {},
		_controls = {
			layers: null, // Will be set later
			scale: L.control.scale({
				imperial: false
			})
		};


	// Private Methods

	function _initLayers() {
		self.Layers = self.getNewLayers();
		_controls.layers = L.control.layers( self.Layers );
	}

	function _setMapOptions(opt) {
		_mapOptions = $.extend({}, _mapOptions, opt);
	}

	function _initControls() {
		if (_options.visible.layers && _controls.layers) {
			_controls.layers.addTo( self.map() );
		}

		if (_options.visible.scale && _controls.scale)
			_controls.scale.addTo( self.map() );

		$('<a class="leaflet-control-zoom-full" href="javascript:RunalyzeLeaflet.toggleFullscreen();" title="Fullscreen"><i class="fa fa-expand"></i></a>').insertAfter('.leaflet-control-zoom-in');

		_object.on('baselayerchange', function(e){
			self.setDefaultLayer(e.name);

			if (_ready)
				Runalyze.changeConfig('TRAINING_MAPTYPE', e.name);
		});
	}

	function _initTooltip() {
		_object.on('mouseover', function(){
			$('<div id="polyline-info" class="tooltip right" style="display:none;"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>').appendTo($('#'+_id));
		});
		_object.on('mouseout', function(){
			$('#polyline-info').remove();
		});
	}


	// Public Methods

	self.init = function(id, mapOptions) {
		if (_object !== null) {
			self.Routes.removeAllRoutes();
			_object.remove();
		}

		_ready = false;
		_initLayers();
		_setMapOptions(mapOptions);
		_id = id;
		_object = L.map(_id, _mapOptions).addLayer( self.Layers[_options.layer] );

		_initControls();
		_initTooltip();
		_ready = true;

		return self;
	};

	self.map = function() {
		return _object;
	};

	self.id = function() {
		return _id;
	}

	self.setDefaultLayer = function(layer) {
		_options.layer = layer;

		return self;
	};

	self.toggleFullscreen = function() {
		if ($('#'+_id).hasClass('fullscreen'))
			self.exitFullscreen();
		else
			self.enterFullscreen();
	};

	self.enterFullscreen = function() {
		$('#'+_id).addClass('fullscreen');
		$(".leaflet-control-zoom-full > i").removeClass('fa-expand').addClass('fa-compress');

		_object._onResize();
	};

	self.exitFullscreen = function() {
		$('#'+_id).removeClass('fullscreen');
		$(".leaflet-control-zoom-full > i").addClass('fa-expand').removeClass('fa-compress');

		_object._onResize();
	};

	return self;
})(jQuery);