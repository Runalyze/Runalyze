/*
 * Additional features for Leaflet
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzeLeaflet.Routes = (function($, parent){

	// Public

	var self = {};


	// Private

	var _epsilon = 0.0003,
		_mouseover = false,
		_mousemovebounded = false,
		_minZoomForMarker = 13,
		_tooltip = '#polyline-info .tooltip-inner',
		_objects = {},
		_options = {
			defaults: {
				color: '#ff8000',
				weight: 3,
				opacity: 1
			},

			hover: {
				weight: 8,
				opacity: 0.7
			}
		};


	// Private Methods

	function _pushMarker(id, marker) {
		_objects[id].marker.push(marker);

		marker.on('mouseover', function(e){
			$(_tooltip).html( e.target.options.tooltip ).parent().show();
			_positionTooltip(e);
			$(_tooltip).parent().addClass('in');
		}).on('mouseout', function(e){
			$(_tooltip).parent().removeClass('in').hide();
		});
	}

	function _decideMarkerVisibility() {
		for (var id in _objects) {
			if (parent.map().getZoom() >= _minZoomForMarker)
				parent.map().addLayer( _objects[id].markergroup );
			else
				parent.map().removeLayer( _objects[id].markergroup );
		}
	}

	function _bindRouteForHover(id) {
		_objects[id].polyline.on('mouseover', _onOver, {id: id});
		_objects[id].polyline.on('mouseout', _onOut, {id: id});
	}

	function _bindMouseMove() {
		if (!_mousemovebounded)
			parent.map().on('mousemove', _onMove);

		_mousemovebounded = true;
	}

	function _onMove(e) {
		if (_mouseover !== false) {
			_setTooltipText( _getSegmentsInfoAt(e.latlng) );
			_positionTooltip(e);
		}
	}

	function _onOver(e) {
		if (_objects[this.id].segmentsInfo.length) {
			$(_tooltip).parent().show();

			_mouseover = this.id;
			_setTooltipText( _getSegmentsInfoAt(e.latlng) );
			_positionTooltip(e);

			$(_tooltip).parent().addClass('in');
		}

		_objects[this.id].polyline.setStyle({
			weight: _options.hover.weight,
			opacity: _options.hover.opacity
		});
	}

	function _onOut(e) {
		_mouseover = false;

		$(_tooltip).parent().removeClass('in').hide();

		_objects[this.id].polyline.setStyle({
			weight: _objects[this.id].options.weight,
			opacity: _objects[this.id].options.opacity
		});
	}

	function _getSegmentsInfoAt(latlng) {
		var id = _mouseover, i = 0;

		for (var s = 0, numSegments = _objects[id].segments.length; s < numSegments; s++) {
			for (var p = 0, numPoints = _objects[id].segments[s].length; p < numPoints; p++) {
				var point = _objects[id].segments[s][p];
				if (Math.abs(point[0] - latlng.lat) < _epsilon && Math.abs(point[1] - latlng.lng) < _epsilon) 
					return _objects[id].segmentsInfo[s][p];
			}
		}

		return {};
	}

	function _positionTooltip(e) {
		var $e = $(_tooltip).parent(), height = $e.outerHeight(),
			layerX = e.originalEvent.clientX - $('#'+parent.id()).offset().left + window.pageXOffset,
			layerY = e.originalEvent.clientY - $('#'+parent.id()).offset().top + window.pageYOffset;

		$e.css({
			top: layerY - height/2,
			left: layerX + 10
		});
	}

	function _setTooltipText(info) {
		var text = '';

		for (var label in info)
			text = text + '<strong>' + label + ':</strong> ' + info[label] + '<br>';

		if (text != '')
			$(_tooltip).html( text.substr(0, text.length - 4) );
	}


	// Public Methods

	self.addRoute = function(id, object) {
		object.segments = object.segments || [];
		object.segmentsInfo = object.segmentsInfo || [];
		object.marker = object.marker || [];
		object.markertopush = object.markertopush || [];
		object.markergroup = L.layerGroup();
		object.visible = object.visible !== false;
		object.hoverable = object.hoverable !== false;
		object.autofit = object.autofit !== false;
		object.options = $.extend( _options.defaults, object.options );
		object.polyline = L.multiPolyline( object.segments, object.options );

		_objects[id] = object;

		for (var i in object.markertopush)
			_pushMarker(id, object.markertopush[i]);

		if (object.visible) {
			if (object.hoverable)
				_bindRouteForHover(id);

			_objects[id].markergroup = L.layerGroup( _objects[id].marker );

			self.showRoute(id);
		}

		if (!this.mousemovebounded)
			_bindMouseMove();

		parent.map().on('zoomend', _decideMarkerVisibility);
	};

	self.fitTo = function(id) {
		parent.map().fitBounds( _objects[id].polyline.getBounds(), { animate: false } );

		_decideMarkerVisibility();
	};

	self.showRoute = function(id) {
		parent.map().addLayer( _objects[id].polyline );

		if (parent.map().getZoom() >= _minZoomForMarker)
			parent.map().addLayer( _objects[id].markergroup );

		if (_objects[id].autofit)
			self.fitTo(id);

		_objects[id].visible = true;
	};

	self.hideRoute = function(id) {
		parent.map().removeLayer( _objects[id].polyline );
		parent.map().removeLayer( _objects[id].markergroup );
		_objects[id].visible = false;
	};

	self.toggleRoute = function(id) {
		if (_objects[id].visible)
			self.hideRoute(id);
		else
			self.showRoute(id);
	};

	self.showAllRoutes = function() {
		for (var id in _objects)
			self.showRoute(id);
	};

	self.hideAllRoutes = function() {
		for (var id in _objects)
			self.hideRoute(id);
	};

	self.toggleAllRoutes = function() {
		for (var id in _objects)
			self.toggleRoute(id);
	};

	self.removeAllRoutes = function() {
		self.hideAllRoutes();

		_objects = {};
	};

	self.segmentFromStrings = function(lats, lngs, sep) {
		lats = lats.split(sep);
		lngs = lngs.split(sep);

		var num = lats.length;
		var latlngs = [];

		for (var i = 0; i < num; i++)
			latlngs[i] = [parseFloat(lats[i]), parseFloat(lngs[i])];

		return latlngs;
	};

	self.distIcon = function(dist) {
		return L.divIcon({className: 'polyline-marker polyline-marker-dist', html: Math.ceil(dist), iconSize: [12, 12], iconAnchor: [8, 8]});
	};

	self.startIcon = function() {
		return L.divIcon({className: 'polyline-marker polyline-marker-start', iconSize: [6, 6], iconAnchor: [6, 6]});
	};

	self.endIcon = function() {
		return L.divIcon({className: 'polyline-marker polyline-marker-end', iconSize: [6, 6], iconAnchor: [6, 6]});
	};

	return self;
}(jQuery, RunalyzeLeaflet));