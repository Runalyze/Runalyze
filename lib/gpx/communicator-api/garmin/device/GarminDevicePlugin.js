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
 * @fileoverview GarminDevicePlugin wraps the Garmin ActiveX/Netscape plugin that should be installed on your machine inorder to talk to a Garmin Gps Device.
 * The plugin is available for download from http://www8.garmin.com/support/download_details.jsp?id=3608
 * More information is available about this plugin from http://www8.garmin.com/products/communicator/
 * 
 * @author Diana Chow diana.chow[at]garmin.com, Carlo Latasa carlo.latasa@garmin.com
 * @version 1.0
 */

/** This api provides a set of functions to accomplish the following tasks with a Gps Device:
 * <br/>
 * <br/>  1) Unlocking devices allowing them to be found and accessed.
 * <br/>  2) Finding avaliable devices plugged into this machine.
 * <br/>  3) Reading from the device.
 * <br/>  4) Writing gpx files to the device.
 * <br/>  5) Downloading data to the device.
 * <br/>	 6) Geting messages, getting transfer status/progress and version information from the device.
 * <br/><br/>
 * Note that the GarminPluginAPIV1.xsd is referenced throughout this API. Please find more information about the GarminPluginAPIV1.xsd from http://
 *  
 * @class
 * requires Prototype
 * @param pluginElement element that references the Garmin GPS Control Web Plugin that should be installed.
 * 
 * constructor 
 * @return a new GarminDevicePlugin
 **/
Garmin.DevicePlugin = function(pluginElement){};  //just here for jsdoc
Garmin.DevicePlugin = Class.create();
Garmin.DevicePlugin.prototype = {

    /** Constructor.
     * @private
     */
	initialize: function(pluginElement) {        
	    this.plugin = pluginElement;
	    this.unlocked = false;
	    //console.debug("DevicePlugin constructor supportsFitnessWrite="+this.supportsFitnessWrite)
	},
	
	/** Unlocks the GpsControl object to be used at the given web address.  
     * More than one set of path-key pairs my be passed in, for example:
     * ['http://myDomain.com/', 'xxx','http://www.myDomain.com/', 'yyy']
     * See documentation site for more info on getting a key. <br/>
     * <br/>
     * Minimum plugin version 2.0.0.4
     * 
     * @param pathKeyPairsArray {Array}- baseURL and key pairs.  
     * @type Boolean
     * @return true if successfully unlocked or undefined otherwise
     */
	unlock: function(pathKeyPairsArray) {
	    var len = pathKeyPairsArray ? pathKeyPairsArray.length / 2 : 0;
	    for(var i=0;i<len;i++) {
	    	if (this.plugin.Unlock(pathKeyPairsArray[i*2], pathKeyPairsArray[i*2+1])){
	    		this.unlocked = true;
	    		return this.unlocked;
	    	}
	    }
	    
	    // Unlock codes for local development
	    this.tryUnlock = this.plugin.Unlock("file:///","cb1492ae040612408d87cc53e3f7ff3c")
        	|| this.plugin.Unlock("http://localhost","45517b532362fc3149e4211ade14c9b2")
        	|| this.plugin.Unlock("http://127.0.0.1","40cd4860f7988c53b15b8491693de133");
        
        this.unlocked = !this.plugin.Locked;
        	
	    return this.unlocked;
	},
	
	/** Returns true if the plug-in is unlocked.
	 */
	isUnlocked: function() {
		return this.unlocked;
	},
	
	/**
	* Check to see if plugin supports this function.  We are having to pass in the string due
	* to IE evaluating the function when passed in as a parameter.
	*
	* @param pluginFunctionName {String} - name of the plugin function.
	* @return true - if function is available in the plugin.  False otherwise.
	*/
	_getPluginFunctionExists: function(pluginFunctionName) {
		var pluginFunction = "this.plugin." + pluginFunctionName;
		
	    try {
		    if( typeof eval(pluginFunction) == "function" ) {
		        return true;
		    }
		    else if(eval(pluginFunction)) {
		        return true;
		    }
		    else {
		        return false;
		    }
		}
		catch( e ) {
		    // For a supported function Internet Explorer says type is undefined but
		    // throws when the call is made.
		    return true;
    	}
	},

	/**
	* Check to see if plugin supports this field.
	*
	* @param pluginField {String} - name of the plugin field.
	* @return true - if the field is available in the plugin.  False otherwise.
	*/
	_getPluginFieldExists: function(pluginField) {
	    try {
		    if( typeof pluginField == "string" ) {
		        return true;
		    }
		    else if( pluginField ) {
		        return true;
		    }
		    else {
		        return false;
		    }
		}
		catch( e ) {
		    // For a supported function Internet Explorer says type is undefined but
		    // throws when the call is made.
		    return true;
    	}
	},
	
	/** Lazy-logic accessor to fitness write support var.
	 * This is used to detect whether the user's installed plugin supports fitness writing.
	 * Fitness writing capability has a minimum requirement of plugin version 2.2.0.1.
	 * This should NOT be called until the plug-in has been unlocked.
	 */
	getSupportsFitnessWrite: function() {
	    return this._getPluginFunctionExists("StartWriteFitnessData"); 
	},
	
	/** Lazy-logic accessor to fitness write support var.
	 * This is used to detect whether the user's installed plugin supports fitness directory reading,
	 * which has a minimum requirement of plugin version 2.2.0.2.
	 * This should NOT be called until the plug-in has been unlocked.
	 */
	getSupportsFitnessDirectoryRead: function() {	
		return this._getPluginFunctionExists("StartReadFitnessDirectory");
	},

	/** Lazy-logic accessor to FIT read support var.
	 * This is used to detect whether the user's installed plugin supports FIT directory reading,
	 * which has a minimum requirement of plugin version 2.8.x.x (TBD)
	 * This should NOT be called until the plug-in has been unlocked.
	 */
	getSupportsFitDirectoryRead: function() {		
			return this._getPluginFunctionExists("StartReadFITDirectory");
	},
	
	/** Lazy-logic accessor to fitness read compressed support var.
	 * This is used to detect whether the user's installed plugin supports fitness reading in compressed format,
	 * which has a minimum requirement of plugin version 2.2.0.2.
	 * This should NOT be called until the plug-in has been unlocked.
	 */
	getSupportsFitnessReadCompressed: function() {
	    return this._getPluginFieldExists(this.plugin.TcdXmlz);
	},
	
	/** Initiates a find Gps devices action on the plugin. 
	 * Poll with finishFindDevices to determine when the plugin has completed this action.
	 * Use getDeviceXmlString to inspect xml contents for and array of Device nodes.<br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @see #finishFindDevices
	 * @see #cancelFindDevices
	 */
	startFindDevices: function() {
		this.plugin.StartFindDevices();
	},

	/** Cancels the current find devices interaction. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @see #startFindDevices
	 * @see #finishFindDevices
	 */
	cancelFindDevices: function() {
        this.plugin.CancelFindDevices();
	},

	/** Poll - with this function to determine completion of startFindDevices. Used after 
	 * the call to startFindDevices(). <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @type Boolean
	 * @return Returns true if completed finding devices otherwise false.
	 * @see #startFindDevices
	 * @see #cancelFindDevices
	 */
	finishFindDevices: function() {
    	return this.plugin.FinishFindDevices();
	},
	
	/** Returns information about the number of devices connected to this machine as 
	 * well as the names of those devices.  Refer to the 
	 * <a href="http://developer.garmin.com/schemas/device/v2/xmlspy/index.html#Link04DDFE88">Devices_t</a>
	 * element in the Device XML schema for what is included.
	 * The xml returned should contain a 'Device' element with 'DisplayName' and 'Number'
	 * if there is a device actually connected. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @type String
	 * @return Xml string with detailed device info
	 * @see #getDeviceDescriptionXml
	 */
	getDevicesXml: function(){
		return this.plugin.DevicesXmlString();
	},

	/** Returns information about the specified Device indicated by the device Number. 
	 * See the getDevicesXml function to get the actual deviceNumber assigned.
	 * Refer to the 
	 * <a href="http://developer.garmin.com/schemas/device/v2/xmlspy/index.html#Link04DDFE88">Devices_t</a>
	 * element in the Device XML schema for what is included in the XML. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @param deviceNumber {Number} Assigned by the plugin, see getDevicesXml for 
	 * assignment of that number.
	 * @type String
	 * @return Xml string with detailed device info
	 * @see #getDevicesXml
	 */
	getDeviceDescriptionXml: function(deviceNumber){
		return this.plugin.DeviceDescription(deviceNumber);
	},
	
	// Read Methods
	
	/** Initiates the read from the gps device conneted. Use finishReadFromGps and getGpsProgressXml to 
	 * determine when the plugin is done with this operation. Also, use getGpsXml to extract the
	 * actual data from the device. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @param deviceNumber {Number} assigned by the plugin, see getDevicesXml for 
	 * assignment of that number.
	 * @see #finishReadFromGps
	 * @see #cancelReadFromGps
	 * @see #getDevicesXml
	 */
	startReadFromGps: function(deviceNumber) {
		 this.plugin.StartReadFromGps( deviceNumber );
	},

	/** Indicates the status of the read process. It will return an integer
	 * know as the completion state.  The purpose is to show the 
 	 * user information about what is happening to the plugin while it 
 	 * is servicing your request. Used after startReadFromGps(). <br/>
 	 * <br/>
 	 * Minimum plugin version 2.0.0.4
 	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see #startReadFromGps
	 * @see #cancelReadFromGps
	 */
	finishReadFromGps: function() {
		return this.plugin.FinishReadFromGps();
	},
	
	/** Cancels the current read from the device. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * @see #startReadFromGps
	 * @see #finishReadFromGps
     */	
	cancelReadFromGps: function() {
		this.plugin.CancelReadFromGps();
	},
	
	/** Start the asynchronous ReadFitnessData operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.1.0.3 for FitnessHistory type<br/>
     * Minimum plugin version 2.2.0.1 for FitnessWorkouts, FitnessUserProfile, FitnessCourses
	 * 
	 * @param deviceNumber {Number} assigned by the plugin, see getDevicesXmlString for 
	 * assignment of that number.
	 * @param dataTypeName {String} a fitness datatype from the 
	 * <a href="http://developer.garmin.com/schemas/device/v2">Garmin Device XML</a> 
	 * retrieved with getDeviceDescriptionXml
	 * @see #finishReadFitnessData  
	 * @see #cancelReadFitnessData
	 * @see #getDeviceDescriptionXml
	 * @see Garmin.DeviceControl#FILE_TYPES
	 */
	startReadFitnessData: function(deviceNumber, dataTypeName) {
		if( !this.checkPluginVersionSupport([2,1,0,3]) ) {
			throw new Error("Your Communicator Plug-in version (" + this.getPluginVersionString() + ") does not support reading this type of fitness data.");
		}

		 this.plugin.StartReadFitnessData( deviceNumber, dataTypeName );
	},

	/** Poll for completion of the asynchronous ReadFitnessData operation. <br/>
     * <br/>
     * If the CompletionState is eMessageWaiting, call MessageBoxXml
     * to get a description of the message box to be displayed to
     * the user, and then call RespondToMessageBox with the value of the
     * selected button to resume operation.<br/>
     * <br/>
     * Minimum plugin version 2.1.0.3 for FitnessHistory type <br/>
     * Minimum plugin version 2.2.0.1 for FitnessWorkouts, FitnessUserProfile, FitnessCourses
	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see #startReadFitnessData  
	 * @see #cancelReadFitnessData
	 */
	finishReadFitnessData: function() {
	 	 return  this.plugin.FinishReadFitnessData();
	},
	
	/** Cancel the asynchronous ReadFitnessData operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.1.0.3 for FitnessHistory type <br/>
     * Minimum plugin version 2.2.0.1 for FitnessWorkouts, FitnessUserProfile, FitnessCourses
     * 
     * @see #startReadFitnessData  
	 * @see #finishReadFitnessData
     */	
	cancelReadFitnessData: function() {
		this.plugin.CancelReadFitnessData();
	},
	
	/**
	 * List all of the FIT files on the device. Starts an asynchronous directory listing operation for the device.
	 * Poll for finished with FinishReadFitDirectory. The result is stored in ______.
	 * 
	 * Minimum plugin version 2.7.2.0
	 * @see #finishReadFitDirectory
	 */
	startReadFitDirectory: function(deviceNumber) {
	    if( !this.getSupportsFitDirectoryRead() ) {
			throw new Error("Your Communicator Plug-in version (" + this.getPluginVersionString() + ") does not support reading directory listing data.");
		}
	    this.plugin.StartReadFITDirectory(deviceNumber);
	},
	
	/** Poll for completion of the asynchronous startReadFitDirectory operation. <br/>
     * <br/>
	 * Minimum plugin version 2.7.2.0
	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
	 * 
	 * @see #startReadFitDirectory
	 * @see #cancelReadFitDirectory
	 * @see #getMessageBoxXml
	 * @see #respondToMessageBox
	 */
	finishReadFitDirectory: function() {
		return this.plugin.FinishReadFITDirectory();
	},
	
	/** Start the asynchronous ReadFitnessDirectory operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @param deviceNumber {Number} assigned by the plugin, see getDevicesXmlString for 
	 * assignment of that number.
	 * @param dataTypeName a Fitness DataType from the GarminDevice.xml retrieved with DeviceDescription
	 * @see #finishReadFitnessDirectory
	 * @see #cancelReadFitnessDirectory
	 * @see Garmin.DeviceControl#FILE_TYPES
	 */
	startReadFitnessDirectory: function(deviceNumber, dataTypeName) {
		if( !this.getSupportsFitnessDirectoryRead() ) {
			throw new Error("Your Communicator Plug-in version (" + this.getPluginVersionString() + ") does not support reading fitness directory data.");
		}
		this.plugin.StartReadFitnessDirectory( deviceNumber, dataTypeName);
	},
	
	/** Poll for completion of the asynchronous ReadFitnessDirectory operation. <br/>
     * <br/>
     * If the CompletionState is eMessageWaiting, call getMessageBoxXml
     * to get a description of the message box to be displayed to
     * the user, and then call respondToMessageBox with the value of the
     * selected button to resume operation.<br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
	 * 
	 * @see #startReadFitnessDirectory
	 * @see #cancelReadFitnessDirectory
	 * @see #getMessageBoxXml
	 * @see #respondToMessageBox
	 */
	finishReadFitnessDirectory: function() {
		return this.plugin.FinishReadFitnessDirectory();
	},
	
	/** Cancel the asynchronous ReadFitnessDirectory operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @see #startReadFitnessDirectory
	 * @see #finishReadFitnessDirectory
     */	
	cancelReadFitnessDirectory: function() {
		this.plugin.CancelReadFitnessDirectory();
	},

	/** Cancel the asynchronous ReadFitDirectory operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.7.2.0
	 * 
	 * @see #startReadFitDirectory
	 * @see #finishReadFitDirectory
     */	
	cancelReadFitDirectory: function() {
		this.plugin.CancelReadFitDirectory();
	},
	
	/** Start the asynchronous ReadFitnessDetail operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.2
	 * 
	 * @param deviceNumber assigned by the plugin, see getDevicesXmlString for 
	 * assignment of that number.
	 * @param dataTypeName a Fitness DataType from the GarminDevice.xml retrieved with DeviceDescription
	 * @see #finishReadFitnessDetail
	 * @see #cancelReadFitnessDetail
	 * @see Garmin.DeviceControl#FILE_TYPES
	 */
	startReadFitnessDetail: function(deviceNumber, dataTypeName, dataId) {
		if( !this.checkPluginVersionSupport([2,2,0,2]) ) {
			throw new Error("Your Communicator Plug-in version (" + this.getPluginVersionString() + ") does not support reading fitness detail.");
		}
		
		this.plugin.StartReadFitnessDetail(deviceNumber, dataTypeName, dataId);
	},
	
	/** Poll for completion of the asynchronous ReadFitnessDetail operation. <br/>
     * <br/>
     * If the CompletionState is eMessageWaiting, call MessageBoxXml
     * to get a description of the message box to be displayed to
     * the user, and then call RespondToMessageBox with the value of the
     * selected button to resume operation.<br/>
     * <br/>
     * Minimum plugin version 2.2.0.2
	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
	 * 
	 */
	finishReadFitnessDetail: function() {
		return this.plugin.FinishReadFitnessDetail();
	},
	
	/** Cancel the asynchronous ReadFitnessDirectory operation. <br/>
	 * <br/>
	 * Minimum version 2.2.0.2
	 * 
	 * @see #startReadFitnessDetail
	 * @see #finishReadFitnessDetail
     */	
	cancelReadFitnessDetail: function() {
		this.plugin.CancelReadFitnessDetail();
	},
	
	// Write Methods
	
	/** Initates writing the gpsXml to the device specified by deviceNumber with a filename set by filename.
	 * The gpsXml is typically in GPX fomat and the filename is only the name without the extension. The 
	 * plugin will append the .gpx extension automatically.<br/>
	 * <br/>
	 * Use finishWriteToGps to poll when the write operation/plugin is complete.<br/>
	 * <br/>
	 * Uses the helper functions to set the xml info and the filename.  <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4<br/>
     * Minimum plugin version 2.2.0.1 for writes of GPX to SD Card
	 * 
	 * @param gpsXml {String} the gps/gpx information that should be transferred to the device.
	 * @param filename {String} the desired filename for the gpsXml that shall end up on the device.
	 * @param deviceNumber {Number} the device number assigned by the plugin.
	 * @see #finishWriteToGps
	 * @see #cancelWriteToGps  
	 */
	startWriteToGps: function(gpsXml, filename, deviceNumber) {
		this._setWriteGpsXml(gpsXml);
		this._setWriteFilename(filename);
	    this.plugin.StartWriteToGps(deviceNumber);
	},

	/** Sets the gps xml content that will end up on the device once the transfer is complete.
	 * Use in conjunction with startWriteToGps to initiate the actual write.
	 *
	 * @private 
	 * @param gpsXml {String} xml data that is to be written to the device. Must be in GPX format.
	 */
	_setWriteGpsXml: function(gpsXml) {
    	this.plugin.GpsXml = gpsXml;
	},

	/** This the filename that wil contain the gps xml once the transfer is complete. Use with 
	 * setWriteGpsXml to set what the file contents will be. Also, use startWriteToGps to 
	 * actually make the write happen.
	 * 
	 * @private
	 * @param filename {String} the actual filename that will end up on the device. Should only be the
	 * name and not the extension. The plugin will append the extension portion to the file name--typically .gpx.
	 * @see #setWriteGpsXml, #startWriteToGps, #startWriteFitnessData
	 */
	_setWriteFilename: function(filename) {
    	this.plugin.FileName = filename;
	},

	/** This is used to indicate the status of the write process. It will return an integer
	 * know as the completion state.  The purpose is to show the 
 	 * user information about what is happening to the plugin while it 
 	 * is servicing your request. <br/>
 	 * <br/>
 	 * Minimum plugin version 2.0.0.4<br/>
     * Minimum plugin version 2.2.0.1 for writes of GPX to SD Card 
 	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see #startWriteToGps
	 * @see #cancelWriteToGps  
 	 */
	finishWriteToGps: function() {
		//console.debug("Plugin.finishWriteToGps");
	   	return  this.plugin.FinishWriteToGps();
	},
    
	/** Cancels the current write operation to the gps device. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4<br/>
     * Minimum plugin version 2.2.0.1 for writes of GPX to SD Card
     * 
     * @see #startWriteToGps
	 * @see #finishWriteToGps  
     */	
	cancelWriteToGps: function() {
		this.plugin.CancelWriteToGps();
	},

	/** Start the asynchronous StartWriteFitnessData operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
	 * @param tcdXml {String} XML of TCD data
	 * @param deviceNumber {Number} the device number, assigned by the plugin. See getDevicesXmlString for 
	 * assignment of that number.
	 * @param filename {String} the filename to write to on the device.
	 * @param dataTypeName {String} a Fitness DataType from the GarminDevice.xml retrieved with DeviceDescription
	 * @see #finishWriteFitnessData  
	 * @see #cancelWriteFitnessData
	 * @see Garmin.DeviceControl#FILE_TYPES
	 */
	startWriteFitnessData: function(tcdXml, deviceNumber, filename, dataTypeName) {	
		if( !this.checkPluginVersionSupport([2,2,0,1]) ) {
			throw new Error("Your Communicator Plug-in version (" + this.getPluginVersionString() + ") does not support writing fitness data.");
		}
		
		this._setWriteTcdXml(tcdXml);
		this._setWriteFilename(filename);
		this.plugin.StartWriteFitnessData(deviceNumber, dataTypeName);
	},
	
	/** This is used to indicate the status of the write process for fitness data. It will return an integer
	 * know as the completion state.  The purpose is to show the 
 	 * user information about what is happening to the plugin while it 
 	 * is servicing your request. <br/>
 	 * <br/>
 	 * Minimum plugin version 2.2.0.1
 	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see #startWriteFitnessData  
	 * @see #cancelWriteFitnessData
	 */
	finishWriteFitnessData: function() {
	 	return  this.plugin.FinishWriteFitnessData();
	},
	
	/** Cancel the asynchronous ReadFitnessData operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.2.0.1
	 * 
	 * @see #startWriteFitnessData  
	 * @see #finishWriteFitnessData
     */	
	cancelWriteFitnessData: function() {
		this.plugin.CancelWriteFitnessData();
	},
	
	/** Sets the tcd xml content that will end up on the device once the transfer is complete.
	 * Use in conjunction with startWriteFitnessData to initiate the actual write.
	 *
	 * @private 
	 * @param tcdXml {String} xml data that is to be written to the device. Must be in TCX format.
	 */
	_setWriteTcdXml: function(tcdXml) {
    	this.plugin.TcdXml = tcdXml;
	},
	
	/**
	 * Determine the amount of space available on a Mass Storage Mode Device Volume.
	 * 
	 * @param {Number} deviceNumber - the device number assigned by the plugin. See {@link getDevicesXmlString} for 
	 * assignment of that number.
	 * @param {String} relativeFilePath - if a file is being replaced, set to relative path on device, otherwise set to empty string.
	 * @return -1 for non-mass storage mode devices.  
	 */
	bytesAvailable: function(deviceNumber, relativeFilePath) {
	    return this.plugin.BytesAvailable(deviceNumber, relativeFilePath);
	},

    /** Responds to a message box on the device. <br/>
     * <br/>
     * Minimum plugin version 2.0.0.4
     *   
     * @param response should be an int which corresponds to a button value from this.plugin.MessageBoxXml
     */
    respondToMessageBox: function(response) {
        this.plugin.RespondToMessageBox(response);
    },

	/** Initates downloading the gpsDataString to the device specified by deviceNumber.
	 * The gpsDataString is typically in GPI fomat and the filename is only the name without the extension. The 
	 * plugin will append the .gpx extension automatically.<br/>
	 * <br/>
	 * Use finishWriteToGps to poll when the write operation/plugin is complete.<br/>
	 * <br/>
	 * Uses the helper functions to set the xml info and the filename.  <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 *  
	 * @param gpsDataString {String} the gpi information that should be transferred to the device.
	 * @param filename {String} the filename to write to on the device.
	 * @param deviceNumber {Number} the device number assigned by the plugin. 
	 * @see #finishDownloadData  
	 * @see #cancelDownloadData
	 */
	startDownloadData: function(gpsDataString, deviceNumber) {
		//console.debug("Plugin.startDownloadData gpsDataString="+gpsDataString);
		this.plugin.StartDownloadData(gpsDataString, deviceNumber);
	},

	/** This is used to indicate the status of the download process. It will return an integer
	 * know as the completion state.  The purpose is to show the 
 	 * user information about what is happening to the plugin while it 
 	 * is servicing your request.<br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @type Number
	 * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see #startDownloadData  
	 * @see #cancelDownloadData
	 */
	finishDownloadData: function() {
		//console.debug("Plugin.finishDownloadData");
		return this.plugin.FinishDownloadData();
	},

	/** Cancel the asynchronous Download Data operation. <br/>
	 * <br/>
	 * Minimum plugin version 2.0.0.4
	 * 
	 * @see #startDownloadData  
	 * @see #finishDownloadData
	 */
	cancelDownloadData: function() {
		this.plugin.CancelDownloadData();
	},

    /** Indicates success of StartDownloadData operation. <br/>
     * <br/>
     * Minimum plugin version 2.0.0.4
     * 
     * @type Boolean
     * @return True if the last StartDownloadData operation was successful
     */
    downloadDataSucceeded: function() {
		return this.plugin.DownloadDataSucceeded;
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
     * @see Garmin.DevicePlugin.finishUnitSoftwareUpdate
     * @see Garmin.DevicePlugin.cancelUnitSoftwareUpdate
     * @see Garmin.DevicePlugin.downloadDataSucceeded
     * @version plugin v2.6.2.0
     */
    startUnitSoftwareUpdate: function(updateResponsesXml, deviceNumber) {
        this.plugin.StartUnitSoftwareUpdate(updateResponsesXml, deviceNumber);
    },
    
    /** Poll for completion of the asynchronous Unit Software Update operation. It will return an integer
	 * know as the completion state.  The purpose is to show the 
 	 * user information about what is happening to the plugin while it 
 	 * is servicing your request.<br/>
 	 * @type Number 
     * @version plugin v2.6.2.0
     * @return Completion state - The completion state can be one of the following: <br/>
	 *  <br/>
	 *	0 = idle <br/>
 	 * 	1 = working <br/>
 	 * 	2 = waiting <br/>
 	 * 	3 = finished <br/>
 	 * @see Garmin.DevicePlugin.startUnitSoftwareUpdate
 	 * @see Garmin.DevicePlugin.cancelUnitSoftwareUpdate
     */
    finishUnitSoftwareUpdate: function() {
        return this.plugin.FinishUnitSoftwareUpdate();  
    },
    
    /** Cancel the asynchrous Download Data operation
     * @version plugin v2.6.2.0
     */
    cancelUnitSoftwareUpdate: function() {
        this.plugin.CancelUnitSoftwareUpdate();
    },
    
    /** Get the UnitSoftwareUpdateRequests for a given device.
     * This request retrieves the main system software (system region only.)
     * @param deviceNumber {Number} the device number to retrieve unit software information for. 
     * @return {String} XML string of the document format in the namespace below, or
     * the most current version of that xms namespace
     * http://www.garmin.com/xmlschemas/UnitSoftwareUpdate/v3
     * @version plugin v2.6.2.0
     * @see Garmin.DevicePlugin.getAdditionalSoftwareUpdateRequests
     */
//    getUnitSoftwareUpdateRequests: function(deviceNumber) {
//        return this.plugin.UnitSoftwareUpdateRequests(deviceNumber);
//    },
    
    /** Get the AdditionalSoftwareUpdateRequests for a given device.
     * This request retrieves the additional system software (all software except for system region.)
     * @param deviceNumber {Number} the device number to retrieve unit software information for.
     * @return {String} XML string of the document format in the namespace below, or
     * the most current version of that xms namespace
     * http://www.garmin.com/xmlschemas/UnitSoftwareUpdate/v3
     * @version plugin v2.6.2.0
     * @see Garmin.DevicePlugin.getUnitSoftwareUpdateRequests
     */
//    getAdditionalSoftwareUpdateRequests: function(deviceNumber) {
//        return this.plugin.AdditionalSoftwareUpdateRequests(deviceNumber);
//    },
    
    /** Indicates success of WriteToGps operation. <br/>
     * <br/>
     * Minimum plugin version 2.0.0.4
     * 
     * @type Boolean
     * @return True if the last ReadFromGps or WriteToGps operation was successful
     */
    gpsTransferSucceeded: function() {
		return this.plugin.GpsTransferSucceeded;
    },

    /** Indicates success of ReadFitnessData or WriteFitnessData operation. <br/>
     * <br/>
     * Minimum plugin version 2.1.0.3
     * 
     * @type Boolean
     * @return True if the last ReadFitnessData or WriteFitnessData operation succeeded
     */
    fitnessTransferSucceeded: function() {
		return this.plugin.FitnessTransferSucceeded;
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
    getBinaryFile: function(deviceNumber, relativeFilePath, compressed) {
        return this.plugin.GetBinaryFile(deviceNumber, relativeFilePath, compressed);
    },
    
    /** This is the GpsXml information from the device. Typically called after a read operation.
     * 
     * @see #finishReadFromGps
     */
	getGpsXml: function(){
		return this.plugin.GpsXml;
	},

    /** This is the fitness data Xml information from the device. Typically called after a ReadFitnessData operation. <br/>
	 * <br/>
     * Schemas for the TrainingCenterDatabase format are available at
     * <a href="http://developer.garmin.com/schemas/tcx/v2/">http://developer.garmin.com/schemas/tcx/v2/</a><br/>
     * <br/>
     * Minimum plugin version 2.1.0.3
     * 
     * @see #finishReadFitnessData
     * @see #finishReadFitnessDirectory
     * @see #finishReadFitnessDetail
     */
	getTcdXml: function(){
		return this.plugin.TcdXml;
	},
	
	 /** Returns last read fitness xml data in compressed format.  The xml is compressed as gzp and base64 expanded. <br/>
	  * <br/>
	  * Minimum plugin version 2.2.0.2
	  * 
	  * @return The read xml data in compressed gzp and base64 expanded format.
	  * @see #finishReadFitnessData
      * @see #finishReadFitnessDirectory
      * @see #finishReadFitnessDetail
	  */
	getTcdXmlz: function() {
		return this.plugin.TcdXmlz;
	},

	 /** Returns last read directory xml data.<br/>
	  * <br/>
	  * 
	  * @return The directory xml data
	  * @see #finishReadFitDirectory
	  */
	getDirectoryXml: function() {
		return this.plugin.DirectoryListingXml;
	},

    /** Returns the xml describing the message when the plug-in is waiting for input from the user.
     * @type String
     * @return The xml describing the message when the plug-in is waiting for input from the user.
     */
	getMessageBoxXml: function(){
		return this.plugin.MessageBoxXml;
	},
    
	/** Get the status/progress of the current state or transfer.
     * @type String
     * @return The xml describing the current progress state of the plug-in.
     */	
	getProgressXml: function() {
		return this.plugin.ProgressXml;
	},

	/** Returns metadata information about the plugin version. 
     * @type String
     * @return The xml describing the user's version of the plug-in.
	 */
	getVersionXml: function() {
		return this.plugin.VersionXml;
	},
	
	/** Gets a string of the version number for the plugin the user has currently installed.
     * @type String 
     * @return A string of the format "versionMajor.versionMinor.buildMajor.buildMinor", ex: "2.0.0.4"
     */	
	getPluginVersionString: function() {
		var versionArray = this.getPluginVersion();
	
		var versionString = versionArray[0] + "." + versionArray[1] + "." + versionArray[2] + "." + versionArray[3];
	    return versionString;
	},
	
	/** Gets the version number for the plugin the user has currently installed.
     * @type Array 
     * @return An array of the format: [versionMajor, versionMinor, buildMajor, buildMinor].
     */	
	getPluginVersion: function() {
    	var versionMajor = parseInt(this._getElementValue(this.getVersionXml(), "VersionMajor"));
    	var versionMinor = parseInt(this._getElementValue(this.getVersionXml(), "VersionMinor"));
    	var buildMajor = parseInt(this._getElementValue(this.getVersionXml(), "BuildMajor"));
    	var buildMinor = parseInt(this._getElementValue(this.getVersionXml(), "BuildMinor"));

	    var versionArray = [versionMajor, versionMinor, buildMajor, buildMinor];
	    return versionArray;
	},
	
	/** Sets the required plugin version number for the application.
	 * @param reqVersionArray {Array} The required version to set to.  In the format [versionMajor, versionMinor, buildMajor, buildMinor]
	 * 			i.e. [2,2,0,1]
	 */
	setPluginRequiredVersion: function(reqVersionArray) {
		Garmin.DevicePlugin.REQUIRED_VERSION.versionMajor = reqVersionArray[0];
		Garmin.DevicePlugin.REQUIRED_VERSION.versionMinor = reqVersionArray[1];
		Garmin.DevicePlugin.REQUIRED_VERSION.buildMajor = reqVersionArray[2];
		Garmin.DevicePlugin.REQUIRED_VERSION.buildMinor = reqVersionArray[3];
	},
	
	/** Sets the latest plugin version number.  This represents the latest version available for download at Garmin.
	 * We will attempt to keep the default value of this up to date with each API release, but this is not guaranteed,
	 * so set this to be safe or if you don't want to upgrade to the latest API.
	 * 
	 * @param reqVersionArray {Array} The latest version to set to.  In the format [versionMajor, versionMinor, buildMajor, buildMinor]
	 * 			i.e. [2,2,0,1]
	 */
	setPluginLatestVersion: function(reqVersionArray) {
		Garmin.DevicePlugin.LATEST_VERSION.versionMajor = reqVersionArray[0];
		Garmin.DevicePlugin.LATEST_VERSION.versionMinor = reqVersionArray[1];
		Garmin.DevicePlugin.LATEST_VERSION.buildMajor = reqVersionArray[2];
		Garmin.DevicePlugin.LATEST_VERSION.buildMinor = reqVersionArray[3];
	},
	
	/** Used to check if the user's installed plugin version meets the required version for feature support purposes.
	 *  
	 * @param {Array} reqVersionArray An array representing the required version, in the format: [versionMajor, versionMinor, buildMajor, buildMinor]. 
	 * @return {boolean} true if the passed in required version is met by the user's plugin version (user's version is equal to or greater), false otherwise.
	 * @see setPluginRequiredVersion
	 */
	checkPluginVersionSupport: function(reqVersionArray) {
		
		var pVersion = this._versionToNumber(this.getPluginVersion());
   		var rVersion = this._versionToNumber(reqVersionArray);
        return (pVersion >= rVersion);
	},
	
	/**
	 * @private
	 */
	_versionToNumber: function(versionArray) {
		if (versionArray[1] > 99 || versionArray[2] > 99 || versionArray[3] > 99)
			throw new Error("version segment is greater than 99: "+versionArray);
		return 1000000*versionArray[0] + 10000*versionArray[1] + 100*versionArray[2] + versionArray[3];
	},
	
	/** Determines if the Garmin plugin is at least the required version for the application.
     * @type Boolean
     * @see setPluginRequiredVersion
	 */
	isPluginOutOfDate: function() {
    	var pVersion = this._versionToNumber(this.getPluginVersion());
   		var rVersion = this._versionToNumber(Garmin.DevicePlugin.REQUIRED_VERSION.toArray());
        return (pVersion < rVersion);
	},
	
	/** Checks if plugin is the most recent version released, for those that want the latest and greatest.
     */
    isUpdateAvailable: function() {
    	var pVersion = this._versionToNumber(this.getPluginVersion());
   		var cVersion = this._versionToNumber(Garmin.DevicePlugin.LATEST_VERSION.toArray());
        return (pVersion < cVersion);
    },
	
	/** Pulls value from xml given an element name or null if no tag exists with that name.
	 * @private
	 */
	_getElementValue: function(xml, tagName) {
		var start = xml.indexOf("<"+tagName+">");
		if (start == -1)
			return null;
		start += tagName.length+2;
		var end = xml.indexOf("</"+tagName+">");
		var result = xml.substring(start, end);
		return result;
	}
	
};

/** Latest version (not required) of the Garmin Communicator Plugin, and a complementary toString function to print it out with
 */
Garmin.DevicePlugin.LATEST_VERSION = {
    versionMajor: 2,
    versionMinor: 7,
    buildMajor: 3,
    buildMinor: 0,
    
    toString: function() {
        return this.versionMajor + "." + this.versionMinor + "." + this.buildMajor + "." + this.buildMinor;
    },
    
    toArray: function() {
        return [this.versionMajor, this.versionMinor, this.buildMajor, this.buildMinor];
    }	
}; 
 
 
/** Latest required version of the Garmin Communicator Plugin, and a complementary toString function to print it out with. 
 */
Garmin.DevicePlugin.REQUIRED_VERSION = {
    versionMajor: 2,
    versionMinor: 1,
    buildMajor: 0,
    buildMinor: 1,
    
    toString: function() {
        return this.versionMajor + "." + this.versionMinor + "." + this.buildMajor + "." + this.buildMinor;
    },
    
    toArray: function() {
        return [this.versionMajor, this.versionMinor, this.buildMajor, this.buildMinor];
    }	
};