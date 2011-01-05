if (Garmin == undefined) var Garmin = {};
/** Copyright � 2007 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview Garmin.DeviceControl A high-level JavaScript API which supports listener and callback functionality.
 * 
 * @author Diana Chow diana.chow[at]garmin.com, Carlo Latasa carlo.latasa@garmin.com
 * @version 1.0
 */
/** A controller object that can retrieve and send data to a Garmin 
 * device.<br><br>
 * @class Garmin.DeviceControl
 * 
 * The controller must be unlocked before anything can be done with it.  
 * Then you'll have to find a device before you can start to read data from
 * and write data to the device.<br><br>
 * 
 * We use the <a href="http://en.wikipedia.org/wiki/Observer_pattern">observer pattern</a> 
 * to handle the asynchronous nature of device communication.  You must register
 * your class as a listener to this Object and then implement methods that will 
 * get called on certain events.<br><br>
 * 
 * Events:<br><br>
 *     onStartFindDevices called when starting to search for devices.
 *       the object returned is {controller: this}<br><br>
 *
 *     onCancelFindDevices is called when the controller is told to cancel finding
 *         devices {controller: this}<br><br>
 *
 *     onFinishFindDevices called when the devices are found.
 *       the object returned is {controller: this}<br><br>
 *
 *     onException is called when an exception occurs in a method
 *         object passed back is {msg: exception}<br><br>
 *
 *	   onInteractionWithNoDevice is called when the device is lazy loaded, but finds no devices,
 * 			yet still attempts a read/write action {controller: this}<br><br>
 * 
 *     onStartReadFromDevice is called when the controller is about to start
 *         reading from the device {controller: this}<br><br>
 * 
 *     onFinishReadFromDevice is called when the controller is done reading 
 *         the device.  the read is either a success or failure, which is 
 *         communicated via json.  object passed back contains 
 *         {success:this.garminPlugin.GpsTransferSucceeded, controller: this} <br><br>
 *
 *     onWaitingReadFromDevice is called when the controller is waiting for input
 *         from the user about the device.  object passed back contains: 
 *         {message: this.garminPlugin.MessageBoxXml, controller: this}<br><br>
 *
 *     onProgressReadFromDevice is called when the controller is still reading information
 *         from the device.  in this case the message is a percent complete/ 
 *         {progress: this.getDeviceStatus(), controller: this}<br><br>
 *
 *     onCancelReadFromDevice is called when the controller is told to cancel reading
 *         from the device {controller: this}<br><br>
 *
 *     onFinishWriteToDevice is called when the controller is done writing to 
 *         the device.  the write is either a success or failure, which is 
 *         communicated via json.  object passed back contains 
 *         {success:this.garminPlugin.GpsTransferSucceeded, controller: this}<br><br>
 *
 *     onWaitingWriteToDevice is called when the controller is waiting for input
 *         from the user about the device.  object passed back contains: 
 *         {message: this.garminPlugin.MessageBoxXml, controller: this}<br><br>
 *
 *     onProgressWriteToDevice is called when the controller is still writing information
 *         to the device.  in this case the message is a percent complete/ 
 *         {progress: this.getDeviceStatus(), controller: this}<br><br>
 *
 *     onCancelWriteToDevice is called when the controller is told to cancel writing
 *         to the device {controller: this}<br><br>
 *
 * @constructor 
 *
 * requires Prototype
 * @requires BrowserDetect
 * @requires Garmin.DevicePlugin
 * @requires Garmin.Broadcaster
 * @requires Garmin.XmlConverter
 */
Garmin.DeviceControl = function(){}; //just here for jsdoc
Garmin.DeviceControl = Class.create();
Garmin.DeviceControl.prototype = {


	/////////////////////// Initialization Code ///////////////////////	

    /** Instantiates a Garmin.DeviceControl object, but does not unlock/activate plugin.
     */
	initialize: function() {
		
		this.pluginUnlocked = false;
		
		try {
			if (typeof(Garmin.DevicePlugin) == 'undefined') throw '';
		} catch(e) {
			throw new Error(Garmin.DeviceControl.MESSAGES.deviceControlMissing);
		};

    	// check that the browser is supported
     	if(!BrowserSupport.isBrowserSupported()) {
    	    var notSupported = new Error(Garmin.DeviceControl.MESSAGES.browserNotSupported);
    	    notSupported.name = "BrowserNotSupportedException";
    	    //console.debug("Control.validatePlugin throw BrowserNotSupportedException")
    	    throw notSupported;
        }
		
		// make sure the browser has the plugin installed
		if (!PluginDetect.detectGarminCommunicatorPlugin()) {
     	    var notInstalled = new Error(Garmin.DeviceControl.MESSAGES.pluginNotInstalled);
    	    notInstalled.name = "PluginNotInstalledException";
    	    throw notInstalled;			
		}
				
		// grab the plugin object on the page
		var pluginElement;
		if( window.ActiveXObject ) { // IE
			pluginElement = $("GarminActiveXControl");
		} else { // FireFox
			pluginElement = $("GarminNetscapePlugin");
		}
		
		// make sure the plugin object exists on the page
		if (pluginElement == null) {
			var error = new Error(Garmin.DeviceControl.MESSAGES.missingPluginTag);
			error.name = "HtmlTagNotFoundException";
			throw error;			
		}
		
		// instantiate a garmin plugin
		this.garminPlugin = new Garmin.DevicePlugin(pluginElement);
		 
		// validate the garmin plugin
		this.validatePlugin();
		
		// instantiate a broacaster
		this._broadcaster = new Garmin.Broadcaster();

		this.getDetailedDeviceData = true;
		this.devices = new Array();
		this.deviceNumber = null;
		this.numDevices = 0;

		this.gpsData = null;
		this.gpsDataType = null; //used by both read and write methods to track data context
		this.gpsDataString = "";
		this.gpsDataStringCompressed = "";  // Compresed version of gpsDataString.  gzip compressed and base 64 expanded.
		
		//this.wasMessageHack = false; //needed because garminPlugin.finishDownloadData returns true after out-of-memory error message is returned
	},

	/** Checks plugin validity: browser support, installation and required version.
	 * @private
     * @throws BrowserNotSupportedException
     * @throws PluginNotInstalledException
     * @throws OutOfDatePluginException
     */
    validatePlugin: function() {
		if (!this.isPluginInstalled()) {
     	    var notInstalled = new Error(Garmin.DeviceControl.MESSAGES.pluginNotInstalled);
    	    notInstalled.name = "PluginNotInstalledException";
    	    throw notInstalled;
        }
		if(this.garminPlugin.isPluginOutOfDate()) {
    	    var outOfDate = new Error(Garmin.DeviceControl.MESSAGES.outOfDatePlugin1+Garmin.DevicePlugin.REQUIRED_VERSION.toString()+Garmin.DeviceControl.MESSAGES.outOfDatePlugin2+this.getPluginVersionString());
    	    outOfDate.name = "OutOfDatePluginException";
    	    outOfDate.version = this.getPluginVersionString();
    	    throw outOfDate;
        }
    },
    
    /** Checks plugin for updates.  Throws an exception if the user's plugin version is
     * older than the one set by the API.
     * 
     * Plugin updates are not required so use this function with caution.
     * @see #setPluginLatestVersion
     */
    checkForUpdates: function() {
    	if(this.garminPlugin.isUpdateAvailable()) {
    		var notLatest = new Error(Garmin.DeviceControl.MESSAGES.updatePlugin1+Garmin.DevicePlugin.LATEST_VERSION.toString()+Garmin.DeviceControl.MESSAGES.updatePlugin2+this.getPluginVersionString());
    	    notLatest.name = "UpdatePluginException";
    	    notLatest.version = this.getPluginVersionString();
    	    throw notLatest;
    	}
    },
    
	/////////////////////// Device Handling Methods ///////////////////////	

	/** Finds any connected Garmin Devices.  
     * When it's done finding the devices, onFinishFindDevices is dispatched <br/>
     * <br/>
     * this.numDevices = the number of devices found<br/>
     * this.deviceNumber is the device that we'll use to communicate with<br/>
     * <br/>
     * Use this.getDevices() to get an array of the found devices and 
     * this.setDeviceNumber({Number}) to change the device. <br/>
     * <br/>
     * Minimum Plugin version 2.0.0.4
     * 
     * @see #getDevices
     * @see #setDeviceNumber
     */	
	findDevices: function() {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
        this.garminPlugin.startFindDevices();
	    this._broadcaster.dispatch("onStartFindDevices", {controller: this});
        setTimeout(function() { this._finishFindDevices() }.bind(this), 1000);
	},

	/** Cancels the current find devices interaction. <br/>
	 * <br/>
	 * Minimum Plugin version 2.0.0.4
     */	
	cancelFindDevices: function() {
		this.garminPlugin.cancelFindDevices();
    	this._broadcaster.dispatch("onCancelFindDevices", {controller: this});
	},

	/** Loads device data into devices array.
	 * 
	 * Minimum Plugin version 2.0.0.4
	 * 
	 * @private
     */	
	_finishFindDevices: function() {
    	if(this.garminPlugin.finishFindDevices()) {
            //console.debug("_finishFindDevices devXml="+this.garminPlugin.getDevicesXml())
            this.devices = Garmin.PluginUtils.parseDeviceXml(this.garminPlugin, this.getDetailedDeviceData);
            //console.debug("_finishFindDevices devXml="+this.garminPlugin.getDevicesXml())
            
            this.numDevices = this.devices.length;
       		this.deviceNumber = 0;
	        this._broadcaster.dispatch("onFinishFindDevices", {controller: this});
    	} else {
    		setTimeout(function() { this._finishFindDevices() }.bind(this), 500);
    	}
	},

	/** Sets the deviceNumber variable which determines which connected device to talk to.
     * @param {Number} deviceNumber The device number
     */	
	setDeviceNumber: function(deviceNumber) {
		this.deviceNumber = deviceNumber;
	},
	
	/**
	 * Get the device number of the connected device to communicate with (multiple devices may
	 * be connected simultaneously, but the plugin only transfers data with one at a time).
	 * @return the device number (assigned by the plugin) determining which connected device
	 * to talk to.
	 */
	getDeviceNumber: function() {
        return this.deviceNumber;
	},

	/** Get a list of the devices found
     * @type Array<Garmin.Device>
     */	
	getDevices: function() {
		return this.devices;
	},
	
	/** Returns the DeviceXML of the current device, as a string.
	 */
	getCurrentDeviceXml: function() {
		return this.garminPlugin.getDeviceDescriptionXml(this.deviceNumber);		
	},

	/////////////////////// Read Methods ///////////////////////
	
	/** Generic read method, supporting GPX and TCX Fitness types: Courses, Workouts, User Profiles, Activity Goals, 
	 * TCX activity directory, and TCX course directory reads. <br/>
	 * <br/>
	 * Fitness detail reading (one specific activity) is not supported by this read method, refer to 
	 * readDetailFromDevice for that. <br/>  
	 * 
	 * @param {String} fileType the filetype to read from device.  Possible values for fileType are located in Garmin.DeviceControl.FILE_TYPES--detail 
	 * types are not supported.
	 * @see #readDetailFromDevice, Garmin.DeviceControl#FILE_TYPES
	 * @throws InvalidTypeException, UnsupportedTransferTypeException
     */	
	readDataFromDevice: function(fileType) {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		// Make sure filetype passed in is a valid type
		if ( ! this._isAMember(fileType, [Garmin.DeviceControl.FILE_TYPES.gpx,
		                                  Garmin.DeviceControl.FILE_TYPES.gpxDetail, 
										  Garmin.DeviceControl.FILE_TYPES.gpxDir,
		                                  Garmin.DeviceControl.FILE_TYPES.tcx, 
		                                  Garmin.DeviceControl.FILE_TYPES.crs, 
										  Garmin.DeviceControl.FILE_TYPES.tcxDir, 
										  Garmin.DeviceControl.FILE_TYPES.crsDir, 
										  Garmin.DeviceControl.FILE_TYPES.wkt, 
										  Garmin.DeviceControl.FILE_TYPES.tcxProfile,
										  Garmin.DeviceControl.FILE_TYPES.goals,
										  Garmin.DeviceControl.FILE_TYPES.fit,
										  Garmin.DeviceControl.FILE_TYPES.fitDir
										  ])) {
			var error = new Error(Garmin.DeviceControl.MESSAGES.invalidFileType + fileType);
			error.name = "InvalidTypeException";
			throw error;
		} 
		// Make sure the device supports this type of data transfer for this type
		if( !this.checkDeviceReadSupport(fileType) ) {
		    var error = new Error(Garmin.DeviceControl.MESSAGES.unsupportedReadDataType + fileType);
    	    error.name = "UnsupportedDataTypeException";
			throw error;
		}
		this.gpsDataType = fileType;
		this.gpsData = null;		
		this.gpsDataString = null;
		this.idle = false;
		
		try {
        	this._broadcaster.dispatch("onStartReadFromDevice", {controller: this});
        	
        	switch(this.gpsDataType) {        		
				case Garmin.DeviceControl.FILE_TYPES.gpxDir:
        		case Garmin.DeviceControl.FILE_TYPES.gpx:
        			this.garminPlugin.startReadFromGps( this.deviceNumber );
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.tcx:
        		case Garmin.DeviceControl.FILE_TYPES.crs:
        		case Garmin.DeviceControl.FILE_TYPES.wkt:
        		case Garmin.DeviceControl.FILE_TYPES.goals:
        		case Garmin.DeviceControl.FILE_TYPES.tcxProfile:        		
        			this.garminPlugin.startReadFitnessData( this.deviceNumber, this.gpsDataType );
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
        			this.garminPlugin.startReadFitnessDirectory(this.deviceNumber, Garmin.DeviceControl.FILE_TYPES.tcx);
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.crsDir:
        			this.garminPlugin.startReadFitnessDirectory(this.deviceNumber, Garmin.DeviceControl.FILE_TYPES.crs);
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.fitDir:
                    this.garminPlugin.startReadFitDirectory(this.deviceNumber);
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.deviceXml:
        			this.gpsDataString = this.getCurrentDeviceXml();
        			break;
        	} 
		    this._progressRead();
		} catch(e) {
		    this._reportException(e);
		}
	},
	
	/** Generic detail read method, which reads a specific fitness activity from the device given an activity ID.  
	 * Supported detail types are history activities and course activities.  The resulting data read is available 
	 * in via gpsData as an XML DOM and gpsDataString as an XML string once the read successfully finishes. 
	 * Typically used after calling readDataFromDevice to read a fitness directory.<br/> 
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @param {String} fileType Filetype to be read from the device.  Supported values are 
	 * Garmin.DeviceControl.FILE_TYPES.tcxDetail and Garmin.DeviceControl.FILE_TYPES.crsDetail
	 * @param {String} dataId The ID of the data to be read from the device.  The format of these values depends 
	 * on the type of data being read (i.e. course data or history data). The CourseName element in the course schema 
	 * is used to identify courses, and the Id element is used to identify history activities.
	 * @see #readDataFromDevice, #readHistoryDetailFromFitnessDevice, #readCourseDetailFromFitnessDevice
	 * @throws InvalidTypeException, UnsupportedTransferTypeException
	 */
	readDetailFromDevice: function(fileType, dataId) {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		if ( ! this._isAMember(fileType, [Garmin.DeviceControl.FILE_TYPES.tcxDetail, Garmin.DeviceControl.FILE_TYPES.crsDetail])) {
			var error = new Error(Garmin.DeviceControl.MESSAGES.invalidFileType + fileType);
			error.name = "InvalidTypeException";
			throw error;
		}
		if( !this.checkDeviceReadSupport(fileType) ) {
			throw new Error(Garmin.DeviceControl.MESSAGES.unsupportedReadDataType + fileType);
		}
		
		this.gpsDataType = fileType;
		this.gpsData = null;		
		this.gpsDataString = null;
		this.idle = false;
		
		try {
        	this._broadcaster.dispatch("onStartReadFromDevice", {controller: this});
        	
        	switch(this.gpsDataType) {
        		case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
        			this.garminPlugin.startReadFitnessDetail(this.deviceNumber, Garmin.DeviceControl.FILE_TYPES.tcx, dataId);
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.crsDetail:
        			this.garminPlugin.startReadFitnessDetail(this.deviceNumber, Garmin.DeviceControl.FILE_TYPES.crs, dataId);
        			break;
        	} 
		    this._progressRead();
		} catch(e) {
		    this._reportException(e);
		}
	},
	
	/** Asynchronously reads GPX data from the connected device.  Only handles reading
     * from the device in this.deviceNumber. <br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString and this.gpsData
     * 
     * @see #readDataFromDevice
     */
	readFromDevice: function() {
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.gpx);
	},
	
	/** Asynchronously reads a single fitness history record from the connected device as TCX format.
	 * Only handles reading from the device in this.deviceNumber.<br/>
	 * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString
     * 
     * Minimum plugin version 2.2.0.2
     * 
     * @param {String} historyId The ID of the history record on the device.
     * 
     * @see #readDetailFromDevice
     */	
	readHistoryDetailFromFitnessDevice: function(historyId) {
		this.readDetailFromDevice(Garmin.DeviceControl.FILE_TYPES.tcx, historyId)
	},
	
	/** Asynchronously reads a single fitness course from the connected device as TCX format.
	 * Only handles reading from the device in this.deviceNumber. <br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.2.0.2
     * 
     * @param {String} courseId The name of the course on the device.
     * 
     * @see #readDetailFromDevice
     */			
	readCourseDetailFromFitnessDevice: function(courseId){
		this.readDetailFromDevice(Garmin.DeviceControl.FILE_TYPES.crs, courseId)
	},
	
	/** Asynchronously reads entire fitness history data (TCX) from the connected device.  
	 * Only handles reading from the device in this.deviceNumber. <br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.1.0.3
     * 
     * @see #readDataFromDevice
     */	
	readHistoryFromFitnessDevice: function() {	
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.tcx);
	},
	
	/** Asynchronously reads entire fitness course data (CRS) from the connected device.  
	 * Only handles reading from the device in this.deviceNumber<br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.2.0.1
     * 
     * @see #readDataFromDevice
     */	
	readCoursesFromFitnessDevice: function() {
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.crs);
	},
	
	/** Asynchronously reads fitness workout data (WKT) from the connected device.  
	 * Only handles reading from the device in this.deviceNumber<br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.2.0.1
     * 
     * @see #readDataFromDevice
     */	
	readWorkoutsFromFitnessDevice: function() {
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.wkt);
	},
	
	/** Asynchronously reads fitness profile data (TCX) from the connected device.
	 * Only handles reading from the device in this.deviceNumber<br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.2.0.1
     * 
     * @see #readDataFromDevice
     */	
	readUserProfileFromFitnessDevice: function() {
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.tcxProfile);
	},

	/** Asynchronously reads fitness goals data (TCX) from the connected device.
	 * Only handles reading from the device in this.deviceNumber<br/>
     * <br/>
     * When the data has been gathered, the onFinishedReadFromDevice is fired, and the
     * data is stored in this.gpsDataString<br/>
     * <br/>
     * Minimum plugin version 2.2.0.1
     * 
     * @see #readDataFromDevice
     */	
	readGoalsFromFitnessDevice: function() {
		this.readDataFromDevice(Garmin.DeviceControl.FILE_TYPES.goals);
	},
	
	
	
	/** Returns the GPS data that was last read as an XML DOM. <br/> 
	 * Pre-requisite - Read function was called successfully.  <br/> 
	 * <br/>
	 * Minimum plugin version 2.1.0.3
	 * 
	 * @return XML DOM of read GPS data
	 * @see #readDataFromDevice
	 * @see #readHistoryFromFitnessDevice
	 * @see #readHistoryDetailFromFitnessDevice
	 * @see #readCourseDetailFromFitnessDevice
	 */
	getGpsData: function() {
		
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		if( this.getReadCompletionState != Garmin.DeviceControl.FINISH_STATES.finished ) {
			throw new Error(Garmin.DeviceControl.MESSAGES.incompleteRead);
		}
		
		return this.gpsData;
	},
	
	/** Returns the GPS data that was last read as an XML string. <br/>  
	 * Pre-requisite - Read function was called successfully. <br/>
	 * <br/>
	 * Minimum plugin version 2.1.0.3
	 * 
	 * @return XML string of read GPS data
	 * @see #readDataFromDevice
	 * @see #readHistoryFromFitnessDevice
	 * @see #readHistoryDetailFromFitnessDevice
	 * @see #readCourseDetailFromFitnessDevice
	 */
	getGpsDataString: function() {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		if( this.getReadCompletionState != Garmin.DeviceControl.FINISH_STATES.finished ) {
			throw new Error(Garmin.DeviceControl.MESSAGES.incompleteRead);
		}
		
		return this.gpsDataString;
	},
	
	/** Returns the last read fitness data in compressed format.  A fitness read method must be called and the read must
	 * finish successfully before this function returns good data. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @return Compressed fitness XML data from the last successful read.  The data is gzp compressed and base64 expanded.
	 * @see #readDataFromDevice
	 * @see #readHistoryFromFitnessDevice
	 * @see #readHistoryDetailFromFitnessDevice
	 * @see #readCourseDetailFromFitnessDevice
	 */
	getCompressedFitnessData: function() {
		
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		if( this.getReadCompletionState != Garmin.DeviceControl.FINISH_STATES.finished ) {
			throw new Error(Garmin.DeviceControl.MESSAGES.incompleteRead);
		}

		try{
			this.garminPlugin.getTcdXmlz();
		}
		catch( aException ) {
 			this._reportException( aException );
		}
	},

	/** Returns the completion state of the current read.  This function can be used with
	 * GPX and TCX (fitness) reads.
	 * 
	 * @type Number
	 * @return {Number} The completion state of the current read.  The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
	 */	
	getReadCompletionState: function() {
		switch(this.gpsDataType) {
			case Garmin.DeviceControl.FILE_TYPES.gpxDir:
			case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
			case Garmin.DeviceControl.FILE_TYPES.gpx:
				return this.garminPlugin.finishReadFromGps();
			case Garmin.DeviceControl.FILE_TYPES.tcx:
			case Garmin.DeviceControl.FILE_TYPES.crs:
			case Garmin.DeviceControl.FILE_TYPES.wkt:
			case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
			case Garmin.DeviceControl.FILE_TYPES.goals:
				return this.garminPlugin.finishReadFitnessData();
			case Garmin.DeviceControl.FILE_TYPES.tcxDir:
			case Garmin.DeviceControl.FILE_TYPES.crsDir:
				return this.garminPlugin.finishReadFitnessDirectory();
			case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
			case Garmin.DeviceControl.FILE_TYPES.crsDetail:
				return this.garminPlugin.finishReadFitnessDetail();
			case Garmin.DeviceControl.FILE_TYPES.fitDir:
                return this.garminPlugin.finishReadFitDirectory();
		}
	},
	
	/** Internal read dispatching and polling delay.
	 * @private
     */	
	_progressRead: function() {
		this._broadcaster.dispatch("onProgressReadFromDevice", {progress: this.getDeviceStatus(), controller: this});
        setTimeout(function() { this._finishReadFromDevice() }.bind(this), 200); //200		 
	},
	
	/** Internal read state logic. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4 for GPX and TCX history read.<br/>
	 * Minimum plugin version 2.2.0.2 for directory and detail read.<br/>
	 * Minimum plugin version 2.2.0.2 for compressed file get.
	 * 
	 * @private
     */	
	_finishReadFromDevice: function() {
		var completionState = this.getReadCompletionState();
		
		//console.debug("control._finishReadFromDevice this.gpsDataType="+this.gpsDataType+" completionState="+completionState)
        try {
        	
			if( completionState == Garmin.DeviceControl.FINISH_STATES.finished ) {
	        	switch( this.gpsDataType ) {
					case Garmin.DeviceControl.FILE_TYPES.gpxDir:
	        		case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
	        		case Garmin.DeviceControl.FILE_TYPES.gpx:
	        			if (this.garminPlugin.gpsTransferSucceeded()) {
		        			this.gpsDataString = this.garminPlugin.getGpsXml();
							this.gpsData = Garmin.XmlConverter.toDocument(this.gpsDataString);
							this._broadcaster.dispatch("onFinishReadFromDevice", {success: this.garminPlugin.gpsTransferSucceeded(), controller: this});	
	        			}
						break;
					case Garmin.DeviceControl.FILE_TYPES.tcx:
					case Garmin.DeviceControl.FILE_TYPES.crs:
					case Garmin.DeviceControl.FILE_TYPES.tcxDir:
					case Garmin.DeviceControl.FILE_TYPES.crsDir:
					case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
					case Garmin.DeviceControl.FILE_TYPES.crsDetail:
					case Garmin.DeviceControl.FILE_TYPES.wkt:
					case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
					case Garmin.DeviceControl.FILE_TYPES.goals:
						if (this.garminPlugin.fitnessTransferSucceeded()) {
							this.gpsDataString = this.garminPlugin.getTcdXml();
							this.gpsDataStringCompressed = this.garminPlugin.getTcdXmlz();
							
							this.gpsData = Garmin.XmlConverter.toDocument(this.gpsDataString);
							this._broadcaster.dispatch("onFinishReadFromDevice", {success: this.garminPlugin.fitnessTransferSucceeded(), controller: this});										
						}
						break;
					case Garmin.DeviceControl.FILE_TYPES.fitDir:
                        // TODO is there a fitness transfer succeeded with fit?
//					    if(this.garminPlugin.fitnessTransferSucceeded()) {
					        this.gpsDataString = this.garminPlugin.getDirectoryXml();
//							this.gpsDataStringCompressed = this.garminPlugin.getTcdXmlz();
							this.gpsData = Garmin.XmlConverter.toDocument(this.gpsDataString);
							this._broadcaster.dispatch("onFinishReadFromDevice", {success: this.garminPlugin.fitnessTransferSucceeded(), controller: this});
//					    }
                        break;
	        	}
			} else if( completionState == Garmin.DeviceControl.FINISH_STATES.messageWaiting ) {
				var msg = this._messageWaiting();
				this._broadcaster.dispatch("onWaitingReadFromDevice", {message: msg, controller: this});
			} else {
	    	    this._progressRead();
			}
		} catch( aException ) {
 			this._reportException( aException );
		}
    },

	/** User canceled the read. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
     */	
	cancelReadFromDevice: function() {
		if (this.gpsDataType == Garmin.DeviceControl.FILE_TYPES.gpx) {
			this.garminPlugin.cancelReadFromGps();
		} else {
			this.garminPlugin.cancelReadFitnessData();
		}
    	this._broadcaster.dispatch("onCancelReadFromDevice", {controller: this});
	},
	
	
	/** Return the specified file as a UU-Encoded string
     * <br/>
     * Minimum version 2.6.3.1
     * 
     * If the file is known to be compressed, compressed should be
     * set to false. Otherwise, set compressed to true to retrieve a
     * gzipped and uuencoded file.
     * 
     * @param relativeFilePath {String} path relative to the Garmin folder on the device
     */
     getBinaryFile: function(deviceNumber, relativeFilePath) {
        if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if(this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
        // Attempt to detect Fit file
        if(relativeFilePath.capitalize().endsWith(".fit")) {
            // Capitalize makes all but first letters lowercase. I can't believe prototype doesn't have a lowercase method. :(
            this.gpsDataType = Garmin.DeviceControl.FILE_TYPES.fit;
        } else {
    		this.gpsDataType = Garmin.DeviceControl.FILE_TYPES.binary;
        }
		var success;
		try {
		    this.gpsDataString = this.garminPlugin.getBinaryFile(deviceNumber, relativeFilePath, false);
		    this.gpsDataStringCompressed = this.garminPlugin.getBinaryFile(deviceNumber, relativeFilePath, true);
//		    this.gpsData = this.garminPlugin.getBinaryFile(deviceNumber, relativeFilePath, compressed);
		    success = true;
	    } catch(e) {
	        success = false;
			this._reportException(e);
	    }
	    
	    this._broadcaster.dispatch("onFinishReadFromDevice", {success: success, controller: this});
        return this.gpsData;
     },

	/////////////////////// Web Drop Methods (Write) ///////////////////////
	
    /** Writes an address to the currently selected device.
     * 
     * @param {String} address The address to be written to the device. This doesn't check validity
     */	
	writeAddressToDevice: function(address) {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if (!this.geocoder) {
			this.geocoder = new Garmin.Geocode();
			this.geocoder.register(this);
		}
		this.geocoder.findLatLng(address);
	},

	/** Handles call-back from geocoder and forwards call to onException on registered listeners.
	 * @private
     * @param {Error} json error wrapped in JSON 'msg' object.
     */
	onException: function(json) {
		this._reportException(json.msg);
	},
	
	/** Handles call-back from geocoder and forwards call to writeToDevice.
	 * Registered listeners will recieve an onFinishedFindLatLon call before writeToDevice is invoked.
	 * Listeners can change the 'fileName' if they choose avoiding overwritting old waypoints on
	 * some devices.
	 * @private
     * @param {Object} json waypoint, fileName and controller in JSON wrapper.
     */
	onFinishedFindLatLon: function(json) {
		json.fileName = "address.gpx";
		json.controller = this;
		this._broadcaster.dispatch("onFinishedFindLatLon", json);
   		var factory = new Garmin.GpsDataFactory();
		var gpxStr = factory.produceGpxString(null, [json.waypoint]);
		this.writeToDevice(gpxStr, json.fileName);
	},

	/////////////////////// More Write Methods ///////////////////////	
    /**
     * Generic write method for GPX and TCX file formats.  For binary write, use {@link downloadToDevice}.
     * 
     * @param {String} dataType - the datatype to write to device.  Possible values are located in {@link #Garmin.DeviceControl.FILE_TYPES}
     * @param {String} dataString - the datastring to write to device.  Should be in the format of the dataType value.
     * @param {String} fileName - the filename to write the data to on the device. File extension is not necessary, 
     * but is suggested for device compatibility.  This parameter is ignored when the dataType value is FitnessActivityGoals (see {@link #writeGoalsToFitnessDevice}).
     * @see #writeToDevice, #writeFitnessToDevice
     * @throws InvalidTypeException, UnsupportedTransferTypeException
     */ 
    writeDataToDevice: function(dataType, dataString, fileName) {
        if (!this.isUnlocked()) {
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
        }
        
		if (this.numDevices == 0) {
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
        }

		this.gpsDataType = dataType;
        
		if (!this.checkDeviceWriteSupport(this.gpsDataType)) {
			throw new Error(Garmin.DeviceControl.MESSAGES.unsupportedWriteDataType + this.gpsDataType);
		}
		
		try {
        	this._broadcaster.dispatch("onStartWriteToDevice", {controller: this});
            
        	switch(this.gpsDataType) {
            	case Garmin.DeviceControl.FILE_TYPES.gpx:
        			this.garminPlugin.startWriteToGps(dataString, fileName, this.deviceNumber);
        			break;
        		case Garmin.DeviceControl.FILE_TYPES.crs:
        		case Garmin.DeviceControl.FILE_TYPES.wkt:
        		case Garmin.DeviceControl.FILE_TYPES.goals:
        		case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
        		case Garmin.DeviceControl.FILE_TYPES.nlf:                
        			this.garminPlugin.startWriteFitnessData(dataString, this.deviceNumber, fileName, this.gpsDataType);
        			break;
        		default:
					throw new Error(Garmin.DeviceControl.MESSAGES.unsupportedWriteDataType + this.gpsDataType);
        	}
		    this._progressWrite();
	    } catch(e) {
			this._reportException(e);
	   	}
    },
    
    /** Writes the given GPX XML string to the device selected in this.deviceNumber. <br/>
     * <br/>
     * Minimum plugin version 2.0.0.4
     * 
     * @param gpxString XML to be written to the device. This doesn't check validity.
     * @param fileName The filename to write data to.  Validity is not checked here.
     */	
	writeToDevice: function(gpxString, fileName) {
        this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.gpx, gpxString, fileName);	    
	},

	/** DEPRECATED - See {@link #writeCoursesToFitnessDevice}<br/> 
	 * <br/>
	 * Writes fitness course data (TCX) to the device selected in this.deviceNumber. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
     * @param tcxString {String} TCX Course XML string to be written to the device. This doesn't check validity.
     * @param fileName {String} filename to write data to on the device.  Validity is not checked here.
     */	
	writeFitnessToDevice: function(tcxString, fileName) {
		this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.crs, tcxString, fileName);
	},

	/** Writes fitness course data (TCX) to the device selected in this.deviceNumber. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
     * @param tcxString {String} TCX Course XML string to be written to the device. This doesn't check validity.
     * @param fileName {String} filename to write data to on the device.  Validity is not checked here.
     */	
	writeCoursesToFitnessDevice: function(tcxString, fileName) {
		this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.crs, tcxString, fileName);
	},

	/** Writes fitness goals data (TCX) string to the device selected in this.deviceNumber. All fitness goals
	 * are written to the filename 'ActivityGoals.TCX' in the device's goals directory, in order for the device
	 * to recognize the file.<br/> 
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
     * @param tcxString {String} ActivityGoals TCX string to be written to the device. This doesn't check validity.
     */	
	writeGoalsToFitnessDevice: function(tcxString) {
		this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.goals, tcxString, '');
	},
	
	/** Writes fitness workouts data (XML) string to the device selected in this.deviceNumber. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
     * @param tcxString XML (workouts) string to be written to the device. This doesn't check validity.
     * @param fileName String of filename to write it to on the device.  Validity is not checked here.
     */	
	writeWorkoutsToFitnessDevice: function(tcxString, fileName) {
		this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.wkt, tcxString, fileName);
	},
	
	/** Writes fitness user profile data (TCX) string to the device selected in this.deviceNumber. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
     * @param tcxString XML (user profile) string to be written to the device. This doesn't check validity.
     * @param fileName String of filename to write it to on the device.  Validity is not checked here.
     */	
	writeUserProfileToFitnessDevice: function(tcxString, fileName) {
		this.writeDataToDevice(Garmin.DeviceControl.FILE_TYPES.tcxProfile, tcxString, fileName);
	},
	
	/** Downloads and writes binary data asynchronously to device. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
     *
     * @param xmlDownloadDescription {String} xml string containing information about the files to be downloaded onto the device.
     * @param fileName {String} this parameter is ignored!  We will remove this param from the API in a future compatibility release.
     */	
	downloadToDevice: function(xmlDownloadDescription) {
		if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if(this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		this.gpsDataType = Garmin.DeviceControl.FILE_TYPES.binary;
		try {
		    this.garminPlugin.startDownloadData(xmlDownloadDescription, this.deviceNumber );
		    this._progressWrite();
	    } catch(e) {
			this._reportException(e);
	    }
	},
	
	/** Internal dispatch and polling delay.
	 * @private
     */	
	_progressWrite: function() {
		//console.debug("control._progressWrite gpsDataType="+this.gpsDataType)		
    	this._broadcaster.dispatch("onProgressWriteToDevice", {progress: this.getDeviceStatus(), controller: this});
        setTimeout(function() { this._finishWriteToDevice() }.bind(this), 200);
	},
	
	/** Internal write lifecycle handling.
	 * @private
     */	
	_finishWriteToDevice: function() {
        try {
			var completionState;
			var success;
			
			switch( this.gpsDataType ) {
				
				case Garmin.DeviceControl.FILE_TYPES.gpx : 
					completionState = this.garminPlugin.finishWriteToGps();
					success = this.garminPlugin.gpsTransferSucceeded();
					break;
				case Garmin.DeviceControl.FILE_TYPES.crs :
				case Garmin.DeviceControl.FILE_TYPES.goals :
				case Garmin.DeviceControl.FILE_TYPES.wkt :
				case Garmin.DeviceControl.FILE_TYPES.tcxProfile :
				case Garmin.DeviceControl.FILE_TYPES.nlf :
					completionState = this.garminPlugin.finishWriteFitnessData();
					success = this.garminPlugin.fitnessTransferSucceeded();
					break;
				case Garmin.DeviceControl.FILE_TYPES.gpi :
				case Garmin.DeviceControl.FILE_TYPES.fitCourse :
				case Garmin.DeviceControl.FILE_TYPES.fitSettings :
				case Garmin.DeviceControl.FILE_TYPES.fitSport :
				case Garmin.DeviceControl.FILE_TYPES.binary :
					completionState = this.garminPlugin.finishDownloadData();
					success = this.garminPlugin.downloadDataSucceeded();
					break;				
				case Garmin.DeviceControl.FILE_TYPES.firmware :
					completionState = this.garminPlugin.finishUnitSoftwareUpdate();
					success = this.garminPlugin.downloadDataSucceeded();
					break;				
				default:
					throw new Error(Garmin.DeviceControl.MESSAGES.unsupportedWriteDataType + this.gpsDataType);
			}
			
			if( completionState == Garmin.DeviceControl.FINISH_STATES.finished ) {
				this._broadcaster.dispatch("onFinishWriteToDevice", {success: success, controller: this});											
			} else if( completionState == Garmin.DeviceControl.FINISH_STATES.messageWaiting ) {
				var msg = this._messageWaiting();
				this._broadcaster.dispatch("onWaitingWriteToDevice", {message: msg, controller: this});
			} else {
	    	     this._progressWrite();
			}
		} catch( aException ) {
 			this._reportException( aException );
		}
	},

	/** Cancels the current write transfer to the device. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4<br/>
     * Minimum plugin version 2.2.0.1 for writes of GPX to SD Card
     */	
	cancelWriteToDevice: function() {
		switch( this.gpsDataType) {
			case Garmin.DeviceControl.FILE_TYPES.gpx:
				this.garminPlugin.cancelWriteToGps();
				break;
			case Garmin.DeviceControl.FILE_TYPES.gpi:
			case Garmin.DeviceControl.FILE_TYPES.binary:
				this.garminPlugin.cancelDownloadData();
				break;
			case Garmin.DeviceControl.FILE_TYPES.firmware:
				this.garminPlugin.cancelUnitSoftwareUpdate();
				break;
			case Garmin.DeviceControl.FILE_TYPES.crs:
			case Garmin.DeviceControl.FILE_TYPES.goals:
			case Garmin.DeviceControl.FILE_TYPES.wkt:
			case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
			case Garmin.DeviceControl.FILE_TYPES.nlf:
				this.garminPlugin.cancelWriteFitnessData();
				break;
		}
		this._broadcaster.dispatch("onCancelWriteToDevice", {controller: this});
	},

    /**
	 * Determine the amount of space available on a mass storage mode device (the
	 * currently selected device according to this.deviceNumber). 
	 * <br/> 
	 * Minimum Plugin version 2.5.1
	 * 
	 * @param {String} relativeFilePath - if a file is being replaced, set to relative path on device, otherwise set to empty string.
	 * @return -1 for non-mass storage mode devices.
	 * @see downloadToDevice 
	 */
	bytesAvailable: function(relativeFilePath) {
	    return this.garminPlugin.bytesAvailable(this.getDeviceNumber(), relativeFilePath);
	},
	
	/** Download and install a list of unit software updates.  Start the asynchronous 
     * StartUnitSoftwareUpdate operation.
     * 
     * Check for completion with the FinishUnitSoftwareUpdate() method.  After
     * completion check the DownloadDataSucceeded property to make sure that all of the downloads 
     * were successfully placed on the device. 
     * 
     * See the Schema UnitSoftwareUpdatev3.xsd for the format of the UpdateResponsesXml description
     *
     * @see Garmin.DeviceControl.cancelWriteToDevice
     * @see Garmin.DevicePlugin.downloadDataSucceeded
     * @see Garmin.DevicePlugin._finishWriteToDevice
     * @version plugin v2.6.2.0
     */
    downloadFirmwareToDevice: function(updateResponsesXml) {
        if (!this.isUnlocked())
			throw new Error(Garmin.DeviceControl.MESSAGES.pluginNotUnlocked);
		if(this.numDevices == 0)
			throw new Error(Garmin.DeviceControl.MESSAGES.noDevicesConnected);
		this.gpsDataType = Garmin.DeviceControl.FILE_TYPES.firmware;
		try {
            this.garminPlugin.startUnitSoftwareUpdate(updateResponsesXml, this.deviceNumber);
		    this._progressWrite();
	    } catch(e) {
			this._reportException(e);
	    }
    },

	/////////////////////// Support Methods ///////////////////////	


	/** Unlocks the GpsControl object to be used at the given web address. <br/>
     * <br/>
     * Minimum Plugin version 2.0.0.4
     * 
     * @param {Array} pathKeyPairsArray baseURL and key pairs.  
     * @type Boolean 
     * @return True if the plug-in was unlocked successfully
     */
	unlock: function(pathKeyPairsArray) {
		this.pluginUnlocked = this.garminPlugin.unlock(pathKeyPairsArray);
		return this.pluginUnlocked;
	},

	/** Register to be an event listener.  An object that is registered will be dispatched
     * a method if they have a function with the same dispatch name.  So if you register a
     * listener with an onFinishFindDevices, and the onFinishFindDevices message is called, you'll
     * get that message.  See class comments for event types
     *
     * @param {Object} listener Object that will listen for events coming from this object 
     * @see {Garmin.Broadcaster}
     */	
	register: function(listener) {
        this._broadcaster.register(listener);
	},

	/** True if plugin has been successfully created and unlocked.
	 * @type Boolean
	 */
	 isUnlocked: function() {
	 	return this.pluginUnlocked;
	 },
	 
    /** Responds to a message box on the device.
     * 
     * Minimum version 2.0.0.4
     * 
     * @param {Number} response should be an int which corresponds to a button value from this.garminPlugin.MessageBoxXml
     */
    // TODO: this method only works with writes - should it work with reads?
    respondToMessageBox: function(response) {
        this.garminPlugin.respondToMessageBox(response ? 1 : 2);
        this._progressWrite();
    },

	/** Called when device generates a message.
	 * This occurs when completionState == Garmin.DeviceControl.FINISH_STATES.messageWaiting.
	 * @private
     */	
	_messageWaiting: function() {
		var messageDoc = Garmin.XmlConverter.toDocument(this.garminPlugin.getMessageBoxXml());
		//var type = messageDoc.getElementsByTagName("Icon")[0].childNodes[0].nodeValue;
		var text = messageDoc.getElementsByTagName("Text")[0].childNodes[0].nodeValue;
		
		var message = new Garmin.MessageBox("Question",text);
		
		var buttonNodes = messageDoc.getElementsByTagName("Button");
		for(var i=0; i<buttonNodes.length; i++) {
			var caption = buttonNodes[i].getAttribute("Caption");
			var value = buttonNodes[i].getAttribute("Value");
			message.addButton(caption, value);
		}
		return message;
	},

	/** Get the status/progress of the current state or transfer
     * @type Garmin.TransferProgress
     */	
	getDeviceStatus: function() {
		var aProgressXml = this.garminPlugin.getProgressXml();
		var theProgressDoc = Garmin.XmlConverter.toDocument(aProgressXml);
		
		var title = "";
		if(theProgressDoc.getElementsByTagName("Title").length > 0) {
			title = theProgressDoc.getElementsByTagName("Title")[0].childNodes[0].nodeValue;
		}
		
		var progress = new Garmin.TransferProgress(title);

		var textNodes = theProgressDoc.getElementsByTagName("Text");
		for( var i=0; i < textNodes.length; i++ ) {
			if(textNodes[i].childNodes.length > 0) {
				var text = textNodes[i].childNodes[0].nodeValue;
				if(text != "") progress.addText(text);
			}
		}
		
		var percentageNode = theProgressDoc.getElementsByTagName("ProgressBar")[0];
		if(percentageNode != undefined) {
			if(percentageNode.getAttribute("Type") == "Percentage") {
				progress.setPercentage(percentageNode.getAttribute("Value"));
			} else if (percentageNode.getAttribute("Type") == "Indefinite") {
				progress.setPercentage(100);			
			}
		}

		return progress;
	},
		
	/**
	 * @private
	 */
	_isAMember: function(element, array) {
		return array.any( function(str){ return str==element; } );
	},
	
	/** Gets the version number for the plugin the user has currently installed.
     * @type Array 
     * @return An array of the format [versionMajor, versionMinor, buildMajor, buildMinor].
     * @see #getPluginVersionString
     */	
	getPluginVersion: function() {
		
    	return this.garminPlugin.getPluginVersion();
	},

	/** Gets a string of the version number for the plugin the user has currently installed.
     * @type String 
     * @return A string of the format "versionMajor.versionMinor.buildMajor.buildMinor", i.e. "2.0.0.4"
     * @see #getPluginVersion
     */	
	getPluginVersionString: function() {
		return this.garminPlugin.getPluginVersionString();
	},
	
	/** Sets the required version number for the plugin for the application.
	 * @param reqVersionArray {Array} The required version to set to.  In the format [versionMajor, versionMinor, buildMajor, buildMinor]
	 * 			i.e. [2,2,0,1]
	 */
	setPluginRequiredVersion: function(reqVersionArray) {
		if( reqVersionArray != null ) {
			this.garminPlugin.setPluginRequiredVersion(reqVersionArray);
		}
	},
	
	/** Sets the latest plugin version number.  This represents the latest version available for download at Garmin.
	 * We will attempt to keep the default value of this up to date with each API release, but this is not guaranteed,
	 * so set this to be safe or if you don't want to upgrade to the latest API.
	 * 
	 * @param reqVersionArray {Array} The latest version to set to.  In the format [versionMajor, versionMinor, buildMajor, buildMinor]
	 * 			i.e. [2,2,0,1]
	 */
	setPluginLatestVersion: function(reqVersionArray) {
		if( reqVersionArray != null ) {
			this.garminPlugin.setPluginLatestVersion(reqVersionArray);
		}
	},
	
	/** Determines if the plugin is initialized
     * @type Boolean
     */	
	isPluginInitialized: function() {
		return (this.garminPlugin != null);
	},

	/** Determines if the plugin is installed on the user's machine
     * @type Boolean
     */	
	isPluginInstalled: function() {
		return (this.garminPlugin.getVersionXml() != undefined);
	},

	/** Internal exception handling for asynchronous calls.
	 * @private
      */	
	_reportException: function(exception) {
		this._broadcaster.dispatch("onException", {msg: exception, controller: this});
	},
	
	/** Number of devices detected by plugin.
	 * @type Number
}    */	
	getDevicesCount: function() {
	    return this.numDevices;
	},
	
	/** Checks if the device lists the given datatype as a supported readable type.
	 * Plugin version affects the results of this function.  The latest plugin version is encouraged.
	 * 
	 * Internal file type support (such as directory types) is detected based on base
	 * type. i.e. tcxDir -> tcx, fitDir -> fit
	 */
	checkDeviceReadSupport: function( datatype ) {
		
        // Do the plugin version check early for fit directory reading
		if( datatype == Garmin.DeviceControl.FILE_TYPES.fitDir) {
		    if( this.garminPlugin.getSupportsFitDirectoryRead() == false) {
    	        // Yeah, breaking the 1 return rule... This is still cleaner than all the other options
    	        // and at least eliminates confusion between other types.
		        return false;
		    }
        } 
        
		var isDatatypeSupported;

		// The selected device
		var device = this._getDeviceByNumber(this.deviceNumber);
		var baseDatatype;

		// Internal types use base type for the support check.
		switch(datatype) {
			case Garmin.DeviceControl.FILE_TYPES.gpxDir:
			case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
			     baseDatatype = Garmin.DeviceControl.FILE_TYPES.gpx;
			     break;			
            case Garmin.DeviceControl.FILE_TYPES.tcxDir:
            case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
    		    baseDatatype = Garmin.DeviceControl.FILE_TYPES.tcx; 
    		    break;
            case Garmin.DeviceControl.FILE_TYPES.crsDir:
            case Garmin.DeviceControl.FILE_TYPES.crsDetail:
                baseDatatype = Garmin.DeviceControl.FILE_TYPES.crs;
                break;
            case Garmin.DeviceControl.FILE_TYPES.fitDir:
            case Garmin.DeviceControl.FILE_TYPES.fitFile:
                baseDatatype = Garmin.DeviceControl.FILE_TYPES.fit;
                break;
            default:     
                baseDatatype = datatype;
		}
		
		// Every device has a device xml and firmware
		if(baseDatatype == Garmin.DeviceControl.FILE_TYPES.deviceXml 
		|| baseDatatype == Garmin.DeviceControl.FILE_TYPES.firmware) {
		    isDatatypeSupported = true;
		} else {
            isDatatypeSupported = device.supportDeviceDataTypeRead(baseDatatype);
		}

		return isDatatypeSupported;
	},
	
	/** Checks if the device lists the given datatype as a supported writeable type.
	 * Plugin version affects the results of this function.  The latest plugin version is encouraged.
	 * 
	 * Internal file types (such as directory types) are NOT detected as supported write types.
	 */
	checkDeviceWriteSupport: function(datatype) {
	    var isDatatypeSupported = false;

		// The selected device
		var device = this._getDeviceByNumber(this.deviceNumber);
		
		// Don't include types that aren't in the Device XML
		if ( datatype == Garmin.DeviceControl.FILE_TYPES.binary 
		  || datatype == Garmin.DeviceControl.FILE_TYPES.gpi) {
		    isDatatypeSupported = true;
		} else {
            isDatatypeSupported = device.supportDeviceDataTypeWrite(datatype);
		}
		
		return isDatatypeSupported;
	},
	
	/** Retrieve a device from the list of found devices by device number. 
	 * @return Garmin.Device
	 */
	_getDeviceByNumber: function(deviceNum) {
		for( var index = 0; index < this.devices.length; index++) {
			if( this.devices[index].getNumber() == deviceNum){
				return this.devices[index];
			}
		}		
	},
	
	/** String representation of instance.
	 * @type String
     */	
	toString: function() {
	    return "Garmin Javascript GPS Controller managing " + this.numDevices + " device(s)";
	}
};

/** Dedicated browser support singleton.
 */
var BrowserSupport = {
    /** Determines if the users browser is currently supported by the plugin
     * @type Boolean
     */	 
	isBrowserSupported: function() {
		//console.debug("Display.isBrowserSupported BrowserDetect.OS="+BrowserDetect.OS+", BrowserDetect.browser="+BrowserDetect.browser)
		// TODO Extract strings to constants
		// TODO Move this out to plugin layer? 
		return ( (BrowserDetect.OS == "Windows" && 
					(BrowserDetect.browser == "Firefox" 
					|| BrowserDetect.browser == "Mozilla" 
					|| BrowserDetect.browser == "Explorer"
					|| BrowserDetect.browser == "Safari"))
				|| (BrowserDetect.OS == "Mac" && 
					(BrowserDetect.browser == "Firefox" 
					|| BrowserDetect.browser == "Safari")) );
	}
};

/** Constants defining possible errors messages for various errors on the page
 */
Garmin.DeviceControl.MESSAGES = {
	deviceControlMissing: "Garmin.DeviceControl depends on the Garmin.DevicePlugin framework.",
	missingPluginTag: "Plug-In HTML tag not found.",
	browserNotSupported: "Your browser is not supported to use the Garmin Communicator Plug-In.",
	pluginNotInstalled: "Garmin Communicator Plugin NOT detected.",
	outOfDatePlugin1: "Your version of the Garmin Communicator Plug-In is out of date.<br/>Required: ",
	outOfDatePlugin2: "Current: ",
	updatePlugin1: "Your version of the Garmin Communicator Plug-In is not the latest version. Latest version: ",
	updatePlugin2: ", current: ",
	pluginNotUnlocked: "Garmin Plugin has not been unlocked",
	noDevicesConnected: "No device connected, can't communicate with device.",
	invalidFileType: "Cannot process the device file type: ",
	incompleteRead: "Incomplete read, cannot get compressed format.",
	unsupportedReadDataType: "Your device does not support reading of the type: ",
	unsupportedWriteDataType: "Your device does not support writing of the type: "
};

/** Constants defining possible states when you poll the finishActions
 */
Garmin.DeviceControl.FINISH_STATES = {
	idle: 0,
	working: 1,
	messageWaiting: 2,
	finished: 3	
};

/** Constants defining possible file types associated with read and write methods.  File types can
 * be accessed in a static way, like so:<br/>
 * <br/>
 * Garmin.DeviceControl.FILE_TYPES.gpx<br/>
 * <br/>
 * NOTE: 'gpi' is being deprecated--please use 'binary' instead for gpi and other binary data. 
 */
Garmin.DeviceControl.FILE_TYPES = {
	gpx:               "GPSData",
	tcx:               "FitnessHistory",
	gpi:               "gpi", //deprecated, use binary instead
	crs:               "FitnessCourses",
	wkt:               "FitnessWorkouts",
	goals:             "FitnessActivityGoals",
	tcxProfile:        "FitnessUserProfile",
	binary:            "BinaryData", // Not in Device XML, so writing this type is "supported" for all devices. For FIT data, use fitFile.
	voices:            "Voices",
	nlf:               "FitnessNewLeaf",
	fit:               "FITBinary",
	fitCourse:         "FIT_TYPE_6",
	fitSettings:       "FIT_TYPE_2",
	fitSport:          "FIT_TYPE_3",
	
	// The following types are internal types used by the API only and cannot be found in the Device XML.
	// NOTE: When adding or removing types to this internal list, modify checkDeviceReadSupport() accordingly.
	tcxDir: 		   "FitnessHistoryDirectory",
	crsDir: 		   "FitnessCoursesDirectory",
	gpxDir: 		   "GPSDataDirectory",
	tcxDetail:         "FitnessHistoryDetail",
	crsDetail: 		   "FitnessCoursesDetail",
	gpxDetail:         "GPSDataDetail",
	deviceXml: 	       "DeviceXml",
	fitDir:     	   "FitDirectory",
	fitFile:    	   "FitFile",
	firmware:          "Firmware"
};

/** Constants defining the strings used by the Device.xml to indicate 
 * transfer direction of each file type
 */
Garmin.DeviceControl.TRANSFER_DIRECTIONS = {
	read:              "OutputFromUnit",
	write:             "InputToUnit",
	both:              "InputOutput"
};

/** Encapsulates the data provided by the device for the current process' progress.
 * Use this to relay progress information to the user.
 * @class Garmin.TransferProgress
 * @constructor 
 */
Garmin.TransferProgress = Class.create();
Garmin.TransferProgress.prototype = {
	initialize: function(title) {
		this.title = title;
		this.text = new Array();
		this.percentage = null;
	},
	
	addText: function(textString) {
		this.text.push(textString);
	},

    /** Get all the text entries for the transfer
     * @type Array
     */	 
	getText: function() {
		return this.text;
	},

    /** Get the title for the transfer
     * @type String
     */	 
	getTitle: function() {
		return this.title;
	},
	
	setPercentage: function(percentage) {
		this.percentage = percentage;
	},

    /** Get the completed percentage value for the transfer
     * @type Number
     */
	getPercentage: function() {
		return this.percentage;
	},

    /** String representation of instance.
     * @type String
     */	 	
	toString: function() {
		var progressString = "";
		if(this.getTitle() != null) {
			progressString += this.getTitle();
		}
		if(this.getPercentage() != null) {
			progressString += ": " + this.getPercentage() + "%";
		}
		return progressString;
	}
};


/** Encapsulates the data to display a message box to the user when the plug-in is waiting for feedback
 * @class Garmin.MessageBox
 * @constructor 
 */
Garmin.MessageBox = Class.create();
Garmin.MessageBox.prototype = {
	initialize: function(type, text) {
		this.type = type;
		this.text = text;
		this.buttons = new Array();
	},

    /** Get the type of the message box
     * @type String
     */	 
	getType: function() {
		return this.type;
	},

    /** Get the text entry for the message box
     * @type String
     */	 
	getText: function() {
		return this.text;
	},

    /** Get the text entry for the message box
     */	 
	addButton: function(caption, value) {
		this.buttons.push({caption: caption, value: value});
	},

    /** Get the buttons for the message box
     * @type Array
     */	 
	getButtons: function() {
		return this.buttons;
	},
	
	getButtonValue: function(caption) {
		for(var i=0; i< this.buttons.length; i++) {
			if(this.buttons[i].caption == caption) {
				return this.buttons[i].value;
			}
		}
		return null;
	},

    /**
	 * @type String
     */	 
	toString: function() {
		return this.getText();
	}
};

/*
 * Dynamic include of required libraries and check for Prototype
 * Code taken from scriptaculous (http://script.aculo.us/) - thanks guys!
var GarminDeviceControl = {
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
			throw("GarminDeviceControl requires the Prototype JavaScript framework >= 1.5.0");
		}

		$A(document.getElementsByTagName("script"))
		.findAll(
			function(s) {
				return (s.src && s.src.match(/GarminDeviceControl\.js(\?.*)?$/))
			}
		)
		.each(
			function(s) {
				var path = s.src.replace(/GarminDeviceControl\.js(\?.*)?$/,'../../');
				var includes = s.src.match(/\?.*load=([a-z,]*)/);
				var dependencies = 'garmin/device/GarminDevicePlugin' +
									',garmin/device/GarminDevice' +
									',garmin/util/Util-XmlConverter' +
									',garmin/util/Util-Broadcaster' +
									',garmin/util/Util-DateTimeFormat' +
									',garmin/util/Util-BrowserDetect' +
									',garmin/util/Util-PluginDetect' +
									',garmin/device/GarminObjectGenerator';
			    (includes ? includes[1] : dependencies).split(',').each(
					function(include) {
						GarminDeviceControl.require(path+include+'.js') 
					}
				);
			}
		);
	}
}

GarminDeviceControl.load();
 */

