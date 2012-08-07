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
 * @fileoverview Garmin.ActivityMatcher An activity filter for synchronizing lists
 * @version 1.9
 */

/** The newer activity filter for synchronizing lists.  It makes an AJAX request
 * to a remote service in order to detect matches on the server.  It is dependent
 * on Garmin.Axm.ActivityMatch, which wraps around the json response making it 
 * easy to retrieve the JSON values via javascript.   
 *  
 * @class Garmin.ActivityMatcher
 */ 
Garmin.ActivityMatcher = function(deviceXml, allActivityIds, ajaxUrl, ajaxOptions, callback){}; //just here for jsdoc
Garmin.ActivityMatcher = Class.create();
Garmin.ActivityMatcher.prototype = {
    initialize: function(deviceXml, allActivityIds, ajaxUrl, ajaxOptions, callback) {
        this.allActivityIds = allActivityIds;
        this.ajaxUrl = ajaxUrl;
        this.ajaxOptions = ajaxOptions;
        this.ajaxOptions.parameters = {deviceXml: deviceXml, externalIds: allActivityIds};
        this.callback = callback;
        
        this.apiRequest = new Garmin.RemoteTransfer();
        this.apiResponse = null;
        
        this.activityMatches = null;
    },
    
    /** Run the filter.  Makes the AJAX request to find matching IDs on the
     * server and populates the filtered list. 
     */
    run: function() {
        
        this.ajaxOptions.onSuccess = function(xhr) {
            this.activityMatches = new Garmin.Axm.ActivityMatch(xhr.responseJSON);
        	this.callback();
    	}.bind(this);
        
        this.apiResponse = this.apiRequest.openRequest(this.ajaxUrl, this.ajaxOptions);
    },
    
    /** Get the matching JSON object for given activity ID from the original response object.
     * Null if not found or if activity matcher service is unavailable.
     */
    get: function(activityId) {
        if( this.activityMatches != null) {
            return this.activityMatches.getMatch(activityId);
        }
    }
}

/**
 * Wrapper for the Axm activityMatch JSON return object.  This is a lightweight wrapper.  All
 * it does is find the given activity in the original response object and return it.  You should access
 * the properties of that object directly.
 */
if (Garmin.Axm == undefined) Garmin.Axm = {};
Garmin.Axm.ActivityMatch = function(){}; // for jsdoc
Garmin.Axm.ActivityMatch = Class.create();
Garmin.Axm.ActivityMatch.prototype = {
    
    initialize: function(json) {
        if( json != null && json['matches'] != null) {
            this.activityMatchesJson = json['matches'];
        }
    },
    
    /** Looks up the match for the given external ID.  This returns the JSON object for 
     * direct access.  isMatch and isDeleted can be accessed directly from the return object:
     * 
     * returned.isMatch
     * returned.isDeleted
     * 
     * @return {JSON} the match result, or null if the ID is not found in the original response
     */
    getMatch: function(activityId) {
        return this.activityMatchesJson[activityId];
    }
}