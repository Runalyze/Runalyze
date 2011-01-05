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
 * @fileoverview Garmin.Activity A data structure representing an activity
 * 
 * @author Bobby Yang bobby.yang.at.garmin.com
 * @version 1.0
 */
/**A data structure for storing data commonly found in various
 * formats supported by various gps devices.  Some examples are
 * gpx track, gpx route, gpx wayoint, and tcx activity.
 * @class Garmin.Activity
 * @constructor 
 */
Garmin.Activity = function(){};
Garmin.Activity = Class.create();
Garmin.Activity.prototype = {
	
	initialize: function() {
		this.attributes = new Hash();
		this.summary = new Garmin.Sample();
		this.series = new Array();
	},
	
	getAttributes: function() {
		return this.attributes;
	},
	
	getAttribute: function(aKey) {
		return this.attributes[aKey];
	},
	
	setAttribute: function(aKey, aValue) {
		this.attributes[aKey] = aValue;
	},
	
	getSeries: function() {
		return this.series;
	},
	
	getHistorySeries: function() {
		for (var i = 0; i < this.series.length; i++) {
			if (this.series[i].getSeriesType() == Garmin.Series.TYPES.history) {
				return this.series[i];
			}
		}
		return null;
	},
	
	addSeries: function(series) {
		this.series.push(series);
	},
	
	getSingleSeries: function(index) {
		var targetSeries = null;
		if (index >= 0 && index < this.series.length) {
			targetSeries = this.series[index];
		}
		return targetSeries;
	},
	
	getSummary: function() {
		return this.summary;
	},
	
	getSummaryValue: function(sKey) {
		return this.summary.getMeasurement(sKey);
	},
	
	setSummaryValue: function(sKey, sValue, sContext) {
		this.summary.setMeasurement(sKey, sValue, sContext);
	},
	
	getEndTime: function() {
		return this.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.endTime).getValue();
	},	
	
	getStartTime: function() {
		return this.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime).getValue();
	},
	
	printMe: function(tabs) {
		var output = "";
		output += tabs + "\n\n[Activity]\n";
		
		output += tabs + "  attributes:\n";
		var attKeys = this.attributes.keys();
		for (var i = 0; i < attKeys.length; i++) {
			output += tabs + "    " + attKeys[i] + ": " + this.attributes[attKeys[i]] + "\n"; 
		}
		
		output += tabs + "  summary:\n";
		output += this.summary.printMe(tabs + "  ");

		output += tabs + "  series:\n";		
		for (var i = 0; i < this.series.length; i++) {
			output += this.series[i].printMe(tabs + "  ");
		}
		
		return output;
	},
	
	toString: function() {
		return "[Garmin.Activity]"
	}
};

Garmin.Activity.ATTRIBUTE_KEYS = {
	activityName:		"activityName",
	activitySport:		"activitySport",
	creatorName:		"creatorName",
	creatorUnitId:		"creatorUnitId",
	creatorProdId:		"creatorProductId",
	creatorVersion:		"creatorVersion",
	dom:				"documentObjectModel"
};

Garmin.Activity.SECTION_KEYS = {
	gpsSignals:			"gpsSignal",
	heartRateSignals:	"heartRateSignal",	
	laps:				"laps",
	tracks:				"tracks"
};

Garmin.Activity.SUMMARY_KEYS = {
	avgHeartRate:		"averageHeartRate",
	calories:			"calories",	
	endTime:			"endTime",
	intensity:			"intensity",	
	maxHeartRate:		"maximumHeartRate",
	maxSpeed:			"maximumSpeed",		
	startTime:			"startTime",
	totalDistance:		"totalDistance",	
	totalTime:			"totalTime"
};
/*
// Dynamic include of required libraries and check for Prototype
// Code taken from scriptaculous
// TODO: put this code in a library and reuse is instead of copying it to new files
var GarminActivity = {
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
			throw("GarminActivity requires the Prototype JavaScript framework >= 1.5.0");
		}

		$A(document.getElementsByTagName("script"))
		.findAll(
			function(s) {
				return (s.src && s.src.match(/GarminActivity\.js(\?.*)?$/))
			}
		)
		.each(
			function(s) {
				var path = s.src.replace(/GarminActivity\.js(\?.*)?$/,'../../');
				var includes = s.src.match(/\?.*load=([a-z,]*)/);
				var dependencies = 'garmin/activity/GarminMeasurement' +
									',garmin/activity/GarminSample' +
									',garmin/activity/GarminSeries';
			    (includes ? includes[1] : dependencies).split(',').each(
					function(include) {
						GarminActivity.require(path+include+'.js') 
					}
				);
			}
		);
	}
}

GarminActivity.load();*/