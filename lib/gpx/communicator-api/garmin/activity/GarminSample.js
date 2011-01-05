if (Garmin == undefined) var Garmin = {};
/**
 * Copyright © 2007 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview Garmin.Sample - A datastructure designed to contain a number of measurements
 * 									recorded at a single point.
 * 
 * @author Bobby Yang bobby.yang.at.garmin.com
 * @version 1.0
 */
/**A collection of measurements recorded at a single point.
 * @class Garmin.Sample
 * @constructor 
 */
Garmin.Sample = function(){};
Garmin.Sample = Class.create();
Garmin.Sample.prototype = {

	initialize: function() {
		// lazy loading related values
		this.isLazyLoaded = false;
		this.factory = null;
		this.dom = null;
		
		// measurements of this sample
		this.measurements = new Hash();
	},

	setLazyLoading: function(isLazyLoaded, factory, dom) {
		this.isLazyLoaded = isLazyLoaded;
		this.factory = factory;
		this.dom = dom;
	},

	getMeasurements: function() {
		this.finishLoading();
		return this.measurements;
	},
	
	getMeasurement: function(mKey) {
		this.finishLoading();
		return this.measurements[mKey];
	},
	
	getMeasurementValue: function(mKey) {
		this.finishLoading();
		return (this.measurements[mKey] ? this.measurements[mKey].getValue() : null);
	},

	getMeasurementContext: function(mKey) {
		this.finishLoading();
		return (this.measurements[mKey] ? this.measurements[mKey].getContext() : null);
	},
	
	getLatitude: function() {
		return this.getMeasurementValue(Garmin.Sample.MEASUREMENT_KEYS.latitude);
	},
	
	getLongitude: function() {
		return this.getMeasurementValue(Garmin.Sample.MEASUREMENT_KEYS.longitude);
	},	
	
	getTime: function() {
		return this.getMeasurementValue(Garmin.Sample.MEASUREMENT_KEYS.time);
	},
	
	setMeasurement: function(mKey, mValue, mContext) {
		// if the key does not exist or is not of type Garmin.Measurement, create a new measurement object
		// else overwrite existing value and context
		if (!this.measurements[mKey] || !(this.measurements[mKey] instanceof Garmin.Measurement)) {
			this.measurements[mKey]= new Garmin.Measurement(mValue, mContext);
		} else {
			this.measurements[mKey].setValue(mValue);
			this.measurements[mKey].setContext(mContext);
		}
	},
	
	/** Determines if this Sample is valid for determing location
	 * @type Boolean
	 * @return True if latitude and longitude exist, false otherwise
	 */
    isValidLocation: function() {
    	var latitude = this.getMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude);
    	var longitude = this.getMeasurement(Garmin.Sample.MEASUREMENT_KEYS.latitude);    	
        return ((latitude != null && latitude.getValue() != null) && 
        		(longitude != null && longitude.getValue() != null));
    },	
	
	/** Finish loading the measurements for this sample if previously lazy-loaded.
	 */
	finishLoading: function() {
		if (this.isLazyLoaded) {
			this.factory.finishLoadingSample(this.dom, this);
		}	
	},	
	
	printMe: function(tabs) {
		var output = ""
		output += tabs + "  [Sample]\n";	
		
		var measKeys = this.measurements.keys();
		for (var i = 0; i < measKeys.length; i++) {
			output += tabs + "    " + measKeys[i] + ":\n";	
			output += this.measurements[measKeys[i]].printMe(tabs + "    "); 
		}
		
		return output;
	},
	
	toString: function() {
		return "[Garmin.Sample]"
	}
};

Garmin.Sample.MEASUREMENT_KEYS = {
	cadence:			"cadence",
	distance:			"distance",
	elevation:			"elevation",
	heartRate:			"heartRate",
	latitude:			"latitude",
	longitude:			"longitude",
	sensorState:		"sensorState",
	time:				"time"
};
/*
// Dynamic include of required libraries and check for Prototype
// Code taken from scriptaculous
// TODO: put this code in a library and reuse is instead of copying it to new files
var GarminSample = {
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
			throw("GarminSample requires the Prototype JavaScript framework >= 1.5.0");
		}

		$A(document.getElementsByTagName("script"))
		.findAll(
			function(s) {
				return (s.src && s.src.match(/GarminSample\.js(\?.*)?$/))
			}
		)
		.each(
			function(s) {
				var path = s.src.replace(/GarminSample\.js(\?.*)?$/,'../../');
				var includes = s.src.match(/\?.*load=([a-z,]*)/);
				(includes ? includes[1] : 'garmin/activity/GarminMeasurement').split(',').each(
					function(include) {
						GarminSample.require(path+include+'.js') 
					}
				);
			}
		);
	}
}

GarminSample.load();*/