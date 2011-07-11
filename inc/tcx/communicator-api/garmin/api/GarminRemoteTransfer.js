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
 * @fileoverview Garmin.RemoteTransfer is a high-level API to transfer data to remote servers using POST, 
 * web service calls, and the like.
 * 
 * @author Diana Chow diana.chow at garmin.com
 * @version 1.0
 */
/** 
 *
 * @class Garmin.RemoteTransfer
 * @constructor 
 * 
 * requires Prototype and Garmin.DeviceDisplay
 */
Garmin.RemoteTransfer = function(){}; //just here for jsdoc
Garmin.RemoteTransfer = Class.create();
Garmin.RemoteTransfer.prototype = {
	
	initialize: function() {
	    this.ajaxRequest = null;
	    this.ajaxResponse = null;
	    this.apiResponse = null;
	    this.errorMsg = null;
	},
	
	/** Open a REST request to a web service.  The result is returned (if any) along 
	 * with request status and any error info provided by the HTTP response.
	 * 
	 * @param url - the URL of the web service endpoint
	 * @param ajaxOptions - options used for the ajax call. Please see http://www.prototypejs.org/api/ajax/options. 
	 * @return a response hash containing the AJAX response object, and an error message if there was one. 
	 * 	Ids of the response elements are response and error.
	 */
	openRequest: function(url, ajaxOptions) {
		this.ajaxRequest = new Ajax.Request(url, ajaxOptions);
	},
	
	/** Abort the active http request, if any. 
	 */
	abortRequest: function() {
	    Ajax.Request.prototype.abort(this.ajaxRequest);
	}
};

/**
 * Ajax.Request.abort
 * extend the prototype.js Ajax.Request object so that it supports an abort method
 */
Ajax.Request.prototype.abort = function(xhr) {
    // prevent and state change callbacks from being issued
    xhr.transport.onreadystatechange = Prototype.emptyFunction;
    // abort the XHR
    xhr.transport.abort();
    // update the request counter
    Ajax.activeRequestCount--;
};

/**
 * Error messages used by this class.
 */
Garmin.RemoteTransfer.MESSAGES = {
    badRequestException: "There was a problem with the request.  Check request parameters.",
	/**
	 * Message used for general exceptions
	 */
	generalException: "An error occured while completing the request.",
	/**
	 * Message used when there is no response from the request.
	 */
	noResponseException: "No response from the URL.  Check URL and domain permissions.",
	/**
	 * Message used when the URL is not found (404).
	 */
	urlNotFoundException: "The URL requested was not found (404).  Check URL."
};
