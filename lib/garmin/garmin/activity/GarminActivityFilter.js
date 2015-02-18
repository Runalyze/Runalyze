if (Garmin == undefined) var Garmin = {};
/**
 * Copyright &copy; 2007-2010 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview This file contains a number of filters used to filter an array of Garmin.Activity.
 * @version 1.9
 */

/**Provides basic workflow for filtering an array of Garmin.Activity.
 * @class Garmin.BasicActivityFilter
 * @constructor 
 * @param (function) userFilterLogic - a function provided by the user containing the actual logic
 * 										used to filter the array of Garmin.Activity. An array containing
 * 										the activities will be passed to this function.
 */
Garmin.BasicActivityFilter = function(userFilterLogic){};
Garmin.BasicActivityFilter = Class.create();
Garmin.BasicActivityFilter.prototype = {
	
	initialize: function(userFilterLogic) {		
		// filter interal values
		this.activities = null;
		this.filterQueue = null;
		
		// user specified values
		this.userFilterLogic = userFilterLogic;
	},
	
	run: function(activities, filterQueue) {
		// set the activities
		this.activities = activities;
	
		// prepare the filter queue if not already available		
		if (filterQueue != null) {
			if (filterQueue instanceof String) {
				if (window[filterQueue] == null	|| !(window[filterQueue] instanceof Array)) {
					window[filterQueue] = new Array();
				}
				this.filterQueue = window[filterQueue];
			} else if (filterQueue instanceof Array) {
				this.filterQueue = filterQueue;
			}
		}
	
		// add myself to the queue if one exists		
		if (this.filterQueue != null) {
			this.filterQueue.push(this);
		}

		// wait for my turn
		this._wait();
	},
	
	_wait: function() {
		// check for my turn
		if (garminFilterQueue != null 
			&& garminFilterQueue.length > 0 
			&& garminFilterQueue[0] != this) {
			//console.debug("waiting for my turn to filter...");			
			// delay before checking for my turn again
			setTimeout(function(){this._wait();}.bind(this), 500);
		} else {
			// the wait is over, start processing
			this._process();
		}
	},
	
	_process: function() {
		// run user code to filter the activities
		this.userFilterLogic(this.activities);
		
		// cleanup this filter
		this._finish();
	},	
	
	_finish: function() {
		if (this.filterQueue != null) {
			// remove myself from the queue, normally
			// would be position 0.  Looping through
			// all elements just incase position is not
			// 0.
			for (var i = 0; i < this.filterQueue.length; i++) {
				if (this.filterQueue[i] == this) {			
					this.filterQueue.splice(i, 1);
					break;
				}
			}
		}
	}
};

/**Provides workflow for activity filtering logic that requires an ajax call.
 * @class Garmin.AjaxActivityFilter
 * @constructor
 * @param (function) preAjaxFilterLogic - a function provided by the user containing the actual logic
 * 										used to filter the array of Garmin.Activity before the ajax call. 
 * 										An array containing the activities will be passed to this function along
 * 										with the ajax call options.  
 * @param (function) postAjaxFilterLogic - a function provided by the user containing the actual logic
 * 										used to filter the array of Garmin.Activity after the ajax call. 
 * 										An array containing the activities will be passed to this function along
 * 										with the ajax response text and xml.
 * @param (String) ajaxURL - the path for the ajax call.
 * @param (Hash) ajaxOptions - options used for the ajax call. Please see http://www.prototypejs.org/api/ajax/options.
 */
Garmin.AjaxActivityFilter = function(preAjaxFilterLogic, postAjaxFilterLogic, ajaxURL, ajaxOptions){};
Garmin.AjaxActivityFilter = Class.create();
Garmin.AjaxActivityFilter.prototype = Object.extend(new Garmin.BasicActivityFilter(), {
	
	initialize: function(preAjaxFilterLogic, postAjaxFilterLogic, ajaxURL, ajaxOptions) {
		// filter interal values
		this.activities = null;
		this.ajaxRequest = null;
		this.filterQueue = null;
		
		// user specified values
		this.preAjaxFilterLogic = preAjaxFilterLogic;
		this.postAjaxFilterLogic = postAjaxFilterLogic;
		this.ajaxURL = ajaxURL;
		this.ajaxOptions = ajaxOptions;
	},
	
	_process: function() {
		// run user code to filter the activities before ajax call
		if (this.preAjaxFilterLogic != null) {
			this.preAjaxFilterLogic(this.activities, this.ajaxOptions);
		}
		
		// backup user ajax callbacks
		this.userAjaxComplete = this.ajaxOptions.onComplete;
		this.userAjaxException = this.ajaxOptions.onException;
		this.userAjaxFailure = this.ajaxOptions.onFailure;
		this.userAjaxSuccess = this.ajaxOptions.onSuccess;
		
		// set my own ajax callbacks
		//this.ajaxOptions.onCreate = this.onAjaxCreate.bind(this);
		this.ajaxOptions.onComplete = this.onAjaxComplete.bind(this);
		this.ajaxOptions.onException = this.onAjaxException.bind(this);
		this.ajaxOptions.onFailure = this.onAjaxFailure.bind(this);
		//this.ajaxOptions.onInteractive = this.onAjaxInteractive.bind(this);
		//this.ajaxOptions.onLoaded = this.onAjaxLoaded.bind(this);
		//this.ajaxOptions.onLoading = this.onAjaxLoading.bind(this);		
		this.ajaxOptions.onSuccess = this.onAjaxSuccess.bind(this);
		//this.ajaxOptions.onUninitialized = this.onAjaxUninitialized.bind(this);		
		
		// initiate ajax call
		this.ajaxRequest = new Ajax.Request(this.ajaxURL, this.ajaxOptions);
	},	
	
	onAjaxComplete: function(transport) {
		// call user callback if one exists
		if (this.userAjaxComplete != null) {
			this.userAjaxComplete(transport);
		}
		
		// cleanup this filter
		this._finish();
	},
	
	onAjaxException: function(request, exception) {
		// call user callback if one exists
		if (this.userAjaxException != null) {
			this.userAjaxException(transport);
		}
		
		// cleanup this filter
		this._finish();		
	},

	onAjaxFailure: function(transport) {
		// call user callback if one exists
		if (this.userAjaxFailure != null) {
			this.userAjaxFailure(transport);
		}
		
		// no need to clean up since onComplete will be called
		// cleanup this filter
		//this._finish();
	},
	
	onAjaxSuccess: function(transport) {
		// call user callback if one exists
		if (this.userAjaxSuccess != null) {
			this.userAjaxSuccess(transport);
		}
		
		// run post ajax user code to filter the activities
		if (this.postAjaxFilterLogic != null) {
			this.postAjaxFilterLogic(this.activities, transport.responseText, transport.responseXML);
		}
		
		// no need to clean up since onComplete will be called
		// cleanup this filter
		//this._finish();
	}						
});

/**Filtering logic used by filters in Garmin.FILTER. 
 */
Garmin.FilterCode = {
	filterForRoute: function(activities) {
		Garmin.FilterCode.filterForSeriesType(activities, [Garmin.Series.TYPES.route]);
	},
	
	filterForRouteAndHistory: function(activities) {
		Garmin.FilterCode.filterForSeriesType(activities, [Garmin.Series.TYPES.history, Garmin.Series.TYPES.route]);
	},
	
	filterForHistory: function(activities) {
		Garmin.FilterCode.filterForSeriesType(activities, [Garmin.Series.TYPES.history]);
	},
	
	filterForWaypoint: function(activities) {
		Garmin.FilterCode.filterForSeriesType(activities, [Garmin.Series.TYPES.waypoint]);
	},
	
	filterForSeriesType: function(activities, seriesTypes) {
		//console.debug("Started with " + myActivities.length + " activities.");
		// loop through all activities and look for ones
		// with series of type seriesType. looping in
		// reverse so elements can be removed while looping.
		for (var i = activities.length; i > 0; i--) {
			var series = activities[i-1].getSeries();
			if (series.length > 0) {
				var match = false;
				// loop through all the series contained by
				// this activity for a match
				for (var j = 0; j < series.length; j++) {
					for (var k = 0; k < seriesTypes.length; k++) {
						if (series[j].getSeriesType() == seriesTypes[k]) {
							match = true;
							break;
						}
					}
				}
				// if this activity is not of seriesType then
				// remove it from activities
				if (!match) {
					activities.splice(i-1, 1);
				}
			}
		}
		//console.debug("Ended with " + myActivities.length + " activities.");
	}	
};

/**Premade filters ready to be used.
 */
Garmin.FILTERS = {
	historyOnly:	 			new Garmin.BasicActivityFilter(Garmin.FilterCode.filterForHistory),
	routeOnly:					new Garmin.BasicActivityFilter(Garmin.FilterCode.filterForRoute),
	waypointOnly:				new Garmin.BasicActivityFilter(Garmin.FilterCode.filterForWaypoint),
	routeAndHistoryOnly:		new Garmin.BasicActivityFilter(Garmin.FilterCode.filterForRouteAndHistory)
};