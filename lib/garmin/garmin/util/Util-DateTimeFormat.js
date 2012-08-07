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
 * @fileoverview Garmin.DateTimeFormat Utility class for parsing GPX dates and other date support functions.
 * @version 1.9
 */
/**
 * @class Garmin.DateTimeFormat
 * @constructor 
 */
Garmin.DateTimeFormat = function(){}; //just here for jsdoc
Garmin.DateTimeFormat = Class.create();
Garmin.DateTimeFormat.prototype = {

	initialize: function() {
		this.hoursInADay = 24;
	    this.minutesInAnHour = 60;
	    this.secondsInAMinute = 60;
	    this.millisecondsInASecond = 1000;
	    this.millisecondsInADay = this.hoursInADay * this.minutesInAnHour * this.secondsInAMinute * this.millisecondsInASecond;
	    this.millisecondsInAnHour = this.minutesInAnHour * this.secondsInAMinute * this.millisecondsInASecond;
	    this.millisecondsInAMinute = this.secondsInAMinute * this.millisecondsInASecond;

		this.xsdString = "";
		this.date = new Date();
	},

	/**
     * Get the date object associated with this object
     * 
     * @type Date
     * @return The Date object that 
     */
	getDate: function() {
		return this.date;
	},

	/**
     * Based on 2003-01-31T17:42:14.160Z (YYYY-MM-DDTHH:MM:SS.sssZ) date this will set this 
     * objects date after parsing the standard time string
     * 
     * @param {String} xsdDateTime
     * @type Garmin.DateTimeFormat
     * @return This object with the parsed date
     */
	parseXsdDateTime: function(xsdDateTime) {
		this.xsdString = xsdDateTime;
	    var pieces = xsdDateTime.split('T');
	    var datePiece = pieces[0];
	    var timePiece = pieces[1];
	    var offset = 0;
	
		// xsd:dateTime -> [-]CCYY-MM-DDThh:mm:ss[Z|(+|-)hh:mm]
	
	    // tear apart the date
	    var datePieces = datePiece.split('-');
	    
	    // parse the year
	    var year = parseInt(datePieces[0],10);
	    
	    // parsae the month, javascript month is 0-based
	    var month = parseInt(datePieces[1],10) -1;
	    
	    // parse the day
	    var dayOfMonth = parseInt(datePieces[2],10);
	    
	    // find the index in the timepiece where the offset piece begins
	    var offsetIndex;
	    if (timePiece.indexOf('Z') != -1) {
	    	offsetIndex = timePiece.indexOf('Z');
	    } else if (timePiece.indexOf('+') != -1) {
			offsetIndex = timePiece.indexOf('+');
	    } else if (timePiece.indexOf('-') != -1) {
			offsetIndex = timePiece.indexOf('-');
	    } else {
	    	offsetIndex = timePiece.length;
	    }
	    
	    // tear apart the offset piece
		var offsetPieces = timePiece.substring(offsetIndex).split(':');
		var offsetHour = 0;
		var offsetMinute = 0;
		
		// parse the offset hour and offset minute if they exist
		if (offsetPieces.length > 1) {
			offsetHour = parseInt(offsetPieces[0], 10);
			offsetMinute = parseInt(offsetPieces[1], 10); 
		}
		
		// figure out the time zone offset in milliseconds
		var offsetMilliseconds = ((((offsetHour * 60) + offsetMinute) * 60) * 1000);
		
	    // tear apart the time, exclude offset string
	    var timePieces = timePiece.substring(0, offsetIndex).split(':');
	    
	    // parse the hour
	    var hourBase24 = parseInt(timePieces[0],10);
	    
	    // parse the minute
	    var minute = parseInt(timePieces[1],10);
	    
	    // split the second up for milliseconds
	    var secondPieces = timePieces[2].split('.');
	    
	    // parse the second
	    var second = parseInt(secondPieces[0],10);
	    
	    // parse the millisecond
	    var millisecond = 0;
	    if(secondPieces.length > 1) {
	        millisecond = parseInt(secondPieces[1],10);
	    }
	    
	    // create the date object	    
	    var date = new Date();
	    date.setUTCFullYear(year);
	    date.setUTCMonth(month);
	    date.setUTCDate(dayOfMonth);
	    date.setUTCHours(hourBase24);
	    date.setUTCMinutes(minute);
	    date.setUTCSeconds(second);
	    date.setUTCMilliseconds(millisecond);
	    
	    // apply the time zone offset to the date object so its in utc time
		date.setTime(date.getTime() - offsetMilliseconds);
	    
	    this.date = date;
	    return this;
	},

	/**
     * Generate a duration string of the format hh:mm:ss
     * 
     * @param {Number} Number of milliseconds to convert
     * @type String
     * @return String of the format hh:mm:ss built from the number of milliseconds
     */
	formatDuration: function(milliseconds) {
	    var remaining = milliseconds;
	    var result = "";
	    var separator = ':';
	    var units = new Array(this.millisecondsInADay, this.millisecondsInAnHour, this.millisecondsInAMinute, this.millisecondsInASecond);
	    for(var whichUnit = 0; whichUnit < units.length; whichUnit++) {
	        var millisecondsInUnit = units[whichUnit];
	        var totalOfUnit = parseInt(remaining / millisecondsInUnit);
	        if(whichUnit != 0 || totalOfUnit != 0) {
	            if(totalOfUnit < 10)  result += "0";
	            result = result + totalOfUnit.toString();
	            if(whichUnit < units.length-1) result += separator;
	        }
	        remaining = remaining - (totalOfUnit * millisecondsInUnit);
	    }
	    return result;
	},

	/**
     * Get the duration from this date to another date in the future.
     * 
     * @param {Garmin.DateTimeFormat} The end date to get the duration to.
     * @type String
     * @return Duration string (hh:mm:ss)
     */
	getDurationTo: function(endDateTime) {
	    return this.formatDuration(endDateTime.getDate().getTime() - this.getDate().getTime());
	},

	/**
     * getDayOfYear
     * http://www.merlyn.demon.co.uk/js-date0.htm
     */
	getDayOfYear: function() {
	    with (this.getDate()) {
	        var Y = getFullYear(), M = getMonth(), D = getDate();
	    }
	    var K, N;
	    N = (Date.UTC(Y, M, D) - Date.UTC(Y, 0, 0)) / 86400000;
	
	    M++;
	    K = 2 - (Y % 4 == 0);
	    N = Math.floor(275 * M / 9) - K * (M > 2) + D - 30;
	
	    with (this.getDate()) {
	        K = valueOf();
	        setMonth(0);
	        setDate(0);
	        N = Math.round((K - valueOf()) / 86400000);
	    }
		return N;
	},

	/** Formats date.
	 * Uses Garmin.DateTimeFormat.FORMAT.date format string.
	 * @type String
	 * @return Date string of the format mm/dd/yyyy
     */
	getDateString: function() {
		return this.formatDate(true);
	},

	/** Formats timestamp using 12 hour clock.
	 * @type String
	 * @return formatted timestamp
     */
	getTimeString: function() {
		return this.format(Garmin.DateTimeFormat.FORMAT.timestamp12hour, true, true);
	},

	/**
	 * @type String
	 * @return Xsd date string of the format mm/dd/yyyy
     * @member Garmin.DateTimeFormat
     */
	getXsdString: function() {
		return this.xsdString;
	},

	/**
	 * @type String
	 * @return this.date.toString()
     */
	toString: function() {
		return this.date.toString();
	},
	
	/** Format date using Garmin.DateTimeFormat.FORMAT.date template. 
	 * @param {Boolean} if true single digits have a zero inserted on the left
	 * @type String
	 * @return formatted date
	 */
	formatDate: function(leftPad) {
		return this.format(Garmin.DateTimeFormat.FORMAT.date, leftPad);
	},
	
	/** Format time using Garmin.DateTimeFormat.FORMAT.time template. 
	 * @param {Boolean} if true single digits have a zero inserted on the left
	 * @type String
	 * @return formatted time
	 */
	formatTime: function(leftPad) {
		return this.format(Garmin.DateTimeFormat.FORMAT.time, leftPad);
	},
	
	/** Format timestamp using Garmin.DateTimeFormat.FORMAT.timestamp template. 
	 * @param {Boolean} if true single digits have a zero inserted on the left
	 * @type String
	 * @return formatted timestamp
	 */
	formatTimestamp: function(leftPad) {
		return this.format(Garmin.DateTimeFormat.FORMAT.timestamp, leftPad);
	},
	
	/** Applies template to date fields.  
	 * Valid fields are: month, day, year, hour, minute, second, millisecond and meridian (AM or PM)
	 * timezone is also available but is known to be unreliable.
	 * Uses prototype Template object.
	 * @param {String} template which specifies formatting
	 * @param {Boolean} leftPad if true single digits have a zero inserted on the left. Defaults to true.
	 * @param {Boolean} twelveHourClock if true set clock to 12 hour verses 24
	 * @type String
	 * @return formatted date
     */
	format: function(template, leftPad, twelveHourClock) {
		if (leftPad!=false)
			leftPad = true;
		var hours = this.date.getHours();
		var values = {
			meridian: this.date.getHours() >= 12 ? "PM" : "AM",
			month: this.leftPad(this.date.getMonth()+1, leftPad), 
			day: this.leftPad(this.date.getDate(), leftPad), 
			year: this.date.getFullYear(), 
			hour: this.leftPad( (twelveHourClock && hours > 12) ? hours - 12 : hours, leftPad), 
			minute: this.leftPad(this.date.getMinutes(), leftPad), 
			second: this.leftPad(this.date.getSeconds(), leftPad),
			millisecond: this.date.getMilliseconds(),
			timezone: this.date.getTimezoneOffset() / 60
		};
		return new Template(template).evaluate(values);
	},
	
	/** left pad integer if activate is true
	 * @param {Number} integer to left pad
	 * @param {Boolean} activate must be true or no padding is done
	 * @type String
	 */
	leftPad: function(integer, activate) {
		return (activate && integer < 10) ? "0" + integer : "" + integer;
	}
}

/** Internationalization constants for date/time formatting.
 */
Garmin.DateTimeFormat.FORMAT = {
	date: "#{month}/#{day}/#{year}",
	time: "#{hour}:#{minute}:#{second}",
	timestamp: "#{month}/#{day}/#{year} #{hour}:#{minute}:#{second}",
	timestamp12hour: "#{month}/#{day}/#{year} #{hour}:#{minute}:#{second} #{meridian}"
};

