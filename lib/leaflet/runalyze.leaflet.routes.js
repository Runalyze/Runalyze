/*
 * Additional features for Leaflet
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzeLeaflet.Routes = (function($, parent, Math){

	// Public

	var self = {
		routeid: 0
	};


	// Private

	var positionMarker;
	var epsilon = 0.0003;
	var epsilonKM = 0.01;
	var maxBisectionIterations = 20;
	var mouseover = false;
	var mousemovebounded = false;
	var minZoomForMarker = 13;
	var tooltip = '#polyline-info .tooltip-inner';

	var objects = {};

	var options = {
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

	function pushMarker(id, marker) {
		objects[id].marker.push(marker);

		marker.on('mouseover', function(e){
			$(tooltip).html( e.target.options.tooltip ).parent().show();
			positionTooltip(e);
			$(tooltip).parent().addClass('in');
		}).on('mouseout', function(e){
			$(tooltip).parent().removeClass('in').hide();
		});
	}

	function decideMarkerVisibility() {
		for (var id in objects) {
			if (parent.map().getZoom() >= minZoomForMarker)
				parent.map().addLayer( objects[id].markergroup );
			else
				parent.map().removeLayer( objects[id].markergroup );
		}
	}

	function bindRouteForHover(id) {
		objects[id].polyline.on('mouseover', onOver, {id: id});
		objects[id].polyline.on('mouseout', onOut, {id: id});
	}

	function bindMouseMove() {
		if (!mousemovebounded)
			parent.map().on('mousemove', onMove);

		mousemovebounded = true;
	}

	function onMove(e) {
		if (mouseover !== false) {
			setTooltipText( getSegmentsInfoAt(e.latlng) );
			positionTooltip(e);
		}
	}

	function onOver(e) {
		if (objects[this.id].segmentsInfo.length) {
			$(tooltip).parent().show();

			mouseover = this.id;
			setTooltipText( getSegmentsInfoAt(e.latlng) );
			positionTooltip(e);
			//setPositionMarker( e.latlng );

			$(tooltip).parent().addClass('in');
		}

		objects[this.id].polyline.setStyle({
			weight: options.hover.weight,
			opacity: options.hover.opacity
		});
	}

	function onOut(e) {
		mouseover = false;

		$(tooltip).parent().removeClass('in').hide();

		objects[this.id].polyline.setStyle({
			weight: objects[this.id].options.weight,
			opacity: objects[this.id].options.opacity
		});
	}

	function getSegmentsInfoAt(latlng) {
		var id = mouseover;

		for (var s = 0, numSegments = objects[id].segments.length; s < numSegments; s++) {
			for (var p = 0, numPoints = objects[id].segments[s].length; p < numPoints; p++) {
				var point = objects[id].segments[s][p];
				if (Math.abs(point[0] - latlng.lat) < epsilon && Math.abs(point[1] - latlng.lng) < epsilon) 
					return objects[id].segmentsInfo[s][p];
			}
		}

		return {};
	}

	function positionTooltip(e) {
		var $e = $(tooltip).parent(), height = $e.outerHeight(),
			layerX = e.originalEvent.clientX - $('#'+parent.id()).offset().left + window.pageXOffset,
			layerY = e.originalEvent.clientY - $('#'+parent.id()).offset().top + window.pageYOffset;

		$e.css({
			top: layerY - height/2,
			left: layerX + 10
		});
	}

	function setTooltipText(info) {
		if (!info.length)
			return;

		var text = '';
		var labels = objects[mouseover].segmentsInfoLabels;

		for (var i = 0; i < labels.length; ++i)
			if (labels[i])
				text = text + '<strong>' + labels[i] + ':</strong> ' + info[i] + '<br>';

		if (text != '')
			$(tooltip).html( text.substr(0, text.length - 4) );
	}

	function setPositionMarker(pos) {
		if (parent.map()) {
			if (!positionMarker) {
				positionMarker = L.marker(pos, {
					icon: self.posIcon()
				}).addTo(parent.map());
			} else {
				positionMarker.setLatLng(pos);
			}
		}
	}


	// Public Methods

	self.addRoute = function(id, object) {
		object.segments = object.segments || [];
		object.segmentsInfoLabels = object.segmentsInfoLabels || [];
		object.segmentsInfo = object.segmentsInfo || [];
		object.marker = object.marker || [];
		object.markertopush = object.markertopush || [];
		object.markergroup = L.layerGroup();
		object.visible = object.visible !== false;
		object.hoverable = object.hoverable !== false;
		object.autofit = object.autofit !== false;
		object.options = $.extend( options.defaults, object.options );
		object.polyline = L.multiPolyline( object.segments, object.options );

		objects[id] = object;

		for (var i in object.markertopush)
			pushMarker(id, object.markertopush[i]);

		if (object.visible) {
			if (object.hoverable)
				bindRouteForHover(id);

			objects[id].markergroup = L.layerGroup( objects[id].marker );

			self.showRoute(id);
		}

		bindMouseMove();

		parent.map().on('zoomend', decideMarkerVisibility);
	};

	self.fitTo = function(id) {
		parent.map().fitBounds( objects[id].polyline.getBounds(), { animate: false } );

		decideMarkerVisibility();
	};

	self.showRoute = function(id) {
		parent.map().addLayer( objects[id].polyline );

		if (parent.map().getZoom() >= minZoomForMarker)
			parent.map().addLayer( objects[id].markergroup );

		if (objects[id].autofit)
			self.fitTo(id);

		objects[id].visible = true;
	};

	self.hideRoute = function(id) {
		parent.map().removeLayer( objects[id].polyline );
		parent.map().removeLayer( objects[id].markergroup );
		objects[id].visible = false;
	};

	self.toggleRoute = function(id) {
		if (objects[id].visible)
			self.hideRoute(id);
		else
			self.showRoute(id);
	};

	self.showAllRoutes = function() {
		for (var id in objects)
			self.showRoute(id);
	};

	self.hideAllRoutes = function() {
		for (var id in objects)
			self.hideRoute(id);
	};

	self.toggleAllRoutes = function() {
		for (var id in objects)
			self.toggleRoute(id);
	};

	self.removeAllRoutes = function() {
		self.hideAllRoutes();

		objects = {};
		mousemovebounded = false;
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

    self.posIcon = function() {
        return L.divIcon({className: 'polyline-marker polyline-marker-pos', iconSize: [6, 6], iconAnchor: [6, 6]});
    };

	self.movePosMarker = function(km) {
		var id = self.routeid;

		if (typeof id === "undefined" || typeof objects[id] === "undefined")
			return;

		var upper;
		var index;
		var lower;
		var pos = [0,0];
		var counter = 0;

		for (var s = 0; s < objects[id].segmentsInfo.length; ++s) {
			var segmentLength = objects[id].segmentsInfo[s].length;

			if (objects[id].segmentsInfo[s][0][0] < km && km < objects[id].segmentsInfo[s][segmentLength-1][0]) {
				counter = 0;
				lower = 0;
				index = 0;
				upper = segmentLength - 1;

				while ((upper - lower) > 1 && counter < maxBisectionIterations && Math.abs(objects[id].segmentsInfo[s][index][0] - km) > epsilonKM) {
					counter++;

					index = Math.floor( (upper+lower)/2 );

					if (objects[id].segmentsInfo[s][index][0] > km) {
						upper = index;
					} else {
						lower = index;
					}
				}

				if (lower != upper) {
					var ratio = (km - objects[id].segmentsInfo[s][lower][0]) / (objects[id].segmentsInfo[s][upper][0] - objects[id].segmentsInfo[s][lower][0]);
					pos = [
						objects[id].segments[s][lower][0] + ratio * (objects[id].segments[s][upper][0] - objects[id].segments[s][lower][0]),
						objects[id].segments[s][lower][1] + ratio * (objects[id].segments[s][upper][1] - objects[id].segments[s][lower][1])
					];
				} else {
					pos = objects[id].segments[s][index];
				}
			}
		}

		setPositionMarker(pos);
	};

	self.unsetPosMarker = function() {
		setPositionMarker([0,0]);
	};

	self.deletePosMarker = function() {
		positionMarker = false;
	};

	return self;
}(jQuery, RunalyzeLeaflet, Math));