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
 * @fileoverview Garmin.Device A place-holder for Garmin device information. <br/>
 * Source: 
 * <a href="http://developer.garmin.com/web/communicator-api/garmin/device/GarminDevice.js">Hosted Distribution</a> &nbsp;
 * <a href="https://svn.garmindeveloper.com/web/trunk/communicator/communicator-api/src/main/webapp/garmin/device/GarminDevice.js">Source Control</a><br/>
 * @version 1.9
 */
/** A place-holder for Garmin device information.
 * @class Garmin.Device
 *
 * @constructor 
 * @param {String} displayName for the device
 * @param {Number} number of the model
 */
Garmin.Device = function(displayName, number){}; //just here for jsdoc
Garmin.Device = Class.create();
Garmin.Device.prototype = {

	initialize: function(displayName, number) {
	    this.displayName = displayName;
	    this.number = number;
	    this.parentNumber = -1;
	    this.parent = null;
	    this.children = new Array();
	    
	    this.partNumber = null;
	    this.softwareVersion = null;
	    this.description = null;
	    this.id = null;
	    this.fileBased = false;
	    this.dataTypes = new Hash({});
	},
	
	/** The display name of this device.
	 * @type String
	 * @return display name
	 * @member Garmin.Device
	 */
	getDisplayName: function() {
		return this.displayName;
	},
	
	/** The device number that the plug-in uses to identify this device
	 * @type Number
	 * @return device number
	 */
	getNumber: function() {
		return this.number;
	},
	
	/** The parent device's number. -1 if this device has no parent.
	 * @returns {Number} parent device number
	 */
	getParentNumber: function() {
		return this.parentNumber;
	},

	/** Set parent device number
	 * @param {Number} parent device number
	 */
	setParentNumber: function(aNumber) {
		this.parentNumber = aNumber;
	},

	/** The parent device object, null if this device has no parent.
	 * @returns {Garmin.Device} parent device
	 */
	getParent: function() {
		return this.parentDevice;
	},

	/** Set parent device
	 * @param {Garmin.Device} parent device object
	 */
	setParent: function(aDevice) {
		this.parentDevice = aDevice;
	},

	/** Add a child device
	 * @param {Garmin.Device} child object
	 */
	addChild: function(aDevice) {
		this.children.push(aDevice);
	},
    
	/** The array of child devices
	 * @returns {Array} of Garmin.Device objects
	 */
    getChildren: function() {
        return this.children;
    },

	/** Set part number of device
	 * @param {String} part number
	 */
	setPartNumber: function(partNumber) {
		this.partNumber = partNumber;
	},

	/** The part number of the device
	 * @type String
	 * @return The part number of the device
	 */
	getPartNumber: function() {
		return this.partNumber;
	},

	/** Software version installed on device
	 * @param {String} Garmin.Device
	 */
	setSoftwareVersion: function(softwareVersion) {
		this.softwareVersion = softwareVersion;
	},

	/** The software version currently on the device
	 * @type String
	 * @return software version
	 */
	getSoftwareVersion: function() {
		return this.softwareVersion;
	},

	/** Set description of the device
	 * @param {String} device description
	 */
	setDescription: function(description) {
		this.description = description;
	},

	/** A description of the device.  This usually represents the model name of the device
	 * and includes the software version, i.e. "Forerunner 405  Jun 27 2008 2.17"
	 * In the GarminDevice XML, this is Model > Description.
	 * @type String
	 * @return device description
	 */
	getDescription: function() {
		return this.description;
	},

	/** set device id
	 * @param {String} device id
	 */
	setId: function(id) {
		this.id = id;
	},

	/** The device id
	 * @type String
	 * @return The device id
	 */
	getId: function() {
		return this.id;
	},
	
	/** Adds a data type to the list of data types supported by this device
	 * @param dataType A DeviceDataType object containing information about the data type being added
	 */
	addDeviceDataType: function(dataType) {
		var newDataType = new Hash();
		newDataType.set(dataType.getTypeName(), dataType);
		this.dataTypes.update(newDataType);	
	},

	/** Returns a specific DeviceDataType object
	 * @type Garmin.DeviceDataType
	 * @return The DeviceDataType object containing the specified extension
	 * @param extension The file extension of the data type you are trying to get
	 */	
	getDeviceDataType: function(extension) {
	    return this.dataTypes.get(extension);
	},

	/** Returns a hash containing all DeviceDataType objects
	 * @type Hash
	 * @return all DeviceDataType objects
	 */	
	getDeviceDataTypes: function() {
		return this.dataTypes;
	},

	/**	Returns true if the device has read support for the file type
	 * @param {String} extension The extension of the file type you are checking for support
	 * @type Boolean
	 * @return read support for the file type
	 */	
	supportDeviceDataTypeRead: function(extension) {
		var dataType = this.getDeviceDataType(extension);
		if (dataType != null && dataType.hasReadAccess()) {
			return true;
		} else {
			return false;
		}	
	},
	
	/** Check if device has write support for the file type.
	 * @param {String} extension The extension of the file type you are checking for support
	 * @type Boolean
	 * @return True if write support 
	 */		
	supportDeviceDataTypeWrite: function(extension) {
		var dataType = this.getDeviceDataType(extension);
		if (dataType != null && dataType.hasWriteAccess()) {
			return true;
		} else {
			return false;
		}			
	},

	/**
	 * Set if device is a File-based device.
     * @param {Boolean} set if device is file based
     */
	setIsFileBased: function(aBool) {
	   this.fileBased = aBool;	
	},

	/**
	 * Check if device is a File-based device.<br\>
	 * Will return false for all devices on plug-in versions prior to 2.8.1.0 <br\>
	 * @see Garmin.DevicePlugin#isDeviceFileBased
     * @returns {Boolean} true if device is file based
     */
	isFileBased: function() {
	   return this.fileBased;	
	},
	
	toString: function() {
		return "Device["+this.getDisplayName()+", "+this.getDescription()+", "+this.getNumber()+"]";
	}
	
};

/** A place-holder for Garmin Device Data Type information
 * @class Garmin.DeviceDataType
 *
 * @constructor 
 * @param {String} typeName for the data type
 * @param {String} extension file extension for the data type
 */
Garmin.DeviceDataType = function(typeName, fileExtension){}; //just here for jsdoc
Garmin.DeviceDataType = Class.create();
Garmin.DeviceDataType.prototype = {
	
	initialize: function(typeName, fileExtension) {
		this.typeName = typeName;
		this.fileExtension = fileExtension;
		this.readAccess = false;
		this.writeAccess = false;
		this.filePath = null;
		this.readFilePath = null;
		this.writeFilePath = null;
		this.identifier = null;
	},
	
	/**
	 * @type String
	 * @return The type name of this data type
	 */
	getTypeName: function() {
		return this.typeName;
	},
	
	/** 
	 * @deprecated
	 * @type String
	 * @return The type/display name of this data type
	 */
	getDisplayName: function() {
		return this.getTypeName();
	},
	
	/**
	 * @type String
	 * @return The file extension of this data type
	 */
	getFileExtension: function() {
		return this.fileExtension;
	},
	
	/**
	 * @type String
	 * @return The file path for this data type
	 */
	getFilePath: function() {
		return this.filePath;
	},
	
	/**
	 * @param filePath - the filepath for this datatype
	 */
	setFilePath: function(filePath) {
		this.filePath = filePath;
	},
	
	/**
     * @param readFilePath - the readFilePath for this datatype
     */
	getReadFilePath: function() {
	   return this.readFilePath;	
	},
	
	/**
	 * @type String
     * @return The read file path for this data type
     */
	setReadFilePath: function(readFilePath) {
		this.readFilePath = readFilePath;
	},
	
	/**
     * @param writeFilePath - the readFilePath for this datatype
     */
    getWriteFilePath: function() {
       return this.writeFilePath;    
    },
    
    /**
     * @type String
     * @return The write file path for this data type
     */
    setWriteFilePath: function(writeFilePath) {
        this.writeFilePath = writeFilePath;
    },
	
	/**
	 * @type String
	 * @return The identifier for this data type
	 */
	getIdentifier: function() {
		return this.identifier;
	},
	
	/**
	 * @param identifier- the identifier for this datatype
	 */
	setIdentifier: function(identifier) {
		this.identifier = identifier;
	},
	
	/**
	 * @param readAccess True == has read access
	 */
	setReadAccess: function(readAccess) {
		this.readAccess = readAccess;
	},
	
	/** Returns value indicating if the device supports read access for this file type
	 * @type Boolean
	 * @return supports read access for this file type
	 */
	hasReadAccess: function() {
		return this.readAccess;
	},
	
	/**
	 * @param {Boolean} has write access
	 */	
	setWriteAccess: function(writeAccess) {
		this.writeAccess = writeAccess;
	},
	
	/** return the value indicating if the device supports write access for this file type
	 * @type Boolean
	 * @return supports write access for this file type
	 */
	hasWriteAccess: function() {
		return this.writeAccess;
	}	
};
