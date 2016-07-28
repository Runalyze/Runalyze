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
 * @fileoverview Garmin.Geocode A place-holder for goecoding services.
 * @version 1.9
 */
/** Currently just a wrapper for Google geocode service.  This code could go into GarminDevicecontrol.js 
 * but it would create Google object dependencies.
 *
 * @class Garmin.Geocode
 * @constructor 
 */
Garmin.Geocode = function(){}; //just here for jsdoc
Garmin.Geocode = Class.create();
Garmin.Geocode.prototype = {

	/** Prototype constructor
	 */
	initialize: function() {
    	this.geocoder = new GClientGeocoder();
		this._broadcaster = new Garmin.Broadcaster();
	},
	
	/** Takes an address and uses geocoding service to get waypoint.
     * Registered listeners will receive either a 'onFinishedFindLatLon(Garmin.Waypoint)' or 'onException(Error)' call.
     * For best results, address should be a comma delineated list of street, suite #, city-state-zip or just zip fields.
     * It's less confusing to the geocoder if the person or business name is excluded.
     * @param {String} comma delineated address.
	 * @type void
	 */
	findLatLng: function(address) {
		var geo = this;
        
        geo.geocoder.getLocations(
        	address,
        	function(response) {
        		if (!response) {
					var err = new Error("Unable to convert address to location: "+address);
			        geo._broadcaster.dispatch("onException", {msg: err});
	            } else {
	        		place = response.Placemark[0];
			        point = new GLatLng(place.Point.coordinates[1],
			                            place.Point.coordinates[0]);

					// address is the input string.  AddressDetails is a hash containing the structured address.
					// See http://www.google.com/apis/maps/documentation/#Geocoding_Structured
			        var latLng = new Garmin.WayPoint(point.lat(), point.lng(), null, address, place.AddressDetails);
			        
			        geo._broadcaster.dispatch("onFinishedFindLatLon", {waypoint: latLng});
	            }
        	}
        );
	},
	
	/** Register to be an event listener.  An object that is registered will be dispatched
     * a method if they have a function with the same dispatch name.  So if you register a
     * listener with an onFinishFindDevices, and the onFinishFindDevices message is called, you'll
     * get that message.  See class comments for event types
     *
     * @param {Object} Object that will listen for events coming from this object 
     * @see {Garmin.Broadcaster}
     */	
	register: function(listener) {
        this._broadcaster.register(listener);
	}

};