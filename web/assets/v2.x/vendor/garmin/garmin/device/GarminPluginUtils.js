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
 * @fileoverview Garmin.Device A place-holder for Garmin device information. <br/>
 * Source: 
 * <a href="http://developer.garmin.com/web/communicator-api/garmin/device/GarminDevice.js">Hosted Distribution</a> &nbsp;
 * <a href="https://svn.garmindeveloper.com/web/trunk/communicator/communicator-api/src/main/webapp/garmin/device/GarminDevice.js">Source Control</a><br/>
 * @version 1.9
 */
 
/** Plugin-specific utility functions.
 *
 * @class Garmin.PluginUtils
 * @constructor 
 */
Garmin.PluginUtils = function(){}; //just here for jsdoc
Garmin.PluginUtils = {

	initialize: function() {
	},
	
	/** Parse device xml string into device objects.  
	 * 
	 * Each device object contains the following:
	 * 1) device display name
	 * 2) device number
	 * 3) device XML as an XML document
	 *
	 * 
	 * @param garminPlugin - the GarminDevicePlugin object having access to the device XML data.
	 * @param getDetailedDeviceData - boolean indicating if you want to get the entire device XML 
	 *  as an XML document (rather than the few essentials)
	 * @returns {Array} an array of {@link Garmin.Device} objects
	 */ 
	parseDeviceXml: function(garminPlugin, getDetailedDeviceData) {
        var xmlDevicesString = garminPlugin.getDevicesXml();
        var xmlDevicesDoc = Garmin.XmlConverter.toDocument(xmlDevicesString); 
        
        var deviceList = xmlDevicesDoc.getElementsByTagName("Device");
        var devices = new Array();
        var numDevices = deviceList.length;
        
    	for( var i=0; i < numDevices; i++ ) {
			var displayName = deviceList[i].getAttribute("DisplayName");        		
    		var deviceNumber = parseInt( deviceList[i].getAttribute("Number") );
    		var deviceDescriptionDoc = null;
    		if (getDetailedDeviceData) {
				var deviceDescriptionXml = garminPlugin.getDeviceDescriptionXml(deviceNumber);
				deviceDescriptionDoc = Garmin.XmlConverter.toDocument(deviceDescriptionXml);    
    		}
    		var theDevice = Garmin.PluginUtils._createDeviceFromXml(displayName, deviceNumber, deviceDescriptionDoc);
    		theDevice.setIsFileBased(garminPlugin.isDeviceFileBased(deviceNumber));
    		theDevice.setParentNumber(garminPlugin.getParentDevice(deviceNumber));
    		devices.push(theDevice);
    	}
    	
        for( var i=0; i < numDevices; i++ )
        {
            var theDevice = devices[i];
            var theParentNum = theDevice.getParentNumber();
            var theParent = null;
            if( theParentNum != -1 )
            {
                theParent = this._deviceWithNumber( devices, theParentNum );
                if(theParent)
                {
                	theParent.addChild( theDevice );
				}
            }
            theDevice.setParent( theParent );
        }
    	return devices;
	},
	
	/** Returns the Garmin.Device object with the given number
	 * @private
	 */
	_deviceWithNumber: function(aDeviceList, aNumber)
	{
    	var theFoundDevice = null;
    	for( var i=0; i < aDeviceList.length; i++ )
    	{
            if( aDeviceList[i].getNumber() == aNumber )
            {
                theFoundDevice = aDeviceList[i];
                break;
            }
        }
        return theFoundDevice;
	},
	
	/** Create a Garmin.Device instance for each connected device found.
	 * @private
	 */
	_createDeviceFromXml: function(displayName, deviceNumber, deviceDescriptionDoc) {
   		var device = new Garmin.Device(displayName, deviceNumber);

   		if(deviceDescriptionDoc) {						
			var partNumber = deviceDescriptionDoc.getElementsByTagName("PartNumber")[0].childNodes[0].nodeValue;
			var softwareVersion = deviceDescriptionDoc.getElementsByTagName("SoftwareVersion")[0].childNodes[0].nodeValue;
			var description = deviceDescriptionDoc.getElementsByTagName("Description")[0].childNodes[0].nodeValue;
			var id = deviceDescriptionDoc.getElementsByTagName("Id")[0].childNodes[0].nodeValue;
			
			device.setPartNumber(partNumber);
			device.setSoftwareVersion(softwareVersion);
			device.setDescription(description);
			device.setId(id);
			
			var dataTypeList = deviceDescriptionDoc.getElementsByTagName("MassStorageMode")[0].getElementsByTagName("DataType");
			var numOfDataTypes = dataTypeList.length;
	
			for ( var j = 0; j < numOfDataTypes; j++ ) {
				var dataName = dataTypeList[j].getElementsByTagName("Name")[0].childNodes[0].nodeValue;					
				var dataExt = dataTypeList[j].getElementsByTagName("FileExtension")[0].childNodes[0].nodeValue;
				
				var dataType = new Garmin.DeviceDataType(dataName, dataExt);
				var fileList = dataTypeList[j].getElementsByTagName("File");
				
				var numOfFiles = fileList.length;
				
				for ( var k = 0; k < numOfFiles; k++ ) {
					// Path is an optional element in the schema
					var pathList = fileList[k].getElementsByTagName("Path");
					var transferDir = fileList[k].getElementsByTagName("TransferDirection")[0].childNodes[0].nodeValue;											
					
					if ((transferDir == Garmin.DeviceControl.TRANSFER_DIRECTIONS.read)) {
						dataType.setReadAccess(true);
						
						if (pathList.length > 0) {
						    var filePath = pathList[0].childNodes[0].nodeValue;
						    dataType.setReadFilePath(filePath);							
						}
					} else if ((transferDir == Garmin.DeviceControl.TRANSFER_DIRECTIONS.write)) {			
						dataType.setWriteAccess(true);
						
						if (pathList.length > 0) {
                            var filePath = pathList[0].childNodes[0].nodeValue;
                            dataType.setWriteFilePath(filePath);                         
                        }
					} else if ((transferDir == Garmin.DeviceControl.TRANSFER_DIRECTIONS.both)) {		
						dataType.setReadAccess(true);
						dataType.setWriteAccess(true);
						
						if (pathList.length > 0) {
                            var filePath = pathList[0].childNodes[0].nodeValue;
                            dataType.setReadFilePath(filePath);
                            dataType.setWriteFilePath(filePath);                         
                        }
					}

                    // Deprecated! Need to be removed at some point.
					if( pathList.length > 0) {
						var filePath = pathList[0].childNodes[0].nodeValue;
						dataType.setFilePath(filePath);
					}
					
					// Identifier is optional
					var identifierList = fileList[k].getElementsByTagName("Identifier");
					if( identifierList.length > 0) {
						var identifier = identifierList[0].childNodes[0].nodeValue;
						dataType.setIdentifier(identifier);
					}
				}			
				device.addDeviceDataType(dataType);
			}   			
   		}
		return device;
	},
	
	/** Is this a device XML error message.
	 * @param {String} xml string or Error instance with embedded xml
	 * @type Boolean
	 * @return true if error is device-generared error
	 */
	isDeviceErrorXml: function(error) {
		var msg = (typeof(error)=="string") ? error : error.name + ": " + error.message;
		return ( (msg.indexOf("<ErrorReport") > 0) );
	},
	
	/** Best effort to convert XML error message to a String.
	 * @param {String} xml string or Error instance with embedded xml
	 * @type String
	 * @return Human readable interpretation of XML message
	 */
	getDeviceErrorMessage: function(error) {
		var msg = (typeof(error)=="string") ? error : error.name + ": " + error.message;
		var startPos = msg.indexOf("<ErrorReport");
		if (startPos>0) { //strip off any text surrounding the xml
			var endPos = msg.indexOf("</ErrorReport>") + "</ErrorReport>".length;
			msg = msg.substring(startPos, endPos);
		}
        var xmlDoc = Garmin.XmlConverter.toDocument(msg); 
        var errorMessage = Garmin.PluginUtils._getElementValue(xmlDoc, "Extra");
        var sourceFileName = Garmin.PluginUtils._getElementValue(xmlDoc, "SourceFileName");
        var sourceFileLine = Garmin.PluginUtils._getElementValue(xmlDoc, "SourceFileLine");
        var msg = "";
        if (errorMessage) {
        	msg = errorMessage;
        } else { // gota show something :-(
        	msg = "Plugin error: ";
	        if (sourceFileName)
	        	msg += "source: "+sourceFileName;
	        if (sourceFileLine)
	        	msg += ", line: "+sourceFileLine;
        }
		return msg;
	},

	/** Get the value of a document element
	 * @param doc - the document that the element is contained in
	 * @param elementName - the name of the element to get the value from
	 * @return the value of the element identified by elementName 
	 */	
	_getElementValue: function(doc, elementName) {
        var elementNameNodes = doc.getElementsByTagName(elementName);
        var value = (elementNameNodes && elementNameNodes.length>0) ? elementNameNodes[0].childNodes[0].nodeValue : null;
 		return value;		
	}
};


/** GPI XML generation utility.
 *
 * @class Garmin.PluginUtils
 * @constructor 
 **/
Garmin.GpiUtil = function(){};
Garmin.GpiUtil = {
	
	/** Build a single DeviceDownload XML for multiple file downloads.  
	 * 
	 * @param descriptionArray - Even sized array with matching source and destination pairs.
	 * @param regionId - Optional parameter designating RegionId attribute of File.  For now, this single
	 * regionId will be applied to all files in the descriptionArray if provided at all.    
	 * 
	 */
	buildMultipleDeviceDownloadsXML: function(descriptionArray) {
		if(descriptionArray.length % 2 != 0) {
			throw new Error("buildMultipleDeviceDownloadsXML expects even sized array with matching source and destination pairs");
		}
		var xml =
		'<?xml version="1.0" encoding="UTF-8"?>\n' +
		'<DeviceDownload xmlns="http://www.garmin.com/xmlschemas/PluginAPI/v1"\n' +
		' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"\n' +
		' xsi:schemaLocation="http://www.garmin.com/xmlschemas/PluginAPI/v1 http://www.garmin.com/xmlschemas/GarminPluginAPIV1.xsd">\n';

		for(var i=0;i<descriptionArray.length;i+=2) {
			var source = descriptionArray[i];
			var destination = descriptionArray[i+1];
		
//			if(!Garmin.GpiUtil.isDestinationValid(destinationArray[i])) {
//				throw new Error("Destination filename contains invalid characters: [" + destinationArray[i] + "]");
//			}
			xml += ' <File Source="'+source+'" Destination="'+destination+'" RegionId="46" />\n';
		}
		xml += '</DeviceDownload>';
		return xml;
	},
	
	buildDeviceDownloadXML: function(source, destination) {
		return Garmin.GpiUtil.buildMultipleDeviceDownloadsXML([source, destination]);
	},
	
	isDestinationValid: function(destination) {
		var splitPath = destination.split("/");
		var filename = splitPath[splitPath.length-1];

		var lengthBefore = filename.length;
		
		var stringAfter = Garmin.GpiUtil.cleanUpFilename(filename);
		
		return(lengthBefore == stringAfter.length);
	},
	
	cleanUpFilename: function(filename) {
		var result = filename;

		var replacement = "";						// see http://www.asciitable.com/
		result = result.stripTags();
		result = result.replace(/&amp;/, replacement);
		result = result.replace(/[\x21-\x2F]/g, replacement); 	// using range "!" through "/"
		result = result.replace(/[\x5B-\x60]/g, replacement);	// using range "[" through "`"
		result = result.replace(/[\x3A-\x40]/g, replacement);	// using range ":" through "@"
		result = result.strip();
		
		return result;
	}
};