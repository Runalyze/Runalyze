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
 * @fileoverview Garmin.File A data structure representing a file
 * @version 1.9
 */
/**A data structure for storing data commonly found in file
 * formats supported by various gps devices.
 * @class Garmin.File
 * @constructor 
 */
Garmin.File = function(){};
Garmin.File = Class.create();
Garmin.File.prototype = {
	
	initialize: function() {
		this.attributes = new Hash();
		this.id = new Garmin.FileId();
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
	
	getId: function() {
		return this.id;
	},
	
	getIdValue: function(iKey) {
		return this.id.getValue(iKey);
	},
	
	setIdValue: function(iKey, iValue) {
		this.id.setValue(iKey, iValue);
	},
	
	setId: function(id) {
	    this.id = id;
	},
	
    getCreationTime: function() {
        return this.getAttribute(Garmin.File.ATTRIBUTE_KEYS.creationTime); 
    },
	
	getIdString: function() {
		return this.getIdValue(Garmin.FileId.KEYS.id).getValue();
	},
	
	printMe: function(tabs) {
		var output = "";
		output += tabs + "\n\n[File]\n";
		
		output += tabs + "  attributes:\n";
		var attKeys = this.attributes.keys();
		for (var i = 0; i < attKeys.length; i++) {
			output += tabs + "    " + attKeys[i] + ": " + this.attributes[attKeys[i]] + "\n"; 
		}
		
		output += tabs + "  id:\n";
		output += this.id.printMe(tabs + "  ");

		return output;
	},
	
	toString: function() {
		return "[Garmin.File]"
	}
};

Garmin.File.ATTRIBUTE_KEYS = {
	isDirectory:		"IsDirectory", // 5/7/09 this guy isn't used by the API yet
	path:		        "Path",
	type:		        "Type",
	creationTime:		"CreationTime",
	dom:				"documentObjectModel",
    size:               "Size", //bytes. unsigned long, optional
    md5Checksum:        "MD5Sum" //hex string, optional, not present if IsDirectory == true
};

Garmin.FileId = function(){};
Garmin.FileId = Class.create();
Garmin.FileId.prototype = {
	
	initialize: function() {
	    this.values = new Hash();
	},
	
	getValue: function(key) {
	    return this.values[key];
	},
	
	setValue: function(key, value){
	    this.values[key] = value;
	},
	
	toString: function() {
		return "[Garmin.FileId]"
	}
};

Garmin.FileId.KEYS = {
	id:                         "Id",
	fileType:                   "FileType",
    manufacturer:               "Manufacturer",
    product:                    "Product",
    serialNumber:               "SerialNumber"
};

/**
 * Mapping of fitness file type to FIT identifier, provided by dynastream.
 */
Garmin.FileId.FILE_TYPE_MAP = {
    activities:                 "4",
    goals:                      "11",
    locations:                  "8",
    monitoring:                 "9",
    profiles:                   "2",
    schedules:                  "7",
    sports:                     "3",
    totals:                     "10"
};