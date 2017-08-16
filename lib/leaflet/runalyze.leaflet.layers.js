/*
 * Additional features for Leaflet
 *
 * @see https://github.com/leaflet-extras/leaflet-providers/blob/master/leaflet-providers.js
 * @see http://leaflet-extras.github.io/leaflet-providers/preview/
 *
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
RunalyzeLeaflet.getNewLayers = function(){
	var layers = {
		'OpenStreetMap': L.tileLayer(
			'//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenStreetMap (de)': L.tileLayer(
			'//{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenStreetMap (hot)': L.tileLayer(
			'//{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
			{
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenMapSurfer': L.tileLayer(
			'//korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
				detectRetina:true,
				attribution: '&copy; <a href="http://giscience.uni-hd.de/">GIScience Research Group University of Heidelberg</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'HikeBikeMap': L.tileLayer(
			'http://{s}.tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'Hydda': L.tileLayer(
			'//{s}.tile.openstreetmap.se/hydda/full/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: '&copy; <a href="http://openstreetmap.se/">OpenStreetMap Sweden</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'Carto': L.tileLayer(
			'http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}' + (L.Browser.retina? '@2x': '') + '.png', {
				subdomains: 'abcd',
				minZoom: 0,
				maxZoom: 18,
				attribution: 'Map tiles by <a href="http://cartodb.com/attributions">CartoDB</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'HERE': L.tileLayer(
			'http://{s}.maptile.lbs.ovi.com/maptiler/v2/maptile/newest/hybrid.day/{z}/{x}/{y}/' + (L.Browser.retina? '512': '256') + '/png8?token={token}&app_id={appId}',
			{
				subdomains: '1234',
				appId: Runalyze.Options.nokiaAuth().app,
				token: Runalyze.Options.nokiaAuth().token,
				attribution: 'Map &copy; <a href="http://developer.here.com">Nokia</a>, Data &copy; NAVTEQ 2012'
			}
		),
		'GoogleMaps': L.tileLayer(
			'//mt0.google.com/vt/lyrs=m@142&x={x}&y={y}&z={z}', {
				detectRetina:true,
				attribution: '&copy; <a href="http://maps.google.com/" target="_blank">Google Maps</a>'
			}
		),
		'GoogleTerrain': L.tileLayer(
			'//mt0.google.com/vt/lyrs=t@126,r@142&x={x}&y={y}&z={z}', {
				detectRetina:true,
				attribution: '&copy; <a href="http://maps.google.com/" target="_blank">Google Terrain</a>'
			}
		),
		'SigmaCycle': L.tileLayer(
			'//tiles1.sigma-dc-control.com/layer8/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: '&copy; <a href="http://www.sigmasport.com/" target="_blank">SIGMA Sport &reg;</a>'
			}
		),
		'Esri': L.tileLayer(
			'//server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
				detectRetina:true,
				attribution: 'Tiles: &copy; <a href="http://www.esri.com/" target="_blank">Esri</a>'
			}
		),
		'EsriSatellite': L.tileLayer(
			'//server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
				detectRetina:true,
				attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
			}
		),
		'Stamen': L.tileLayer(
			'//stamen-tiles.a.ssl.fastly.net/toner/{z}/{x}/{y}' + (L.Browser.retina? '@2x': '') + '.png', {
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'StamenWatercolor': L.tileLayer(
			'//stamen-tiles.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'StamenTerrain': L.tileLayer(
			'//stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.png', {
				detectRetina:true,
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		)
	};

	if (Runalyze.Options.MapboxAuth() != "") {
		layers['MapboxOutdoor'] = L.tileLayer(
			'//api.mapbox.com/styles/v1/mapbox/outdoors-v10/tiles/256/{z}/{x}/{y}' + (L.Browser.retina ? '@2x' : '') + '?access_token={accesstoken}',
			{
				accesstoken: Runalyze.Options.MapboxAuth(),
				attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <a href="https://www.mapbox.com/map-feedback/" target="_blank"><strong>Improve this map</strong></a>'
			}
		);
	}

	if (Runalyze.Options.thunderforestAuth() != "") {
		layers['OpenCycleMap'] = L.tileLayer(
			'//{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey={apikey}', {
				apikey: Runalyze.Options.thunderforestAuth(),
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		);
        layers['Thunderforest'] = L.tileLayer(
			'//{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey={apikey}', {
				apikey: Runalyze.Options.thunderforestAuth(),
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
			}
		);
	}

	return layers;
};
