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
		'OpenStreetMap (de)': L.tileLayer(
			'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenStreetMap (hot)': L.tileLayer(
			'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'OpenMapSurfer': L.tileLayer(
			'http://openmapsurfer.uni-hd.de/tiles/roads/x={x}&y={y}&z={z}', {
				attribution: '&copy; <a href="http://giscience.uni-hd.de/">GIScience Research Group University of Heidelberg</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'HikeBikeMap': L.tileLayer(
			'http://{s}.tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'MapQuest': L.tileLayer(
			'http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
				subdomains: '1234',
				attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>'
			}
		),
		'Thunderforest': L.tileLayer(
			'http://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
			}
		),
		'Hydda': L.tileLayer(
			'http://{s}.tile.openstreetmap.se/hydda/full/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://openstreetmap.se/">OpenStreetMap Sweden</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'CartoDB': L.tileLayer(
			'http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
				subdomains: 'abcd',
				minZoom: 0,
				maxZoom: 18,
				attribution: 'Map tiles by <a href="http://cartodb.com/attributions">CartoDB</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'HERE': L.tileLayer(
			'http://{s}.maptile.lbs.ovi.com/maptiler/v2/maptile/newest/hybrid.day/{z}/{x}/{y}/256/png8?token={token}&app_id={appId}', {
				subdomains: '1234',
				appId: Runalyze.Options.nokiaAuth().app,
				token: Runalyze.Options.nokiaAuth().token,
				attribution: 'Map &copy; <a href="http://developer.here.com">Nokia</a>, Data &copy; NAVTEQ 2012'
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
		'OpenCycleMap': L.tileLayer(
			'http://{s}.tile.opencyclemap.org/cycle/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.opencyclemap.org">OpenCycleMap</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
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
		'Stamen': L.tileLayer(
			'http://{s}.tile.stamen.com/toner/{z}/{x}/{y}.png', {
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'Stamen (watercolor)': L.tileLayer(
			'http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.png', {
				attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, ' +
					'<a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>, ' +
					'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
			}
		),
		'Acetate': L.tileLayer(
			'http://a{s}.acetate.geoiq.com/tiles/acetate-hillshading/{z}/{x}/{y}.png', {
				subdomains: '0123',
				minZoom: 2,
				maxZoom: 18,
				attribution: '&copy;2012 Esri & Stamen, Data from OSM and Natural Earth'
			}
		)
	};
};