if (Garmin == undefined) var Garmin = {};
/** Copyright &copy; 2007-2010 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview Garmin.DeviceControl A mostly deprecated library of GPS track and waypoint data structures along with parsing tools.
 * @deprecated use Garmin.GpxActivityFactory instead
 * @version 1.9
 */

/** A waypoint represents a stored location.
 * Equivalent to a <wpt> in GPX format
 * Note: this class is only used by Garmin.Geocode but otherwise has been replaced by Garmin.Activity.
 * 
 * @class Garmin.WayPoint 
 * @constructor 
 * @param {Number} lat
 * @param {Number} lng
 * @param {Number} elev
 * @param {String} name
 */
Garmin.WayPoint = function(lat, lng, elev, name, addrdetails, desc, sym, type, cmt){};
Garmin.WayPoint = Class.create();
Garmin.WayPoint.prototype = {

	/** Prototype constructor
	 */
	initialize: function(lat, lng, elev, name, addrdetails, desc, sym, type, cmt) {
		this.lat = lat;
		this.lng = lng;
		this.name = name;
		this.addrdetails = addrdetails;
		
		// Get city, streetaddr, and zip data.
		if( this.addrdetails ) {
			this._initSubArea();
		}
		this.elev = elev;		
		this.desc = desc;
		this.sym = sym;
		this.type = type;
		this.cmt = cmt;
		this.date = null;
	},
	
	/** Initializes the subadministrative area, as designated by Google Maps API (see http://code.google.com/apis/maps/documentation/services.html#Geocoding_Structured).
	 *  Apparently some locations have Locality while others don't, so this function takes care of both.
	 */
	_initSubArea: function() {
		
		if( this.addrdetails.Country ) {
            this.country = this.addrdetails.Country.CountryNameCode;
            if (this.addrdetails.Country.AdministrativeArea) {
                this.state = this.addrdetails.Country.AdministrativeArea.AdministrativeAreaName;
				
				if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea ) {
	                if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality) {
	                    this.city = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName;
	                    if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.Thoroughfare ) {
	                        this.streetaddr = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.Thoroughfare.ThoroughfareName;
	                    }
	                    if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode ) {
	                        this.zip = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode.PostalCodeNumber;
	                    }
	                } else {
	                   this.city = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName; 
	
	                   if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Thoroughfare ) {
	                       this.streetaddr = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.Thoroughfare.ThoroughfareName;
	                   } 
	
	                   if( this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.PostalCode ) {
	                       this.zip = this.addrdetails.Country.AdministrativeArea.SubAdministrativeArea.PostalCode.PostalCodeNumber;
	                   } 
	                }
				} else {
					if( this.addrdetails.Country.AdministrativeArea.Locality) {
	                    this.city = this.addrdetails.Country.AdministrativeArea.Locality.LocalityName;
	                    if( this.addrdetails.Country.AdministrativeArea.Locality.Thoroughfare ) {
	                        this.streetaddr = this.addrdetails.Country.AdministrativeArea.Locality.Thoroughfare.ThoroughfareName;
	                    }
	                    if( this.addrdetails.Country.AdministrativeArea.Locality.PostalCode ) {
	                        this.zip = this.addrdetails.Country.AdministrativeArea.Locality.PostalCode.PostalCodeNumber;
	                    }
	                } else {
	                   this.city = this.addrdetails.Country.AdministrativeArea.SubAdministrativeAreaName; 
	
	                   if( this.addrdetails.Country.AdministrativeArea.Thoroughfare ) {
	                       this.streetaddr = this.addrdetails.Country.AdministrativeArea.Thoroughfare.ThoroughfareName;
	                   } 
	
	                   if( this.addrdetails.Country.AdministrativeArea.PostalCode ) {
	                       this.zip = this.addrdetails.Country.AdministrativeArea.PostalCode.PostalCodeNumber;
	                   } 
	                }
				}
            }
        }
	},
	
	/** Get waypoint symbol usually associated with a display icon.
	 * @type String 
	 * @return The symbol of this waypoint
	 */
	getSymbol: function() {
		return this.sym;
	},

	/** Get waypoint type.
	 * @type String 
	 * @return The type of this waypoint
	 */
	getType: function() {
		return this.type;
	},

	/** Get waypoint name.
	 * @type String 
	 * @return The name of this waypoint
	 */
	getName: function() {
		return this.name;
	},

	/** Get waypoint address XML string.  Uses the extension element to generate XML address format listed in the
	 * GPX Extensions v3 schema: http://www8.garmin.com/xmlschemas/GpxExtensions/v3/GpxExtensionsv3.xsd
	 *
	 * @type String 
	 * @return The address of this waypoint, as an XML string with the outermost element being gpxx::Address
	 */
	getAddress: function() {
		return (this.addrdetails != null) ? "<gpxx:Address><gpxx:StreetAddress>" 
				+ this.getStreetAddr() + "</gpxx:StreetAddress><gpxx:City>" 
				+ this.getCity() + "</gpxx:City><gpxx:State>" 
				+ this.getState() + "</gpxx:State><gpxx:PostalCode>"
				+ this.getZip() + "</gpxx:PostalCode></gpxx:Address>" : null;
	},
	
	/** Get country that the waypoint is located in, according to Google Maps API.
	 *  See http://www.google.com/apis/maps/ for details.
	 * @type String 
	 * @return The country the waypoint is located in.
	 */
	getCountry: function() {
		return this.country;
	},

	/** Get state that the waypoint is located in, according to Google Maps API.
	 * 	See http://www.google.com/apis/maps/ for details.
	 * @type String 
	 * @return The state the waypoint is located in.
	 */
	getState: function() {
		return this.state;
	},

	/** Get city that the waypoint is located in, according to Google Maps API.
	 * See http://www.google.com/apis/maps/ for details.
	 * @type String 
	 * @return The city the waypoint is located in.
	 */
	getCity: function() {
		return this.city;
	},
	
	/** Get street address that the waypoint is located in, according to Google Maps API.
	 * See http://www.google.com/apis/maps/ for details.
	 * @type String 
	 * @return The street address the waypoint is located in.
	 */
	getStreetAddr: function() {
		return this.streetaddr;
	},
	
	/** Get zip code that the waypoint is located in, according to Google Maps API.
	 * See http://www.google.com/apis/maps/ for details.
	 * @type String 
	 * @return The zip code the waypoint is located in.
	 */
	getZip: function() {
		return this.zip;
	},
	
	/** Get waypoint description.
	 * @type String 
	 * @return A description of this waypoint
	 */
	getDescription: function() {
		return this.desc;
	},

	/** Shortcut for directly getting the lat value
	 * 
	 * @type Number
	 * @return The value of the latitude for this point
	 */
	getLat: function() {
		return this.lat;
	},
	
	/** Shortcut for directly getting the longitude value
	 * 
	 * @type Number
	 * @return The value of the longitude for this point
	 */
	getLng: function() {
		return this.lng;
	},
	
	/** Shortcut for directly getting the elevation value
	 * 
	 * @type Number
	 * @return The value of the elevation for this point
	 */
	getElev: function() {
		return this.elev;
	},
	
	/** Get comment.
	 * 
	 * @type String
	 * @return The value of the comment for this point
	 */
	getComment: function() {
		return this.cmt;
	},
	
	
	/** Shortcut for directly getting the date/time
	 * 
	 * @type Garmin.DateTimeFormat
	 * @return The DateTimeFormat object for this point
	 */
	getDate: function() {
		return this.date;
	},
	
	toString: function() {
		return "Waypoint: (" + this.getLat() + ", " +  this.getLng() + ")";
	}
};


/** TrackPoint class reprsents a point from a track.<br>
 * A TrackPoint contains an associative array of measurements, which can be retrieved with a 
 * #getMeasurement call passing in a string index.<br>
 * Equivalent to a <trkpt> in GPX format  
 * @deprecated use Garmin.Activity instead
 * @class Garmin.TrackPoint
 * @constructor 
 */
Garmin.TrackPoint = function(){};
Garmin.TrackPoint = Class.create();
Garmin.TrackPoint.prototype = {
    /** prototype constructor
     */
	initialize: function() {
		this.measurements = null;
		this.date = null;
	},

	/** Get a Measurement from this TrackPoint
	 * If the measurement does not exist - return null
	 * 
	 * @param {String} context of the measurement we would like to get
	 * @type Object
	 * @return A measurement object (important to remember it's value is in measurementObject.value!)
	 * 	or null if the measurement doesn't exist
	 */
	getMeasurement: function(context) {
		var meas = this.measurements[context];
		if(meas == undefined) {
		  meas = null;
		}
		return meas;
	},

	/** Determines if this TrackPoint point is valid for determing location
	 * @type Boolean
	 * @return True if lat/lon exist, false otherwise
	 */
    isValidLocation: function() {
        return ( (this.getLat() != "null") && (this.getLat() != null) && (this.getLng() != "null") && (this.getLng() != null));
    },

	/** Shortcut for directly getting the lat value
	 * 
	 * @type Number
	 * @return The value of the latitude for this point
	 */
	getLat: function() {
	    var meas = this.getMeasurement( "latitude" );
	    if(meas == null) {
	    	return null;
	    } else {
	    	return meas.value;
	    }
	},
	
	/** Shortcut for directly getting the longitude value
	 * 
	 * @type Number
	 * @return The value of the longitude for this point
	 */
	getLng: function() {
		var meas = this.getMeasurement( "longitude" );
	    if(meas == null) {
	    	return null;
	    } else {
	    	return meas.value;
	    }	
	},
	
	/** Shortcut for directly getting the elevation value
	 * 
	 * @type Number
	 * @return The value of the elevation for this point
	 */
	getElev: function() {
		var meas = this.getMeasurement( "elevation" );
	    if(meas == null) {
	    	return null;
	    } else {
	    	return meas.value;
	    }	
	},
	
	/** Shortcut for directly getting the date/time
	 * 
	 * @type  Garmin.DateTimeFormat
	 * @return The time for this point
	 */
	getDate: function() {
		return this.date;
	},
	
	toString: function() {
		return "TrackPoint Point: (" + this.getLat() + ", " +  this.getLng() + ")";
	}
};



/** Equivalent to a <trkseg> in GPX format
 * 
 * @deprecated use Garmin.Activity instead
 * @class Garmin.TrackSegment
 * @constructor
 */
Garmin.TrackSegment = function(){};
Garmin.TrackSegment = Class.create();
Garmin.TrackSegment.prototype = {

    initialize: function() {
        this.points = new Array();
    },
    
    addTrackPoint: function(trackPointObject) {
    	this.points.push(trackPointObject);
    },
    
    /** Find the nearest valid point to the index given
     * 
     * @param index is the index
     * @param incDirection is an int in the direction we'd like to look positive 
     * 	nums are forward, negative nums are backwards
     * 
     * @type Garmin.TrackPoint 
     * @return The nearest point (possibly the index) that has a validLocation
     */ 
    findNearestValidLocationPoint: function(index, incDirection) {
        if( this.getPoint( index ).isValidLocation() ) {
            return this.getPoint( index );
        } else if( index >= this.getLength() ) {
        	return this.findNearestValidLocationPoint(this.getLength()-1, -1);
        } else {
            return this.findNearestValidLocationPoint(index+incDirection, incDirection);
        }
    },

	/** Get the point specified on the track
	 * If the number is negative, get's the first
	 * If it's larger than possible, get's the last
	 * Otherwise it gets the number requested
	 *
	 * @param {Number} index is the point we want
     * @type Garmin.TrackPoint 
	 * @return A TrackPoint that fits the pattern described above 
	 */
    getPoint: function(index) {
        index = Math.floor(index);
    
        if(index >= this.getLength()) {
            return this.getEnd();
        }
        if(index <= 0) {
            return this.getStart();
        }
            
        return this.points[index];
    },

    /** Quick method to get the first point
     * @type Garmin.TrackPoint 
     * @return The first point of this track
     */
    getStart: function() {
        return this.points[0];
    },

    /** Quick method to get the last point
     * @type Garmin.TrackPoint 
     * @return The last point of this track
     */
    getEnd: function() {
        return this.points[this.getLength()-1];
    },

    /** Get the latitude for the start point of this segment
     * @type Number
	 * @return Latitude of the first trackpoint
     */
    getStartLat: function() {
    	return this.getStart().getLat();
    },

    /** Get the longitude for the start point of this segment
     * @type Number
	 * @return Longitude of the first trackpoint
     */
    getStartLng: function() {
    	return this.getStart().getLng();
    },

    /** Get the data/time for the start point of this segment
     * @return date/time of the first trackpoint
     * @type Garmin.DateTimeFormat
     */
    getStartDate: function() {
    	return this.getStart().getDate();
    },

    /** Get the data/time for the end point of this segment
     * @type Garmin.DateTimeFormat
     * @return Date/time of the last trackpoint
     */
    getEndDate: function() {
    	return this.getEnd().getDate();
    },
    
    /** Get the total duration for this track segment
	 * @type String
	 * @return String of Duration (hh:mm:ss)
     */
    getDuration: function() {
    	return this.getStartDate().getDurationTo(this.getEndDate());
    },

    /** Get the total number of trackpoints in this segment
	 * @type Number
	 * @return Total number of trackpoints in this segment
     */
    getLength: function() {
        return this.points.length;
    },

    /** String representation
	 * @type String
     */
    toString: function() {
        return "Track Segment w/ " + this.getLength() + " points.";
    }
};


/** A track is an ordered list of track segments.<br>
 * Equivalent to a <trk> in GPX format.
 * 
 * @deprecated use Garmin.Activity instead
 * @class Garmin.Track
 * @constructor
 */
Garmin.Track = function(){}; 
Garmin.Track = Class.create();
Garmin.Track.prototype = {
    initialize: function() {
        this.segments = new Array();
    },
    
    /** Add a segment to this track
	 * @type Garmin.TrackPoint
     */
    addSegment: function(trackSegment) {
    	this.segments.push(trackSegment);
    },

	/** Get the segment specified on the track
	 * If the number is negative, get's the first
	 * If it's larger than possible, get's the last
	 * Otherwise it gets the number requested
	 *
	 * @param {Number} index is the segment we want
	 * @type Garmin.TrackSegment
	 * @return A segment that fits the pattern described above 
	 */
    getSegment: function(index) {
        index = Math.floor(index);
    
        if(index >= this.getLastSegment()) {
            return this.getEnd();
        }
        if(index <= 0) {
            return this.getFirstSegment();
        }
            
        return this.segments[index];
    },

    /** Get the first segment
	 * @type Garmin.TrackSegment
     * @return The first segment of this track
     */
    getFirstSegment: function() {
        return this.segments[0];
    },

    /** Get the last segment
	 * @type Garmin.TrackSegment
     * @return The last segment of this track
     */
    getLastSegment: function() {
        return this.segments[this.getNumSegments()-1];
    },

    /** Get the total length of the track
	 * @type Number
     */
    getNumSegments: function() {
        return this.segments.length;
    },

    /** Get the start point for the track
	 * @type Garmin.TrackPoint
     */
    getStart: function() {
    	return this.getFirstSegment().getStart();
    },

    /** Get the latitude of the start point for the track
	 * @type Number
     */
    getStartLat: function() {
    	return this.getFirstSegment().getStartLat();
    },

    /** Get the lpngitude of the start point for the track
	 * @type Number
     */
    getStartLng: function() {
    	return this.getFirstSegment().getStartLng();
    },
    
    /** Get the DateTimeFormat object for the start of this track
	 * @type Garmin.DateTimeFormat
     */
    getStartDate: function() {
    	return this.getFirstSegment().getStartDate();
    },

    /** Get the end point for the track
	 * @type Garmin.TrackPoint
     */
    getEnd: function() {
    	return this.getLastSegment().getEnd();
    },

    /** Get the DateTimeFormat object for the end of this track
	 * @type Garmin.DateTimeFormat
     */
    getEndDate: function() {
    	return this.getLastSegment().getEndDate();
    },
    
    /** Get the total duration for this track
	 * @type String
     */
    getDuration: function() {
    	return this.getStartDate().getDurationTo(this.getEndDate());
    },

    /** Get the total number of trackpoints in this track
	 * @type Number
     */
    getLength: function() {
		var length = 0;
		for( var i=0; i < this.segments.length; i++ ) {
			length += this.segments[i].getLength();
		}
        return length;
    },

    /** Checks to see if the startDate is defined.  If not then this is
     * technically a route and presents issues for some
     * track log databases where timestamps are used to merge old
     * and new entries.
	 * @type Boolean
	 * @return True if this track has a timestamp
     */
    isDrawable: function() {
    	return (this.getStartDate() != null);
    },

    toString: function() {
        return "Track w/ " + this.getNumSegments() + " segments.";
    }
};

/* THIS CLASS NOT NEEDED YET
 * @class Garmin.Address
 * Address class reprsents a US postal address.<br>
 * @constructor 

Garmin.Address = function(){};
Garmin.Address = Class.create();
Garmin.Address.prototype = {
	initialize: function() {
		this.streetAddress = null;
		this.streetAddress2 = null;
		this.city = null;
		this.state = null;
		this.postalCode = null;
	},

	toString: function() {
		return "Address: (" + this.streetAddress + ", " + 
			  (this.streetAddress2 ? this.streetAddress2+", " : "") + 
			  (this.city ? this.city+", " : "") + 
			  (this.state ? this.state+" " : "") + 
			  (this.postalCode ? this.postalCode : "") + 
			  ")";
	}
};
 */


/** Used to parse track and/or waypoint data from a number of Xml formats.<br>
 * Currently only supports tracks from a GPX file.
 * 
 * @deprecated use Garmin.GpxActivityFactory instead
 * @class Garmin.GpsDataFactory
 * @constructor
 */
Garmin.GpsDataFactory = function(){}; 
Garmin.GpsDataFactory = Class.create();
Garmin.GpsDataFactory.prototype = {

    initialize: function() {
    	this.tracks = new Array();
    	this.waypoints = new Array();
    },

    /** Get the tracks parsed by this factory
	 * @type Array<Garmin.Tracks>
     */
	getTracks: function() {
		return this.tracks;
	},

    /** Get the waypoints parsed by this factory
	 * @type Array<Garmin.WayPoint>
     */
	getWaypoints: function() {
		return this.waypoints;
	},

    /** Parse a gpx string and save the tracks found as objects
	 * @param {String} xml string in GPX format
     */
    parseGpxString: function(gpxString) {
		var gpxDocument = Garmin.XmlConverter.toDocument(gpxString);
		
		this.parseGpxDocument(gpxDocument);
	},

    /** Parse a gpx document and save the tracks and waypoints found
	 * @param {Document} xml document in GPX format
     */
    parseGpxDocument: function(gpxDocument) {
		this.parseGpxTracks(gpxDocument);
		this.parseGpxWaypoints(gpxDocument);
    },

    /** Parse a GPX xml document for tracks
	 * @param {Document} xml document in GPX format
     * @type Array<Garmin.Track>
     */
	parseGpxTracks: function(gpxDocument) {
		var tracks = new Array();

    	var trackNodes = gpxDocument.getElementsByTagName("trk");

		// triple for-loop fun
		for( var i=0; i < trackNodes.length; i++ ) {
			var trk = new Garmin.Track();

			var trackSegments = trackNodes[i].getElementsByTagName("trkseg");
	
			for( var j=0; j < trackSegments.length; j++ ) {
				var trkseg = new Garmin.TrackSegment();
				
				var trackPoints = trackSegments[j].getElementsByTagName("trkpt");
		
				for( var k=0; k < trackPoints.length; k++ ) {
					var trkpt = new Garmin.TrackPoint();

					var lat = trackPoints[k].getAttribute("lat");
					var lng = trackPoints[k].getAttribute("lon");
					var eleElement = trackPoints[k].getElementsByTagName("ele");
					var ele = (eleElement.length > 0) ? eleElement[0].childNodes[0].nodeValue : null;
					
					var timeNodes = trackPoints[k].getElementsByTagName("time");
					if(timeNodes.length > 0) {
						var time = timeNodes[0].childNodes[0].nodeValue;
						trkpt.date = (new Garmin.DateTimeFormat()).parseXsdDateTime(time);
					}
					
					trkpt.measurements = {
						latitude: {
							value: lat,
							context: "latitude"
						},
						longitude: {
							value: lng,
							context: "longitude"
						},
						elevation: {
							value: ele,
							context: "feet"
						}
					};
					
					trkseg.addTrackPoint(trkpt);
				}
				
				trk.addSegment(trkseg);
			}

			tracks.push(trk);
		}

    	this.tracks = tracks;
    	return tracks;
	},

    /** Parse a GPX xml waypoint into a Waypoint object.
	 * @param {Element} xml waypoint in GPX format
     * @type Garmin.WayPoint
     */
	parseGpxWaypoint: function(waypointNode) {
		var lat  = waypointNode.getAttribute("lat");
		var lng  = waypointNode.getAttribute("lon");
		var name = this._tagValue(waypointNode,"name");
		var desc = this._tagValue(waypointNode,"desc");
		var ele  = this._tagValue(waypointNode,"ele");
		var sym  = this._tagValue(waypointNode,"sym");
		var type  = this._tagValue(waypointNode,"type");
		var cmt  = this._tagValue(waypointNode,"cmt");
		
		var wpt  = new Garmin.WayPoint(lat, lng, ele, name, null, desc, sym, type, cmt);		
   		return wpt;
	},

    /** Parse a GPX xml document for waypoints
	 * @param {Document} xml document in GPX format
     * @type Array<Garmin.WayPoint>
     */
	parseGpxWaypoints: function(gpxDocument) {
		var waypoints = new Array();
    	var waypointNodes = gpxDocument.getElementsByTagName("wpt");
		for( var i=0; i < waypointNodes.length; i++ ) {
			var waypointNode = waypointNodes[i];
			var wpt = this.parseGpxWaypoint(waypointNode);
			waypoints.push(wpt);
		}
    	this.waypoints = waypoints;
    	return waypoints;
	},

    /** Take a list of tracks and waypoints and generate a GPX xml string
	 * @param {Array<Garmin.Track>} Tracks
	 * @param {Array<Garmin.WayPoint>} Waypoints
     * @type String
     */
	produceGpxString: function(tracks, waypoints) {
		gpxString = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\" ?>";
	
		gpxString += "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" xmlns:gpxx=\"http://www.garmin.com/xmlschemas/GpxExtensions/v3\" creator=\"Garmin Communicator Plug-In API\" version=\"1.1\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensions/v3/GpxExtensionsv3.xsd\">";
		
		if(tracks != null) {
			for( var i=0; i < tracks.length; i++ ) {
				gpxString += this.produceTrackGpxString(tracks[i]);
			}
		}

		if(waypoints != null) {		
			for( var i=0; i < waypoints.length; i++ ) {
				gpxString += this.produceWaypointGpxString(waypoints[i]);
			}
		}
		gpxString += "</gpx>";
	
		return gpxString;
	},

    /** Take a track object and generate a GPX xml string (without gpx or xml headers)
	 * @param {Garmin.Track} Track
     * @type String
     */
	produceTrackGpxString: function(track) {
		gpxString = "<trk>";

		for( var i=0; i < track.getNumSegments(); i++ ) {
			var segment = track.getSegment(i);
			
			gpxString += "<trkseg>";
			for(var j=0; j < segment.getLength(); j++) {
				var point = segment.getPoint(j);
				
				gpxString += "<trkpt lat=\"" + point.getLat() + "\" lon=\"" + point.getLng() + "\">";
				if(point.getElev()) {
					gpxString += "<ele>" + point.getElev() + "</ele>";
				}
				if(point.getDate()) {
					gpxString += "<time>" + point.getDate().getXsdString() + "</time>";
				}
				gpxString += "</trkpt>";
			}
			gpxString += "</trkseg>";

		}
		
		gpxString += "</trk>";
	
		return gpxString;
	},

    /** Take a waypoint object and generate a GPX xml string (without gpx or xml headers)
	 * @param {Garmin.WayPoint} WayPoint
     * @type String
     */
	produceWaypointGpxString: function(waypoint) {
		gpxString = "<wpt lat=\"" + waypoint.getLat() + "\" lon=\"" + waypoint.getLng() + "\">";
	
		if(waypoint.getElev()) {
			gpxString += "<ele>" + waypoint.getElev() + "</ele>";
		}
		if(waypoint.getName()) {
			gpxString += "<name>" + waypoint.getName() + "</name>";
		}
		if(waypoint.getComment()) {
			gpxString += "<cmt>" + waypoint.getComment() + "</cmt>";
		}
		if(waypoint.getDescription()) {
			gpxString += "<desc>" + waypoint.getDescription() + "</desc>";
		}
		if(waypoint.getSymbol()) {
			gpxString += "<sym>" + waypoint.getSymbol() + "</sym>";
		}
		if(waypoint.getAddress()) {
			gpxString += "<extensions><gpxx:WaypointExtension>" + waypoint.getAddress() + "</gpxx:WaypointExtension></extensions>";
		}
		if(waypoint.getType()) {
			gpxString += "<type>" + waypoint.getType() + "</type>";
		}
		gpxString += "</wpt>";
	
		return gpxString;
	},

    /** Utility method to get the value of a child element.
     * @param {Node} parent DOM node
     * @param {String} name of child element
     * @type String value of child element
     */
	_tagValue: function(parentNode, tagName) {
		var subNode = parentNode.getElementsByTagName(tagName);
		return subNode.length > 0 ? subNode[0].childNodes[0].nodeValue : null;
	},
	
    /** String representation of instance.
     * @type String
     */
    toString: function() {
        return "GpsDataFactory.";
    }
};
