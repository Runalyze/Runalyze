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
 * @fileoverview Garmin.Broadcaster is for registering listeners and dispatching call-back metheds.
 * @version 1.9
 */
/** 
 * @class Broadcaster
 * Acts as an event broadcaster.  Code is pretty similar to Ajax.Responders, 
 * but doesn't need to extend Enumerable.  
 * <br><br>
 * To use, register an object, then dispatch methods.<br>
 * var toBeAlerted = new AlertedDude();<br>
 * var broadcaster = new Broadcaster();<br>
 * <br>
 * broadcaster.register(toBeAlerterd);<br>
 * broadcaster.dispatch("alerting");<br>
 * 
 * that will call toBeAlerted.alerting();<br>
 * if you pass an object w/ the dispatch call the object will be passed as well.<br>
 * Most calls are implemented using JSON, with controller as the owning broadcaster
 * object.<br>
 * so ... <br>
 * broadcaster.dispatch("alerting", {message: "howdy", controller: this});<br>
 * toBeAlerted.alerting({message: 'howdy', controller: broadcaster})
 * @constructor 
 */
Garmin.Broadcaster = function(){}; //just here for jsdoc
Garmin.Broadcaster = Class.create();
Garmin.Broadcaster.prototype = {
	initialize: function() {
	    this.responders = new Array();
	},

	/**
     * Register an object to listen for events
     * 
     * @param {Object} responder
     * @member Garmin.Broadcaster
     */
	register: function(responderToAdd) {
	  if (!this.responders.include(responderToAdd))
	    this.responders.push(responderToAdd);
	},

	/**
     * Unregister an object that is listening
     * 
     * @param {Object} responder
     * @member Garmin.Broadcaster
     */
	unregister: function(responderToRemove) {
	  this.responders = this.responders.without(responderToRemove);
	},

	/**
     * Dispatch an event to all listeners
     * 
     * @param {String} callback
     * @param {Object} json
     * @member Garmin.Broadcaster
     */
	dispatch: function(callback, json) {
	  this.responders.each(function(responder) {
	    if (responder[callback] && typeof responder[callback] == 'function') {
	      try {
	        responder[callback].apply(responder, [json]);
	      } catch (e) { alert(e) }
	    }
	  });
	}
};