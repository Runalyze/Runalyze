if (Garmin == undefined) var Garmin = {};
/**
 * Copyright © 2007 Garmin Ltd. or its subsidiaries.
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
 * @fileoverview Garmin.DirectoryFactory - A factory for producing directory objects.
 * 
 * @author Diana Chow diana.chow at garmin.com
 * @version 1.0
 */
/**A factory that can produce a Garmin.Directory object given directory xml.  Currently
 * this is for FIT support, but this may be extended later for other types.
 * @class Garmin.DirectoryFactory
 * @constructor 
 * @requires Garmin.File, Garmin.FileId
 */
Garmin.DirectoryFactory = function(){};
Garmin.DirectoryFactory = {
	
	parseString: function(xmlString) {
		var dirDocument = Garmin.XmlConverter.toDocument(xmlString);		
		return Garmin.DirectoryFactory.parseDocument(dirDocument);		
	},
	
	/* Creates and returns a list of all FIT files from the document, regardless of type. */
	parseDocument: function(dirDocument) {
		
		// Not parseable directory doc
		if( dirDocument.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.directoryListing).length == 0) {
			throw new Error("ERROR: Unable to parse directory document.");
		}
		
		var parsedDocument;
		
		// Files		
		if( dirDocument.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.file).length >= 0) {
			// Complete file
			parsedDocument = Garmin.DirectoryFactory._parseFiles(dirDocument);
		} 
		
		return parsedDocument;
	},
	
	produceString: function(directory) {
		// TODO fill this guy out when we need it
	},
	
	_parseFiles: function(dirDocument) {
		var files = new Array();
		var fileNodes;

		// Grab the activity/course nodes, depending on document		
		fileNodes = dirDocument.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.file);
		
		// loop through all files in the document
		for (var i = 0; i < fileNodes.length; i++) {
			
			// create new file object
			var file = Garmin.DirectoryFactory._parseFile(fileNodes[i], Garmin.DirectoryFactory.SCHEMA_TAGS.file);
			
			// Add the populated file to the list of files.
			files.push(file);
		}
		
		return files;
	},
	
	_parseFile: function(fileNode, fileType) {
		// create new activity object
		var file = new Garmin.File();
		
		// set is directory
		file.setAttribute(Garmin.File.ATTRIBUTE_KEYS.isDirectory, fileNode.getAttribute(Garmin.File.ATTRIBUTE_KEYS.isDirectory));
		
		// set path
		file.setAttribute(Garmin.File.ATTRIBUTE_KEYS.path, fileNode.getAttribute(Garmin.File.ATTRIBUTE_KEYS.path));
		
		// set type
		file.setAttribute(Garmin.File.ATTRIBUTE_KEYS.type, fileNode.getAttribute(Garmin.File.ATTRIBUTE_KEYS.type));

		// set creation time, optional.
		var creationTimeNode = fileNode.getElementsByTagName(Garmin.File.ATTRIBUTE_KEYS.creationTime)[0];
		if( creationTimeNode != null ) {
    		var creationTime = creationTimeNode.childNodes[0].nodeValue;
    		var creationTimeObj = (new Garmin.DateTimeFormat()).parseXsdDateTime(creationTime);
    		file.setAttribute(Garmin.File.ATTRIBUTE_KEYS.creationTime, creationTimeObj);
		}

        // set dom
		file.setAttribute(Garmin.File.ATTRIBUTE_KEYS.dom, fileNode);
		
		// set id - only one id per file	
		// TODO Will there be other id types in the future? probably... 	
		var fitIdNode = fileNode.getElementsByTagName(Garmin.DirectoryFactory.SCHEMA_TAGS.fitId)[0];
		var fitId = Garmin.DirectoryFactory._parseFitId(fitIdNode);
		file.setId(fitId);
		
		return file;
	},
	
	_parseFitId: function(fitIdNode) {
	    var fitId = new Garmin.FileId();
	     
		// set id
		var id = Garmin.DirectoryFactory._tagValue(fitIdNode, Garmin.DirectoryFactory.SCHEMA_TAGS.id);
		if( id != null ) {
		  fitId.setValue(Garmin.DirectoryFactory.SCHEMA_TAGS.id, id);
		}
        // set filetype
		var fileType = Garmin.DirectoryFactory._tagValue(fitIdNode, Garmin.DirectoryFactory.SCHEMA_TAGS.fileType);
		if( fileType != null ) {
		  fitId.setValue(Garmin.DirectoryFactory.SCHEMA_TAGS.fileType, fileType);
		}
        // set manufacturer
		var manufacturer = Garmin.DirectoryFactory._tagValue(fitIdNode, Garmin.DirectoryFactory.SCHEMA_TAGS.manufacturer);
		if( manufacturer != null ) {
		  fitId.setValue(Garmin.DirectoryFactory.SCHEMA_TAGS.manufacturer, manufacturer);
		}
        // set product
		var product = Garmin.DirectoryFactory._tagValue(fitIdNode, Garmin.DirectoryFactory.SCHEMA_TAGS.product);
		if( product != null ) {
		  fitId.setValue(Garmin.DirectoryFactory.SCHEMA_TAGS.product, product);
		}
        // set serial number
		var serialNumber= Garmin.DirectoryFactory._tagValue(fitIdNode, Garmin.DirectoryFactory.SCHEMA_TAGS.serialNumber);
		if( serialNumber != null ) {
		  fitId.setValue(Garmin.DirectoryFactory.SCHEMA_TAGS.serialNumber, serialNumber);
		}
		
		return fitId;
	},
	
	/**
	 * Takes in a list of any file type and returns a list of only activity file types.
	 * @param files {Array} list of Garmin.File objects of any type produced from the factory 
	 */
	getActivityFiles: function(files) {
	    var activityFiles = new Array();
	    
	    for(var i=0; i < files.length; i++) {
	        if( files[i].getIdValue(Garmin.FileId.KEYS.fileType) == Garmin.FileId.FILE_TYPE_MAP.activities){
	            activityFiles.push(files[i]);
	        }
	    }
	    
	    return activityFiles;
	},
	
	/**
	 * Gets the first tag's value under the parent.
	 */
	_tagValue: function(parentNode, tagName) {
		var subNode = parentNode.getElementsByTagName(tagName);
		return subNode.length > 0 ? subNode[0].childNodes[0].nodeValue : null;
	},	
	
    toString: function() {
        return "[DirectoryFactory]";
    }	
};

Garmin.DirectoryFactory.SCHEMA_TAGS = {
	directoryListing:			"DirectoryListing",
	file:                       "File",
	unitId:                     "UnitId",
	fitId:                      "FitId",
	path:                       "Path",
	type:                       "Type",
	id:                         "Id",
	fileType:                   "FileType",
    manufacturer:               "Manufacturer",
    product:                    "Product",
    serialNumber:               "SerialNumber",
    isDirectory:                "IsDirectory"
};