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
				mapTypeId: -1,
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
				if (googleIsThere()) {
					this.id = id;
					$(this.id).gmap3( $.extend({action:'init'}, this.options) );

					this.initOSM();
				} else {
					RunalyzeLog.add('ERROR', 'JavaScript-file for GoogleMaps is missing.');
					$(id).append('<p class="error">GoogleMaps ist derzeit nicht verf&uuml;gbar.').css({height:'auto'});
				}

				return this;
			},

			initOSM: function() {
				RunalyzeGMap.get().mapTypes.set("OSM", new google.maps.ImageMapType({
					getTileUrl: function(coord, zoom) {
						return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
					},
					tileSize: new google.maps.Size(256, 256),
					name: "OpenStreetMap",
					maxZoom: 18
				}));


				google.maps.event.addListener(RunalyzeGMap.get(), 'tilesloaded', function () {
					setTimeout(RunalyzeGMap.setMapType, 50);
				});
			},

			setMapType: function(type) {
				if (googleIsThere()) {
					RunalyzeGMap.setCopyright(type);

					if (type == "G_NORMAL_MAP")
						RunalyzeGMap.options.mapTypeId = google.maps.MapTypeId.ROADMAP;
					else if (type == "G_SATELLITE_MAP")
						RunalyzeGMap.options.mapTypeId = google.maps.MapTypeId.SATELLITE;
					else if (type == "G_HYBRID_MAP")
						RunalyzeGMap.options.mapTypeId = google.maps.MapTypeId.HYBRID;
					else if (type == "G_PHYSICAL_MAP")
						RunalyzeGMap.options.mapTypeId = google.maps.MapTypeId.TERRAIN;
					else if (type == "OSM")
						RunalyzeGMap.options.mapTypeId = "OSM";
				}

				return RunalyzeGMap;
			},

			addMarkers: function(markersToAdd, hideWindow) {
				if (googleIsThere()) {
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
				}
			},

			changeMarkerVisibility: function() {
				if ($("#trainingMapMarkerVisibile").hasClass('checked')) {
					RunalyzeGMap.showMarkers();
					Runalyze.changeConfig('TRAINING_MAP_MARKER', 'true');
				} else {
					RunalyzeGMap.hideMarkers();
					Runalyze.changeConfig('TRAINING_MAP_MARKER', 'false');
				}
			},

			hideMarkers: function() {
				var markers = $(this.id).gmap3({action:'get',name:'marker',all: true});
				$.each(markers, function(i, marker){
					marker.setMap(null);
				});
			},

			showMarkers: function() {
				var markers = $(this.id).gmap3({action:'get',name:'marker',all: true}),
					map = $(this.id).gmap3('get');
				$.each(markers, function(i, marker){
					marker.setMap(map);
				});
			},

			get: function() {
				return $(RunalyzeGMap.id).gmap3('get');
			},

			addPolyline: function(pathToSet, withoutHover) {
				if (googleIsThere()) {
					this.polylines.push(pathToSet);

					var eventsOption = {
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
						};

					if (typeof withoutHover != "undefined")
						eventsOption = {};

					$(this.id).gmap3({
						action: 'addPolyline',
						options: RunalyzeGMap.optionsForPolyline,
						path: pathToSet,
						events: eventsOption
					}, {
						action: 'autofit'
					});
				}

				return this;
			},

			resize: function() {
				if (googleIsThere() && $(RunalyzeGMap.id).css('opacity') > 0) {
					if (Runalyze.ajax.is(':visible') && Runalyze.ajax.hasClass('fullscreen')) {
						this.resizeOverlayMap();
					} else {
						if ($(this._parentContainer()).hasClass('fullscreen'))
							$(this.id).height( $(RunalyzeGMap.id).height() + $(this._parentContainer()).height() - $(this._parentContainer()+" .toolbar-box-content").outerHeight() );
						else
							$(this.id).height('');
					}

					google.maps.event.trigger($(RunalyzeGMap.id).gmap3('get'), "resize");
					this.autofit();
				}
			},

			resizeOverlayMap: function() {
				Runalyze.ajax.css('height','auto');
				$(this.id).height( $(this.id).height() + $(window).height() - Runalyze.ajax.outerHeight() );
			},

			setOverlayMapToFullscreen: function() {
				Runalyze.ajax.removeClass('loading').addClass('fullscreen');
				$("#map-fullscreen-link").remove()
				this.resize();
			},

			autofit: function() {
				$(this.id).gmap3({ action: 'autofit' });
			},

			zoomIn: function() {
				var map = $(this.id).gmap3('get');
				map.setZoom(map.getZoom() + 1);
			},

			zoomOut: function() {
				var map = $(this.id).gmap3('get');
				map.setZoom(map.getZoom() - 1);
			},

			changeMapType: function(mapType) {
				this.setMapType(mapType);

				Runalyze.changeConfig('TRAINING_MAPTYPE', mapType);

				var map = $(this.id).gmap3('get');
				map.setMapTypeId(this.options.mapTypeId);
			},

			toggleFullScreen: function() {
				if ($(this._parentContainer()).hasClass('fullscreen'))
					RunalyzeGMap.exitFullScreen();
				else
					RunalyzeGMap.fullScreen();
			},

			fullScreen: function() {
				$(this._parentContainer()).addClass('fullscreen');
				RunalyzeGMap.resize();
				$("#map-fullscreen-link").text('Vollbild verlassen');
			},

			exitFullScreen: function() {
				$(this._parentContainer()).removeClass('fullscreen');
				RunalyzeGMap.resize();
				$("#map-fullscreen-link").text('Vollbild');
			},

			_parentContainer: function() {
				if ($("#ajax").is(':visible'))
					return '#ajax';

				return '#training-map';
			},

			setCopyright: function(mapType) {
				if (typeof mapType == "undefined")
					mapType = RunalyzeGMap.options.mapTypeId;

				var text;

				if (mapType == "OSM") {
					text = '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors&nbsp;';

					if ($("#map-copyright").length == 0) {
						$('<span id="map-copyright">'+text+'</span>').insertBefore(
							$(RunalyzeGMap.id+" :contains('Terms of Use'), "+RunalyzeGMap.id+" :contains('Nutzungsbedingungen')")
							.filter(function() { return $(this).children().length < 1 })
						);
					} else
						$("#map-copyright").html(text);
				} else
					$("#map-copyright").remove();
			}
	}

	function googleIsThere() {
		return (typeof google != "undefined");
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

	if (googleIsThere())
		RunalyzeGMap.options.mapTypeId = google.maps.MapTypeId.HYBRID;
})();