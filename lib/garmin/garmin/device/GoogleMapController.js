if (Garmin == undefined) var Garmin = {};
/** Copyright © 2007 Garmin Ltd. or its subsidiaries.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License')
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @fileoverview Garmin.MapController Overlays tracks and waypoint data on Google maps.
 * 
 * @author Jason Holmes jason.holmes.at.garmin.com
 * @author Bobby Yang bobby.yang.at.garmin.com
 * @version 1.0
 */
/**
 * Accepts Garmin.Series objects and draws them on a Google Map.
 * 
 * @class Garmin.MapController
 * @constructor 
 * @param (String) mapString id of element to place map in
 */
Garmin.MapController = function(mapString){}; //just here for jsdoc
Garmin.MapController = Class.create();
Garmin.MapController.prototype = {

    initialize: function(mapString) {
        this.mapElement = $(mapString);
        this.usePositionMarker = true;
        
        this.polylines = new Array();
        this.markers = new Array();
        this.tracks = new Array();
        this.markerIndex = 0;

        this.timeToCheck = false;
        try {
	        this.map = new GMap2( $(mapString) );
	        this.map.addControl(new GSmallMapControl());
			this.map.addControl(new GMapTypeControl());
        	new GKeyboardHandler(this.map);
        } catch (e) {
        	alert("WARNING: application will not function properly with missing Google script element or invalid Google map key.  Error: "+e);
        }
        window.onUnload = "GUnload()";
    },

    /** Set the center point and zoom level of the map.
     * @param (Number) Latitude of the center point
     * @param (Number) Longitude of the center point
     * @param (Number) Zoom level
     */
    centerAndScale: function(lat, lon, scale) {
    	scale = (scale == null ? 13 : scale);
        this.map.setCenter(new GLatLng(lat, lon), scale);
    },
    
    /** Draw track on map.
     * @param (Garmin.Track) The track to draw
     * @param (String) Color in RGB Hex format, default: "#ff0000"
     */    
    drawTrack: function(series, color) {
        color = (color == null ? "#ff0000" : color);
        
        // create a smaller version of the whole track
        // create 300 points or so ...
	    // Problem is that Google Maps dies when you hit near 500 points, so we have to
	    // ensure that we create fewer than that for the track.
        var drawAt = Math.ceil(series.getSamplesLength()/300);
        var drawnPoints = new Array();

        try {
        	// create up to 300 points
			for(var h = 0; h < series.getSamplesLength(); h += drawAt) {
				drawnPoints.push(this.createNearestValidLocationPoint(series, h, -1));
		    }
		    // create the end point
           	drawnPoints.push(this.createNearestValidLocationPoint(series, series.getSamplesLength()-1, -1));
        } catch( e ) {
            alert("GoogleMapControl.drawTrack: " + e.message);
        }	    
        
        if (drawnPoints.length > 0) {
	        //draw the new smaller version
	        var polyline = new GPolyline(drawnPoints, color, 2, .8)
			try {
				this.centerAndScale(drawnPoints[0].lat(), drawnPoints[0].lng());			
	        	this.map.addOverlay( polyline );
		        this.addStartFinishMarkers(series);
		        this.bounds = this.findAZoomLevel(drawnPoints);
		        this.setOnBounds( this.bounds );
			} catch(e){ alert("GoogleMapControl.drawTrack, IE error on map.addOverlay("+polyline+") err: "+e); }
        }
    },

	/**Creates a GLatLng for the sample in the series closest to the index with a valid location (lat and lon).
	 * @param series - The series to search through.
	 * @param index - The index to start searching from.
	 * @param incDirection - The direction to travel for the search.
	 * @return A GLatLng of the nearest valid location sample found.
	 */
	createNearestValidLocationPoint: function(series, index, incDirection) {
    	var sample = series.findNearestValidLocationSample(index, -1);
    	if (sample != null) {
    		var sampleLat = sample.getMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude).getValue();
    		var sampleLon = sample.getMeasurement(Garmin.Sample.MEASUREMENT_KEYS.longitude).getValue();
    		return new GLatLng(sampleLat, sampleLon);    		
    	} else {
			throw new Error("No valid location point in series.");
    	}
	},

    /** Draw waypoint on map.
     * @param (Garmin.Series) series containing a waypoint to add to the map
     */
    drawWaypoint: function(series) {
    	var sample = series.getSample(0);
        this.centerAndScale(sample.getLatitude(), sample.getLongitude(), 15);    	
        this.addMarker(sample.getLatitude(), sample.getLongitude());
    },

    /** Calculates minimum bounding box for an set of points.
     * @param (Array) The array of points to find a zoom level for
     */
    findAZoomLevel: function(points) {
        var bounds = new GLatLngBounds(points[0], points[0]);
        
        for(var i=1; i<points.length-1; i+=3) {
            bounds.extend(points[i]);
        }
        
        return bounds;
    },
    
	/** Check the new dimensions of the map, and determine the bounds of the tracks
	 * Then set the map to zoom to that bound level
   	 * @private
	 */
    sizeAndSetOnBounds: function() {
        this.map.checkResize();
        this.setOnBounds( this.bounds );
    },

    /** Set the bounding box on the map.
     * @param (GLatLngBounds) bounding box for the
     */
    setOnBounds: function(bounds) {
        this.map.setCenter( this.bounds.getCenter(), this.map.getBoundsZoomLevel(this.bounds) );
    },
    
    /** Add an icon to a point.
     * @param {Number} latitude of marker
     * @param {Number} longitude of marker
     */
    addMarker: function(latitude, longitude) {
    	this.addMarkerWithIcon(latitude, longitude, Garmin.MapIcons.getRedIcon());
    },

    /** Adds a marker to the point with the icon specified
     * @param {Number} latitude of marker
     * @param {Number} longitude of marker
     * @param (GIcon) icon to add at the point
     */
    addMarkerWithIcon: function(latitude, longitude, icon) {
        var gMarker = new GMarker(new GLatLng(latitude, longitude), icon);
        this.markers.push(gMarker);
        this.map.addOverlay(gMarker);
    },

    /** Add start and finish markers to a track
     * @param (Garmin.Series) The series to add markers to
     */
    addStartFinishMarkers: function(series) {
    	var firstSample = series.getFirstValidLocationSample();
    	var lastSample = series.getLastValidLocationSample();
        this.addMarkerWithIcon(firstSample.getLatitude(), firstSample.getLongitude(), Garmin.MapIcons.getGreenIcon());
        this.addMarkerWithIcon(lastSample.getLatitude(), lastSample.getLongitude(), Garmin.MapIcons.getRedIcon());
    },

    /** String representation of map.
     * @return (String)
     */
    toString: function() {
        return "Google Based Map Controller, managing " + this.tracks.length + " track(s)";
    }
};

/** Icons used to mark waypoints and POIs on Google maps.  
 * 
 * @class Garmin.MapIcons
 * @constructor 
 */
Garmin.MapIcons = function(){}; //just here for jsdoc
Garmin.MapIcons = {
    getRedIcon: function() {
        var icon = new GIcon();
        icon.image = "http://trail.motionbased.com/trail/site/images/marker_red.png";
        return Garmin.MapIcons._applyShadowAndStuff(icon);
    },
    
    getGreenIcon: function() {
        var icon = new GIcon();
        icon.image = "http://trail.motionbased.com/trail/site/images/marker_green.png";
        return Garmin.MapIcons._applyShadowAndStuff(icon);
    },
    
    getBaseIcon: function() {
    	var baseIcon = new GIcon();
		baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
		baseIcon.iconSize = new GSize(20, 34);
		baseIcon.shadowSize = new GSize(37, 34);
		baseIcon.iconAnchor = new GPoint(9, 34);
		baseIcon.infoWindowAnchor = new GPoint(9, 2);
		baseIcon.infoShadowAnchor = new GPoint(18, 25);
        return baseIcon;
    },
    
    _applyShadowAndStuff: function(icon) {
        icon.iconSize = new GSize(12, 20);
        icon.shadow = "http://trail.motionbased.com/trail/site/images/marker_shadow.png";
        icon.shadowSize = new GSize(22, 20);
        icon.iconAnchor = new GPoint(6, 20);
        icon.infoWindowAnchor = new GPoint(5, 1);
        return icon;
    }
}