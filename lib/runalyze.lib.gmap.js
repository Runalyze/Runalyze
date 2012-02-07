/*
 * Additional features for gmap3
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzeGMap = {
			minimumDistanceForPolylineHover: 0.005,
			mouseIsOverPolyline: false,
			polylines: [],

			options: {
				mapTypeId: google.maps.MapTypeId.HYBRID,
				mapTypeControl: false,
				navigationControl: false,
				scrollwheel: true,
				streetViewControl: false
			},

			optionsForPolyline: {
				strokeColor: "#FF5500",
				strokeOpacity: 1.0,
				strokeWeight: 2
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			setPolylineOptions: function(opt) {
				this.optionsForPolyline = $.extend({}, this.optionsForPolyline, opt);
				return this;
			},

			init: function(id) {
				this.id = id;

				$(this.id).gmap3( $.extend({action:'init'}, this.options) );

				return this;
			},

			setMapType: function(type) {
				if (type == "G_NORMAL_MAP")
					this.options.mapTypeId = google.maps.MapTypeId.ROADMAP;
				else if (type == "G_SATELLITE_MAP")
					this.options.mapTypeId = google.maps.MapTypeId.SATELLITE;
				else if (type == "G_HYBRID_MAP")
					this.options.mapTypeId = google.maps.MapTypeId.HYBRID;
				else if (type == "G_PHYSICAL_MAP")
					this.options.mapTypeId = google.maps.MapTypeId.TERRAIN;

				return this;
			},

			addMarkers: function(markersToAdd, hideWindow) {
				$(this.id).gmap3({
					action: 'addMarkers',
					markers: markersToAdd,
					marker: {
						options: { draggable: false },
						events: {
							mouseover: function(marker, event, data) {
								$("#mapOverlay").remove();
								$(RunalyzeGMap.id).gmap3({
									action: 'addOverlay',
									latLng: marker.position,
									offset: { y: -20, x: 10},
									content: '<div id="mapOverlay" class="hoverTip withLeftArrow">'+data+'</div>'
								});
							},
							mouseout: function() {
								$("#mapOverlay").remove();
							}
						}
					}
				});
			},

			addPolyline: function(pathToSet) {
				this.polylines.push(pathToSet);

				$(this.id).gmap3({
					action: 'addPolyline',
					options: RunalyzeGMap.optionsForPolyline,
					path: pathToSet,
					events: {
						mouseover: function(path, position) {
							path.setOptions({
								strokeWeight: 8,
								strokeOpacity: 0.5
							});
						},
						mousemove: function(path, position) {
							var data = getDataForPolylineAt( position.latLng.lat(), position.latLng.lng() ),
								offset = getOffsetFor( path.getMap(), position );

							if (!RunalyzeGMap.mouseIsOverPolyline) {
								$("#mapOverlay").remove();
								$(RunalyzeGMap.id).append('<div id="mapOverlay" class="hoverTip withLeftArrow" />');
							}

							$("#mapOverlay").css({top: offset.y-20, left: offset.x+10}).html(data);
						},
						mouseout: function(path, position) {
							path.setOptions({
								strokeWeight: RunalyzeGMap.optionsForPolyline.strokeWeight,
								strokeOpacity: RunalyzeGMap.optionsForPolyline.strokeOpacity
							});
							$("#mapOverlay").remove();
						}
					}
				}, {
					action: 'autofit'
				});

				return this;
			}
	}

	function getDataForPolylineAt(lat, lng) {
		var minimumDistance = 9999999,
			minimumData = '',
			numPoly = RunalyzeGMap.polylines.length;

		for (var p = 0; p < numPoly; p++) {
			var numPoints = RunalyzeGMap.polylines[p].length;
			for (var i = 0; i < numPoints; i++) {
				var currentDistance = distance(lat, lng, RunalyzeGMap.polylines[p][i][0], RunalyzeGMap.polylines[p][i][1]);

				if (currentDistance < minimumDistance) {
					minimumDistance = currentDistance;
					minimumData = RunalyzeGMap.polylines[p][i][2];

					if (currentDistance < RunalyzeGMap.minimumDistanceForPolylineHover)
						return minimumData;
				}
			}
		}

		return minimumData;
	}

	function getOffsetFor(map, position) {
		var scale = Math.pow(2, map.getZoom());
		var nw = new google.maps.LatLng(
		    map.getBounds().getNorthEast().lat(),
		    map.getBounds().getSouthWest().lng()
		);
		var worldCoordinateNW = map.getProjection().fromLatLngToPoint(nw);
		var worldCoordinate = map.getProjection().fromLatLngToPoint(position.latLng);
		var pixelOffset = new google.maps.Point(
		    Math.floor((worldCoordinate.x - worldCoordinateNW.x) * scale),
		    Math.floor((worldCoordinate.y - worldCoordinateNW.y) * scale)
		);

		return pixelOffset;
	}

	function distance(lat1, lon1, lat2, lon2) {
		var radlat1 = Math.PI * lat1/180;
		var radlat2 = Math.PI * lat2/180;
		var radlon1 = Math.PI * lon1/180;
		var radlon2 = Math.PI * lon2/180;
		var theta = lon1-lon2;
		var radtheta = Math.PI * theta/180;
		var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
		dist = Math.acos(dist) * 180/Math.PI * 60 * 1.1515;

		return dist * 1.609344;
	}

	if (!window.RunalyzeGMap)
		window.RunalyzeGMap = RunalyzeGMap;
})();