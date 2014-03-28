/*
 * Additional features for Leaflet
 * 
 * @see https://github.com/leaflet-extras/leaflet-providers/blob/master/leaflet-providers.js
 * @see http://leaflet-extras.github.io/leaflet-providers/preview/
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzeLeaflet.getNewLayers = function(){
	return {
		'OpenStreetMap': L.tileLayer(
			'http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenStreetMap DE': L.tileLayer(
			'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenStreetMap HOT': L.tileLayer(
			'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenCycleMap': L.tileLayer(
			'http://{s}.tile.opencyclemap.org/cycle/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'Nokia': L.tileLayer(
			'http://{s}.maptile.lbs.ovi.com/maptiler/v2/maptile/newest/hybrid.day/{z}/{x}/{y}/256/png8?token={token}&app_id={appId}', {
				subdomains: '1234',
				appId: 'tGaXC0G7JoT5tEvUJW8b',
				token: 'rXyIHiKvci-xdLuLEPJepQ',
				attribution: 'Map &copy; <a href="http://developer.here.com">Nokia</a>, Data &copy; NAVTEQ 2012'
			}
		),
		'MapQuest': L.tileLayer(
			'http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
				subdomains: '1234',
				attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>'
			}
		),
		'Esri': L.tileLayer(
			'http://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
				attribution: 'Tiles: &copy; <a href="http://www.esri.com/" target="_blank">Esri</a>'
			}
		),
		'EsriSatellite': L.tileLayer(
			'http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
				attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
			}
		),
		'Acetate': L.tileLayer(
			'http://a{s}.acetate.geoiq.com/tiles/acetate-hillshading/{z}/{x}/{y}.png', {
				subdomains: '0123',
				minZoom: 2,
				maxZoom: 18,
				attribution: '&copy;2012 Esri & Stamen, Data from OSM and Natural Earth'
			}
		),
		'GoogleMaps': L.tileLayer(
			'http://mt0.google.com/vt/lyrs=m@142&x={x}&y={y}&z={z}', {
				attribution: '&copy; <a href="http://maps.google.com/" target="_blank">Google Maps</a>'
			}
		),
		'GoogleTerrain': L.tileLayer(
			'http://mt0.google.com/vt/lyrs=t@126,r@142&x={x}&y={y}&z={z}', {
				attribution: '&copy; <a href="http://maps.google.com/" target="_blank">Google Terrain</a>'
			}
		),
		'SigmaCycle': L.tileLayer(
			'http://tiles1.sigma-dc-control.com/layer8/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.sigmasport.com/" target="_blank">SIGMA Sport &reg;</a>'
			}
		),
		'Thunderforest': L.tileLayer(
			'http://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
			}
		),
		'Stamen': L.tileLayer(
			'http://{s}.tile.stamen.com/toner/{z}/{x}/{y}.png', {
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		)
	};
};