if (Garmin == undefined) var Garmin = {};
/**
 * Copyright � 2007 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview Garmin.GpxActivityFactory - A factory for producing gpx activity and data.
 * 
 * @author Bobby Yang bobby.yang.at.garmin.com
 * @version 1.0
 */
/**A factory that can produce an array activity given gpx xml and produce gps xml given an
 * array of activity.
 * many other types of data.
 * @class Garmin.GpxActivityFactory
 * @constructor 
 */
Garmin.GpxActivityFactory = function(){};
Garmin.GpxActivityFactory = {
	
	parseString: function(gpxString) {
		var gpxDocument = Garmin.XmlConverter.toDocument(gpxString);		
		return Garmin.GpxActivityFactory.parseDocument(gpxDocument);	
	},
	
	parseDocument: function(gpxDocument) {
        return this.parseDocumentByType(gpxDocument, Garmin.GpxActivityFactory.GPX_TYPE.all);
	},
	
	parseDocumentByType: function(gpxDocument, type) {
		var activities = new Array();
        var routes = new Array();
        var tracks = new Array();
        var waypoints = new Array();        
        
        switch(type) {
          case Garmin.GpxActivityFactory.GPX_TYPE.routes:
              activities = Garmin.GpxActivityFactory._parseGpxRoutes(gpxDocument);
              break;
          case Garmin.GpxActivityFactory.GPX_TYPE.waypoints:
              activities = Garmin.GpxActivityFactory._parseGpxWaypoints(gpxDocument);
              break;
          case Garmin.GpxActivityFactory.GPX_TYPE.tracks:
              activities = Garmin.GpxActivityFactory._parseGpxTracks(gpxDocument);
              break;
          case Garmin.GpxActivityFactory.GPX_TYPE.all:
              routes = Garmin.GpxActivityFactory._parseGpxRoutes(gpxDocument);
              tracks = Garmin.GpxActivityFactory._parseGpxTracks(gpxDocument);
              waypoints = Garmin.GpxActivityFactory._parseGpxWaypoints(gpxDocument);
              activities = waypoints.concat(routes).concat(tracks);
              break;    
        }
         
        return activities;
	},
	
	produceString: function(activities) {
		var gpxString = "";
		
		// default creator information incase we can't find the creator info in the dom
		var creator = Garmin.GpxActivityFactory.DETAIL.creator;
		
		// default metadata information incase we can't find the metadata node in the dom
		var metadata = "\n  <metadata>";
		metadata += "\n    <link href=\"http://www.garmin.com\">";
		metadata += "\n      <text>Garmin International</text>";
		metadata += "\n    </link>";						
		metadata += "\n  </metadata>";
		
		// try to find creator and metadata info in the dom
		if (activities != null && activities.length > 0) {
			var activityDom = activities[0].getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom);
			var gpxNode = activityDom.ownerDocument.getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.gpx);
			if (gpxNode.length > 0) {
				// grab creator information from the dom if possible
				var creatorStr = gpxNode[0].getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.creator);
				if (creatorStr != null && creatorStr != "") {
					creator = creatorStr;
				}
				// grab metadata info
				var metadataNode = gpxNode[0].getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.metadata);
				if (metadataNode.length > 0) {
					metadata = Garmin.XmlConverter.toString(metadataNode[0]);
				}
			}
		}

		// header tags
		gpxString += "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\" ?>";	
		gpxString += "\n<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" creator=\"" + creator + "\" version=\"1.1\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensions/v3/GpxExtensionsv3.xsd\">";
	
		//metadata tag
		gpxString += "\n  " + metadata;
		
		if (activities != null) {
			// waypoint and track tags
			for(var i = 0; i < activities.length; i++) {		
				gpxString += "\n  " + Garmin.GpxActivityFactory._produceActivityString(activities[i]);
			}
		}
		
		// footer tags
		gpxString += "\n</gpx>";
		
		return gpxString;
	},
	
	/** Fully load the sample, assume sample was previously lazy-loaded
	 */	
	finishLoadingSample: function(domNode, sample) {
		if (domNode.nodeName == Garmin.GpxActivityFactory.SCHEMA_TAGS.routePoint) {
			Garmin.GpxActivityFactory._parseGpxRoutePoint(domNode, sample);
			sample.isLazyLoaded = false;
		} else if (domNode.nodeName == Garmin.GpxActivityFactory.SCHEMA_TAGS.trackPoint) {
			Garmin.GpxActivityFactory._parseGpxTrackPoint(domNode, sample);
			sample.isLazyLoaded = false;
		}
	},		
	
	_produceActivityString: function(activity) {
		var activityString = "";
		if (activity != null) {
			var series = activity.getSeries();
			for (var i = 0; i < series.length; i++) {
				var currentSeries = series[i];
				if (currentSeries.getSeriesType() == Garmin.Series.TYPES.history) {
					// converting the dom back into string
					// this is the lazy way, this will not work if 
					// converting between file types or activity data
					// has been modified.
					var activityDom = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom);			
					if (activityDom != null) {
						activityString = Garmin.XmlConverter.toString(activityDom);
					}
				} else if (currentSeries.getSeriesType() == Garmin.Series.TYPES.waypoint) {
					// converting the dom back into string
					// this is the lazy way, this will not work if 
					// converting between file types or activity data
					// has been modified.
					var activityDom = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom);			
					if (activityDom != null) {
						activityString = Garmin.XmlConverter.toString(activityDom);
					}						
				}
			}
		}		
		return activityString;
	},
	
	_parseGpxRoutes: function(gpxDocument) {
		var routes = new Array();
    	var routeNodes = gpxDocument.getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.route);

		for( var i=0; i < routeNodes.length; i++ ) {
			var route = new Garmin.Activity();
			
			var routeName = Garmin.GpxActivityFactory._tagValue(routeNodes[i], Garmin.GpxActivityFactory.SCHEMA_TAGS.routeName);
			if (routeName == null) {
				routeName = "";
			}
			
			route.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom, routeNodes[i]);
			route.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName, routeName);

			var series = new Garmin.Series(Garmin.Series.TYPES.route);
			route.addSeries(series);

			var routePoints = routeNodes[i].getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.routePoint);					
			if (routePoints.length > 0) {					
				for( var j=0; j < routePoints.length; j++ ) {
					var routePoint = new Garmin.Sample();
					routePoint.setLazyLoading(true, Garmin.GpxActivityFactory, routePoints[j]);
					series.addSample(routePoint);
				}
			}
			
			if (series.getSamplesLength() > 0) {
				routes.push(route);
			}
		}
		
    	return routes;			
	},
	
	_parseGpxRoutePoint: function(routePointNode, routePointSample) {
		if (routePointSample == null) {
			routePointSample = new Garmin.Sample();
		}

		routePointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude, routePointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLatitude));
		routePointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.longitude, routePointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLongitude));
		
		var elevation =  Garmin.GpxActivityFactory._tagValue(routePointNode,Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointElevation);
		if (elevation != null) {
			routePointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.elevation, elevation);
		}		
		
		return routePointSample;		
	},
	
	_parseGpxTracks: function(gpxDocument) {
		var tracks = new Array();
		
    	var trackNodes = gpxDocument.getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.track);
		for( var i=0; i < trackNodes.length; i++ ) {
			var track = new Garmin.Activity();
			
			var trackName = Garmin.GpxActivityFactory._tagValue(trackNodes[i], Garmin.GpxActivityFactory.SCHEMA_TAGS.trackName);
			if (trackName == null) {
				trackName = "";
			}
			
			track.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom, trackNodes[i]);
			track.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName, trackName);

			var series = new Garmin.Series(Garmin.Series.TYPES.history);
			track.addSeries(series);

			var trackSegments = trackNodes[i].getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.trackSegment);	
			for( var j=0; j < trackSegments.length; j++ ) {
				
				// grab all the trackpoints
				var trackPoints = trackSegments[j].getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.trackPoint);											
				if (trackPoints.length > 0) {
					
					// set the start and end time summary values		
					var startTime = Garmin.GpxActivityFactory._tagValue(trackPoints[0], Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointTime);
					var endTime = Garmin.GpxActivityFactory._tagValue(trackPoints[trackPoints.length - 1], Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointTime);					
					if (startTime != null && endTime != null) {
						track.setSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime, (new Garmin.DateTimeFormat()).parseXsdDateTime(startTime));
						track.setSummaryValue(Garmin.Activity.SUMMARY_KEYS.endTime, (new Garmin.DateTimeFormat()).parseXsdDateTime(endTime));
					} else {
						// can't find timestamps, must be a route reported as a track (GPSMap does this)
						series.setSeriesType(Garmin.Series.TYPES.route);
					}
				
					// loop through all the trackpoints in this segment				
					for( var k=0; k < trackPoints.length; k++ ) {
						var trackPoint = new Garmin.Sample();
						trackPoint.setLazyLoading(true, Garmin.GpxActivityFactory, trackPoints[k]);
						series.addSample(trackPoint);						
					}
					
					// add the track to the list of tracks
					tracks.push(track);
				}
			}
		}

    	return tracks;	
	},
	
	_parseGpxTrackPoint: function(trackPointNode, trackPointSample) {
		if (trackPointSample == null) {
			trackPointSample = new Garmin.Sample();	
		}
		
		trackPointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude, trackPointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLatitude));
		trackPointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.longitude, trackPointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLongitude));
		
		var elevation =  Garmin.GpxActivityFactory._tagValue(trackPointNode,Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointElevation);
		if (elevation != null) {
			trackPointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.elevation, elevation);
		}

		var time = Garmin.GpxActivityFactory._tagValue(trackPointNode, Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointTime);
		if (time != null) {
			trackPointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.time, (new Garmin.DateTimeFormat()).parseXsdDateTime(time));
		}			
		
		return trackPointSample;
	},
	
	_parseGpxWaypoints: function(gpxDocument) {
		var waypoints = new Array();
    	var waypointNodes = gpxDocument.getElementsByTagName(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypoint);
    	
		for( var i=0; i < waypointNodes.length; i++ ) {
			waypoints.push(Garmin.GpxActivityFactory._parseGpxWaypoint(waypointNodes[i]));
		}
    	
    	return waypoints;
	},
	
	_parseGpxWaypoint: function(waypointNode) {
		var waypoint = new Garmin.Activity();
		var waypointSeries = new Garmin.Series(Garmin.Series.TYPES.waypoint);
		var waypointSample = new Garmin.Sample();
		
		waypoint.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.dom, waypointNode);			

		waypointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude, waypointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLatitude)); 
		waypointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.longitude, waypointNode.getAttribute(Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointLongitude));
				
		var elevation =  Garmin.GpxActivityFactory._tagValue(waypointNode,Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointElevation);
		if (elevation != null) {
			waypointSample.setMeasurement(Garmin.Sample.MEASUREMENT_KEYS.elevation, elevation);
		}
		
		var wptName =  Garmin.GpxActivityFactory._tagValue(waypointNode,Garmin.GpxActivityFactory.SCHEMA_TAGS.waypointName);
		if (wptName != null) {
			waypoint.setAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName, wptName);
		}
		
		waypointSeries.addSample(waypointSample);
		waypoint.addSeries(waypointSeries);		
   		return waypoint;
	},
	
	_tagValue: function(parentNode, tagName) {
		var subNode = parentNode.getElementsByTagName(tagName);
		return subNode.length > 0 ? subNode[0].childNodes[0].nodeValue : null;
	},	
	
    toString: function() {
        return "[GpxActivityFactory]";
    }	
};

/** Constants defining GPX type
 */
Garmin.GpxActivityFactory.GPX_TYPE = {
	routes:    "routes",
	waypoints: "waypoints",
	tracks:    "tracks",
	all:       "all"
}

/** Constants defining details about the factory
 */
Garmin.GpxActivityFactory.DETAIL = {
	creator:			"Garmin Communicator Plug-In API"
};

/** Constants defining tags used by the gpx schema. This is used
 *  by the factory when converting between the xml and datastructure.
 */
Garmin.GpxActivityFactory.SCHEMA_TAGS = {
	creator:					"creator",
	gpx:						"gpx",
	metadata:					"metadata",
	route:						"rte",
	routeName:					"name",
	routePoint:					"rtept",
	track:						"trk",
	trackName:					"name",
	trackPoint:					"trkpt",
	trackSegment:				"trkseg",
	waypoint:					"wpt",
	waypointComment:			"cmt",
	waypointDGPSAge:			"ageofdgpsdata",
	waypointDGPSID:				"dgpsid",
	waypointDescription:		"desc",
	waypointGeoIdHeight:		"geoidheight",
	waypointHDOP:				"hdop",
	waypointMagVar:				"magvar",
	waypointName:				"name",
	waypointLatitude:			"lat",
	waypointLink:				"link",
	waypointLongitude:			"lon",
	waypointElevation:			"ele",
	waypointPDOP:				"pdop",
	waypointSatellites:			"sat",
	waypointSource:				"src",
	waypointSymbol:				"sym",
	waypointTime:				"time",
	waypointType:				"type",
	waypointVDOP:				"vdop"
};
/*
// Dynamic include of required libraries and check for Prototype
// Code taken from scriptaculous
// TODO: put this code in a library and reuse is instead of copying it to new files
var GpxActivityFactory = {
	require: function(libraryName) {
		// inserting via DOM fails in Safari 2.0, so brute force approach
		document.write('<script type="text/javascript" src="'+libraryName+'"></script>');
	},

	load: function() {
		if((typeof Prototype=='undefined') || 
			(typeof Element == 'undefined') || 
			(typeof Element.Methods=='undefined') ||
			parseFloat(Prototype.Version.split(".")[0] + "." +
			Prototype.Version.split(".")[1]) < 1.5) {
			throw("GpxActivityFactory requires the Prototype JavaScript framework >= 1.5.0");
		}

		$A(document.getElementsByTagName("script"))
		.findAll(
			function(s) {
				return (s.src && s.src.match(/GpxActivityFactory\.js(\?.*)?$/))
			}
		)
		.each(
			function(s) {
				var path = s.src.replace(/GpxActivityFactory\.js(\?.*)?$/,'../../');
				var includes = s.src.match(/\?.*load=([a-z,]*)/);
				var dependencies = 'garmin/util/Util-XmlConverter' +
									',garmin/util/Util-DateTimeFormat' +
									',garmin/activity/GarminMeasurement' +
									',garmin/activity/GarminSample' +
									',garmin/activity/GarminSeries' +
									',garmin/activity/GarminActivity';
			    (includes ? includes[1] : dependencies).split(',').each(
					function(include) {
						GpxActivityFactory.require(path+include+'.js') 
					}
				);
			}
		);
	}	
}

GpxActivityFactory.load();*/
