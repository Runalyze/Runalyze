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
 * @fileoverview GarminDeviceControlDemo Demonstrates Garmin.DeviceControl.
 * 
 * @author Michael Bina michael.bina.at.garmin.com
 * @version 1.0
 */
var GarminDeviceControlDemo = Class.create();
GarminDeviceControlDemo.prototype = {

	initialize: function(statusDiv, mapId, keysArray) {        
        this.status = $(statusDiv);
        this.mc = new Garmin.MapController(mapId);
        this.factory = null;
        this.keys = keysArray;
        
        this.findDevicesButton = $("findDevicesButton");
        this.cancelFindDevicesButton = $("cancelFindDevicesButton");
        this.deviceSelect = $("deviceSelect");
        this.deviceInfo = $("deviceInfoText");

		this.fileTypeSelect=$("fileTypeSelect");
        this.readDataButton = $("readDataButton");
        this.cancelReadDataButton = $("cancelReadDataButton");
        this.readTracksText = $("readTracksText");
        this.readRoutesSelect = $("readRoutesSelect");
        this.readTracksSelect = $("readTracksSelect");
        this.readWaypointsSelect = $("readWaypointsSelect");
        this.dataString = $("dataString");
        this.compressedDataString = $("compressedDataString");

        this.writeDataButton = $("writeDataButton");
        this.cancelWriteDataButton = $("cancelWriteDataButton");
        this.writeDataSelect = $("writeDataSelect");
		this.writeDataText = $("writeDataText");
		this.writeDataFilename = $("writeDataFilename");

		this.progressBar = $("progressBar");
		this.progressBarDisplay = $("progressBarDisplay");

		this.activityListing = $("activityListing");
		this.readSelectedButton = $("readSelectedButton");
		
		this.checkAllBox = $("checkAllBox"); // Checkbox used to check/select all directory checkboxes.
		
		this.garminController = null;
		this.intializeController();
		
		this.activityDirectory = null; // Array of activity ID strings in the directory
		this.activitySelection = null; // Array of selected activity objects in the directory
		this.activityQueue = null; // Queue of activity IDs to sync events
		this.fileTypeRead = this.fileTypeSelect.value;
		
		if(this.garminController && this.garminController.isPluginInitialized()) {
	        this.findDevicesButton.disabled = false;
	        this.findDevicesButton.onclick = function() {
	        	this.findDevicesButton.disabled = true;
	        	this.cancelFindDevicesButton.disabled = false;
	        	this.garminController.findDevices();
	        }.bind(this)
		}		
	},
	
	intializeController: function() {
		try {
			this.garminController = new Garmin.DeviceControl();
			this.garminController.register(this);
			
			if(this.garminController.unlock(this.keys)) {
	        	this.setStatus("Plug-in initialized.  Find some devices to get started.");
			} else {
	        	this.setStatus("The plug-in was not unlocked successfully.");
	        	this.garminController = null;
			}
		} catch (e) { this.handleException(e); }
	},

	showProgressBar: function() {
		Element.show(this.progressBar);
	},

	hideProgressBar: function() {
		Element.hide(this.progressBar);
	},

	updateProgressBar: function(value) {
		if (value) {
			var percent = (value <= 100) ? value : 100;
	    	this.progressBarDisplay.style.width = percent + "%";
		}
	},

    onStartFindDevices: function(json) {
        this.setStatus("Looking for connected Garmin devices");
    },

    onFinishFindDevices: function(json) {
    	try {
	       	this.findDevicesButton.disabled = false;
	       	this.cancelFindDevicesButton.disabled = true;
	
	        if(json.controller.numDevices > 0) {
	            var devices = json.controller.getDevices();
	            this.setStatus("Found " + devices.length + " devices.");
	
				this.listDevices(devices);
				
		        this.cancelReadDataButton.onclick = function() {
		        	this.fileTypeSelect.disabled = false;
		        	this.readDataButton.disabled = false;
		        	this.cancelReadDataButton.disabled = true;
		        	this.writeDataButton.disabled = false;
		        	this.hideProgressBar();
		        	this.garminController.cancelReadFromDevice();
		        }.bind(this)
				
				this.fileTypeSelect.disabled = false;	        
		        this.readDataButton.disabled = false;
		        this.readDataButton.onclick = function() {	
		        	this.activities = null;
			    	this.readTracksSelect.length = 0;	
			    	this.readWaypointsSelect.length = 0;
			    	this.readRoutesSelect.length = 0;
					this.mc.map.clearOverlays();
		        	this.fileTypeSelect.disabled = true;
		        	this.readDataButton.disabled = true;
		        	this.cancelReadDataButton.disabled = false;
		        	this.showProgressBar();
		        	this.readSelectedButton.disabled = true;
		        	this.checkAllBox.disabled = true;
		        	this._clearActivityListing();
		        	
		        	try {
		        	    // Leave as specific type calls in order to test both generic and specific
			        	switch( this.fileTypeRead ) {
			        		case Garmin.DeviceControl.FILE_TYPES.gpx :
			        			this.garminController.readFromDevice();
				        		break;
				        	case Garmin.DeviceControl.FILE_TYPES.tcx :
			        			this.garminController.readHistoryFromFitnessDevice();
			        			break;
			        		case Garmin.DeviceControl.FILE_TYPES.crs :
			        			this.garminController.readCoursesFromFitnessDevice();
			        			break;
			        		case Garmin.DeviceControl.FILE_TYPES.tcxDir :
			        		case Garmin.DeviceControl.FILE_TYPES.crsDir :
			        			this.garminController.readDataFromDevice(this.fileTypeRead);
			        			break;
			        		case Garmin.DeviceControl.FILE_TYPES.wkt :
			        			this.garminController.readWorkoutsFromFitnessDevice();
			        			break;
			        		case Garmin.DeviceControl.FILE_TYPES.tcxProfile :
			        			this.garminController.readUserProfileFromFitnessDevice();
			        			break;
			        		case Garmin.DeviceControl.FILE_TYPES.goals :
			        			this.garminController.readGoalsFromFitnessDevice();
			        			break;
			        	}
		        	} catch (e) { this.handleException(e); }
		        	
		        	this.writeDataButton.disabled = false;
		        	this.checkAllBox.disabled = false;
		        	
		       	}.bind(this)
		        
		        this.writeDataSelect.disabled = false;
				this.writeDataSelect.onchange = function() {
					this.loadWriteData(this.writeDataSelect.value);
				}.bind(this)
				this.loadWriteData(this.writeDataSelect.value);
	
		        this.cancelWriteDataButton.onclick = function() {
		        	this.writeDataButton.disabled = false;
		        	this.cancelWriteDataButton.disabled = true;
		        	this.hideProgressBar();
		        	this.garminController.cancelWriteToDevice();
		        }.bind(this)
	
				this.writeDataText.onchange = function() {
					this.setWriteFilename();
				}.bind(this)
				
		        this.writeDataButton.disabled = false;	        
		        this.writeDataButton.onclick = function() {
		        	this.writeDataButton.disabled = true;
		        	this.cancelWriteDataButton.disabled = false;
		        	this.showProgressBar();
					
					try {
					    // Leave as specific type calls in order to test both generic and specific
						switch(this.garminController.gpsDataType) {
							case Garmin.DeviceControl.FILE_TYPES.gpx:
								this.garminController.writeToDevice(this.writeDataText.value, this.writeDataFilename.value);
								break;
							case Garmin.DeviceControl.FILE_TYPES.crs:
								this.garminController.writeFitnessToDevice(this.writeDataText.value, this.writeDataFilename.value);
								break;
							case Garmin.DeviceControl.FILE_TYPES.wkt:
								this.garminController.writeWorkoutsToFitnessDevice(this.writeDataText.value, this.writeDataFilename.value);
								break;
							case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
								this.garminController.writeUserProfileToFitnessDevice(this.writeDataText.value, this.writeDataFilename.value);
								break;
							case Garmin.DeviceControl.FILE_TYPES.goals:
								this.garminController.writeGoalsToFitnessDevice(this.writeDataText.value, this.writeDataFilename.value);
								break;
						}
					} catch (e) { this.handleException(e); }
					
		        }.bind(this);
		        
		        this.readSelectedButton.disabled = true;
		        this.readSelectedButton.onclick = function() {
		        	
		        	if( this._directoryHasSelected() == false) {
		        		alert("At least one activity must be selected before attempting to read.");
		        	} else {
			        	this.activities = null;
			        	this.readTracksSelect.length = 0;	
				    	this.readWaypointsSelect.length = 0;
				    	this.readRoutesSelect.length = 0;
						this.mc.map.clearOverlays();
			        	this.fileTypeSelect.disabled = true;
			        	this.readSelectedButton.disabled = true;
			        	this.cancelReadDataButton.disabled = false;
		        	
			        	this.showProgressBar();
	
						if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir) {
							this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.tcxDetail;
						} else if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.crsDir ){
							this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.crsDetail;
						}
						 
						this._populateActivityQueue();
						
						this._readSelectedActivities();
						
			        	this.writeDataButton.disabled = false;
			        	this.checkAllBox.disabled = false;
		        	}
		        }.bind(this);
		        
		        this.checkAllBox.disabled = true;
		        this.checkAllBox.onclick = function() { this._checkAllDirectory(); }.bind(this);
		        
		        this.fileTypeSelect.onchange = function() { this.fileTypeRead = this.fileTypeSelect.value; }.bind(this);
		        
	        } else {
				this.setStatus("No devices found.");
				this.deviceInfo.innerHTML = "";
				this._clearHtmlSelect(this.deviceSelect);
				this.deviceSelect.disabled = true;
	        }
    	} catch (e) { this.handleException(e); }
    },
    
	onCancelFindDevices: function(json) {
    	this.setStatus("Find cancelled");
    },

	listDevices: function(devices) {
		this._clearHtmlSelect(this.deviceSelect);
		for( var i=0; i < devices.length; i++ ) {
           	this.deviceSelect.options[i] = new Option(devices[i].getDisplayName(),devices[i].getNumber());
           	if(devices[i].getNumber() == this.garminController.deviceNumber) {
           		this.deviceSelect.selectedIndex = i;
           		this.showDeviceInfo(devices[i]);
           	}
		}
   		this.deviceSelect.selectedIndex = 0;
       	this.showDeviceInfo(devices[0]);
		this.deviceSelect.onchange = function() {
			var device = this.garminController.getDevices()[this.deviceSelect.value];
			this.showDeviceInfo(device);
			this.garminController.setDeviceNumber(this.deviceSelect.value);
		}.bind(this)
		this.deviceSelect.disabled = false;
	},

	showDeviceInfo: function(device) {
		this.deviceInfo.innerHTML = "Part Number:\t\t" + device.getPartNumber() + "\n";
		this.deviceInfo.innerHTML += "Software Version:\t" + device.getSoftwareVersion() + "\n";
		this.deviceInfo.innerHTML += "Description:\t\t" + device.getDescription() + "\n";
		this.deviceInfo.innerHTML += "Id:\t\t\t" + device.getId() + "\n\n";
		
		var dataTypes = device.getDeviceDataTypes().values();		
		var typeListSize = dataTypes.length;
		for (var i = 0; i < typeListSize; i++) {
			this.deviceInfo.innerHTML += "-DataType---------------\n"
			this.deviceInfo.innerHTML += "  Name:\t\t" + dataTypes[i].getDisplayName() + "\n";
			this.deviceInfo.innerHTML += "  Extension:\t" + dataTypes[i].getFileExtension() + "\n";
			this.deviceInfo.innerHTML += "  Read:\t\t" + dataTypes[i].hasReadAccess() + "\n";
			this.deviceInfo.innerHTML += "  Write:\t" + dataTypes[i].hasWriteAccess() + "\n\n";			
		}
	},

    onProgressReadFromDevice: function(json) {
	  	this.updateProgressBar(json.progress.getPercentage());
    	this.setStatus(json.progress);
    },
    
	onCancelReadFromDevice: function(json) {
    	this.setStatus("Read cancelled");
    },

    onFinishReadFromDevice: function(json) {
    	try {
		    this.setStatus("Processing retrieved data...");
	    	this.fileTypeSelect.disabled = false;
	       	this.readDataButton.disabled = false;
	       	this.cancelReadDataButton.disabled = true;
	       	this.hideProgressBar();
	    	this.dataString.value = json.controller.gpsDataString;
	    	this.compressedDataString.value = json.controller.gpsDataStringCompressed;
	    	
	    	// Factory setting for parsing the data into activities if applicable.
	    	switch(this.fileTypeRead) {
	    		case Garmin.DeviceControl.FILE_TYPES.gpx:
	    			this.factory = Garmin.GpxActivityFactory;
	    			break;
	    		case Garmin.DeviceControl.FILE_TYPES.tcx:
	    		case Garmin.DeviceControl.FILE_TYPES.crs:
	    		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
	    		case Garmin.DeviceControl.FILE_TYPES.crsDir:
	    		case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
	    		case Garmin.DeviceControl.FILE_TYPES.crsDetail:
	    			this.factory = Garmin.TcxActivityFactory;
	    			break;
	    		default:
	    			// No factory for unsupported type.
	    			this.factory = null;
	    			break;
	    	}
	    	
			// parse the data into activities if possible
			if (this.factory != null) {
				
				// Convert the data obtained from the device into activities.
				// If we're starting a new read session, start a new activities array
				if( this.activities == null) {
					this.activities = new Array();
				}
				
				// Populate this.activities
				switch(this.fileTypeRead) {
					case Garmin.DeviceControl.FILE_TYPES.crsDir:
					case Garmin.DeviceControl.FILE_TYPES.tcxDir:
						this.activities = this.factory.parseDocument(json.controller.gpsData);
		    			
		    			if( this.activities != null ) {
			    			// If we read a directory, save the directory for the session
			    			this._createActivityDirectory();
		    			}
						break;
					case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
		    		case Garmin.DeviceControl.FILE_TYPES.crsDetail:
		    			
		    			// Store this read activity
		    			this.activities = this.activities.concat( this.factory.parseDocument(json.controller.gpsData) );
		    			
						// Not finished with the activity queue
				    	if( this.activityQueue.length > 0) {
				    		this._readSelectedActivities();
				    		
				    		// Cleanest way to deal with the js single-thread issue for now.
				    		// Cutting out to immediately move on to the next activity in the queue before listing.
				    		return;
				    	}
				    	
		    			break;
		    		default:
		    			this.activities = this.factory.parseDocument(json.controller.gpsData);
		    			break;
				}
			}
			
			// Finished reading activities in queue, if any
			if( this.activityQueue == null || this.activityQueue.length == 0 ) {
		    	
		    	if( this.fileTypeRead != Garmin.DeviceControl.FILE_TYPES.tcxDir && this.fileTypeRead != Garmin.DeviceControl.FILE_TYPES.crsDir) {
		    		// List the activities (and display on Google Map)
					if( this.activities != null) {
			    		this.setStatus("Listing activities...");
			    		var summary = this._listActivities(this.activities);
			    		this.setStatus( new Template("Results: #{routes} routes, #{tracks} tracks and  #{waypoints} waypoints found").evaluate(summary) );
					} else {
						this.setStatus("Finished retrieving data.");
					} 
		    	} else {
		    		// List the activity directory
					if( this.activities != null) {
			    		this.setStatus("Listing activity directory...");
			    		var summary = this._listDirectory(this.activities);
			    		this.setStatus( new Template("Results: #{routes} routes, #{tracks} tracks and  #{waypoints} waypoints found").evaluate(summary) );
					} else {
						this.setStatus("Finished retrieving data.");
					} 
		    	}
		    	
		    	// Disable appropriate buttons after read is finished
		    	switch(this.fileTypeRead) {
		    		case Garmin.DeviceControl.FILE_TYPES.gpx:
		    			break;
		    		case Garmin.DeviceControl.FILE_TYPES.tcx:
		    		case Garmin.DeviceControl.FILE_TYPES.crs:
		    			// Display the track selected by default, if there is one.
		    			if( this.readTracksSelect.onchange){
		    				this.readTracksSelect.onchange();
		    			}
		    			break;
		    		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
		    		case Garmin.DeviceControl.FILE_TYPES.crsDir:
		    			this.readSelectedButton.disabled = false;
		    			this.checkAllBox.disabled = false;
		    			break;
		    		case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
		    		case Garmin.DeviceControl.FILE_TYPES.crsDetail:
		    			this.readSelectedButton.disabled = false;
		    			this.checkAllBox.disabled = false;
		    			break;
		    	}
    		}
	    	
    	} catch (e) { this.handleException(e); }
    },
    
    /** Reads the user-selected activities from the device by using the activity queue. 
     */
    _readSelectedActivities: function() {
    	// Pop the selected activity off the queue.  (The queue only holds selected activities)
    	var currentActivity = this.activityQueue.last();
    	this.garminController.readDetailFromDevice(this.fileTypeRead, $(currentActivity).value);
    	this.activityQueue.pop();
    },
    
    _clearActivityListing: function() {
    	//clear previous data, if any (keep the header).  IE deletes header too...
		while(this.activityListing.rows.length > 0) {
			this.activityListing.deleteRow(0);
		}
    },
    
    /** Creates the activity directory of all activities on the device
     * of the user-selected type.  Most recent entries are first.
     */
    _createActivityDirectory: function() {
    	this.activityDirectory = new Array();
    	this.activityQueue = new Array(); // Initialized here so that we can detect activity selection read status
    	
    	for( var jj = 0; jj < this.activities.length; jj++) {
    		
    		this.activityDirectory[jj] = this.activities[jj].getAttribute("activityName");
    	}
    },
    
    /** Creates the activity queue of selected activities.  This should be called
     * only after the user has finished selecting activities.  The queue
     * is an Array that is constructed and then reversed to simulate a queue.
     */
    _populateActivityQueue: function() {
    	
    	for( var jj = 0; jj < this.activityDirectory.length; jj++) {
    		
    		if( $("activityItemCheckbox" + jj).checked == true){
    			this.activityQueue.push("activityItemCheckbox" + jj);
    		}
    	}
    	
    	// Reverse the array to turn it into a queue
    	this.activityQueue.reverse(); 
    },

	/** The activityListing object is the HTML table element on the demo page.  This function
	 * adds the necessary row to the table with the activity data.
	 */
	_addToActivityListing: function(index, activity) {
		
		var selectIndex = 0;
		var nameIndex = 1;
		
		var activityName = activity.getAttribute("activityName");
		
		var row = this.activityListing.insertRow(this.activityListing.rows.length); // append a new row to the table
		var selectCell = row.insertCell(selectIndex);
		var nameCell = row.insertCell(nameIndex);
		
		var checkbox = document.createElement("input");
		checkbox.id = "activityItemCheckbox" + index;
		checkbox.type = "checkbox";
		checkbox.value = activityName;
		
		selectCell.appendChild(checkbox);
		
		if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir) {
			nameCell.innerHTML = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime).getValue().getDateString() 
									+ " (Duration: " + activity.getStartTime().getDurationTo(activity.getEndTime()) + ")"; // Correct time zone
		}
		else if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.crsDir ) {
			nameCell.innerHTML = activityName;
		}
		
	},
	
	/** Selects all checkboxes in the activity directory, which selects all activities to be read from the device.
	 */
	_checkAllDirectory: function() {
		for( var boxIndex=0; boxIndex < this.activityDirectory.length; boxIndex++ ) {
			$("activityItemCheckbox" + boxIndex).checked = this.checkAllBox.checked;
		}
	},
	
	/** Checks if any activities in directory listing are selected.  Returns true if so, false otherwise.
	 */
	_directoryHasSelected: function() {
		for( var boxIndex=0; boxIndex < this.activityDirectory.length; boxIndex++ ) {
			if ( $("activityItemCheckbox" + boxIndex).checked == true) {
				return true;
			}
		}
		
		return false;
	},
	
	/** Lists the directory and returns summary data (# of tracks). 
	 */
	_listDirectory: function(activities) {
		var numOfRoutes = 0;
		var numOfTracks = 0;
		var numOfWaypoints = 0;
		
		// clear existing entries
		this._clearHtmlSelect(this.readRoutesSelect);
		this._clearHtmlSelect(this.readTracksSelect);
    	this._clearHtmlSelect(this.readWaypointsSelect);
		
		// loop through each activity
		for (var i = 0; i < activities.length; i++) {
			var activity = activities[i];
			
			// Directory entry
			if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir || this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.crsDir) {
				this._addToActivityListing(i, activity);
			}
			
			numOfTracks++;
		}
		
		return {routes: numOfRoutes, tracks: numOfTracks, waypoints: numOfWaypoints};
	},
	
	/** List activities and display on Google Map when appropriate.
	 */
   	_listActivities: function(activities) {
		var numOfRoutes = 0;
		var numOfTracks = 0;
		var numOfWaypoints = 0;
		
		// clear existing entries
		this._clearHtmlSelect(this.readRoutesSelect);
		this._clearHtmlSelect(this.readTracksSelect);
    	this._clearHtmlSelect(this.readWaypointsSelect);
		
		// loop through each activity
		for (var i = 0; i < activities.length; i++) {
			var activity = activities[i];
			var series = activity.getSeries();
			
				// loop through each series in the activity
			for (var j = 0; j < series.length; j++) {
				var curSeries = series[j];		
				
				switch(curSeries.getSeriesType()) {
					case Garmin.Series.TYPES.history:
						// activity contains a series of type history, list the track
						this._listTrack(activity, curSeries, i, j);
						numOfTracks++;
						break;
					case Garmin.Series.TYPES.route:
						// activity contains a series of type route, list the route
						this._listRoute(activity, curSeries, i, j);
						numOfRoutes++;
						break;
					case Garmin.Series.TYPES.waypoint:
						// activity contains a series of type waypoint, list the waypoint
						this._listWaypoint(activity, curSeries, i, j);				
						numOfWaypoints++;
						break;
					case Garmin.Series.TYPES.course:
						// activity contains a series of type course, list the coursetrack
						this._listCourseTrack(activity, curSeries, i, j);				
						numOfTracks++;
						break;
				}	
			}
		}
		
		if(numOfRoutes > 0) {
			this.readRoutesSelect.disabled = false;
			this.displayTrack(this.readRoutesSelect.options[this.readRoutesSelect.selectedIndex].value);			
			this.readRoutesSelect.onchange = function() {
				this.displayTrack(this.readRoutesSelect.options[this.readRoutesSelect.selectedIndex].value);
			}.bind(this);
		} else {
			this.readRoutesSelect.disabled = true;
		}
		
		if(numOfTracks > 0) {
			this.readTracksSelect.disabled = false;
			this.displayTrack(this.readTracksSelect.options[this.readTracksSelect.selectedIndex].value);			
			this.readTracksSelect.onchange = function() {
				this.displayTrack(this.readTracksSelect.options[this.readTracksSelect.selectedIndex].value);
			}.bind(this);
		} else {
			this.readTracksSelect.disabled = true;
		}
		
		if(numOfWaypoints > 0) {
			this.readWaypointsSelect.disabled = false;
			this.displayWaypoint(this.readWaypointsSelect.options[this.readWaypointsSelect.selectedIndex].value);			
			this.readWaypointsSelect.onchange = function() {
				this.displayWaypoint(this.readWaypointsSelect.options[this.readWaypointsSelect.selectedIndex].value);
			}.bind(this);
		} else {
			this.readWaypointsSelect.disabled = true;			
		}
		
		return {routes: numOfRoutes, tracks: numOfTracks, waypoints: numOfWaypoints};
	},

    /** Load route names into select UI component.
     * @private
     */    
	_listRoute: function(activity, series, activityIndex, seriesIndex) {
		var routeName = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
		this.readRoutesSelect.options[this.readRoutesSelect.length] = new Option(routeName, activityIndex + "," + seriesIndex);
	},

    /** Load track name into select UI component.
     * @private
     */    
	_listTrack: function(activity, series, activityIndex, seriesIndex) {
		var startDate = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime).getValue();
		var endDate = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.endTime).getValue();
		var trackName = startDate.getDateString() + " (Duration: " + startDate.getDurationTo(endDate) + ")";
		this.readTracksSelect.options[this.readTracksSelect.length] = new Option(trackName, activityIndex + "," + seriesIndex);
	},
	
	/** Load track name into select UI component.
     * @private
     */    
	_listCourseTrack: function(activity, series, activityIndex, seriesIndex) {
		var trackName = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
		this.readTracksSelect.options[this.readTracksSelect.length] = new Option(trackName, activityIndex + "," + seriesIndex);
	},
	

    /** Load waypoint name into select UI component.
     * @private
     */
	_listWaypoint: function(activity, series, activityIndex, seriesIndex) {
		var wptName = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
		this.readWaypointsSelect.options[this.readWaypointsSelect.length] = new Option(wptName, activityIndex + "," + seriesIndex);
	},
    
    /** Draws a simple line on the map using the Garmin.MapController.
     * @param {Select} index - value of select widget. 
     */
    displayTrack: function(index) {
    	index = index.split(",", 2);
    	var activity = this.activities[parseInt(index[0])];
    	var series = activity.getSeries()[parseInt(index[1])];
    	
		this.mc.map.clearOverlays();
    	if( series.findNearestValidLocationSample(0,1) != null ) {
			this.mc.drawTrack(series);
    	}
    },

    /** Draws a point (usualy as a thumb tack) on the map using the Garmin.MapController.
     * @param {Select} index - value of select widget. 
     */
    displayWaypoint: function(index) {
    	index = index.split(",", 2);
    	var activity = this.activities[parseInt(index[0])];
    	var series = activity.getSeries()[parseInt(index[1])];
    	    	
		this.mc.map.clearOverlays();
        this.mc.drawWaypoint(series);
    },
    
	/**Sets the size of the select options to zero which essentially clears it from 
	 * any values.
	 * @private
	 */
    _clearHtmlSelect: function(select) {
		if(select) {
			//select.size = 0;
			select.options.size = 0;
		}
    },

	loadWriteData: function(filepath) {
		new Ajax.Request(filepath, {
			onSuccess: function(resp) {
				this.writeDataText.value = resp.responseText;
				this.setWriteFilename();
			}.bind(this),
			onFailure: function(resp) {
				this.handleException(new Error("Error loading test data: "+filepath));
			}.bind(this)
		});
	},

    onStartWriteToDevice: function(json) { 
    	this.setStatus("Writing data to to the device");
    },

    onCancelWriteToDevice: function(json) { 
    	this.setStatus("Writing cancelled");
    },

    /**
     * The device already has a file with this name on it.  Do we want to override?  1 is yes, 2 is no
     */ 
    onWaitingWriteToDevice: function(json) { 
        if(confirm(json.message.getText())) {
            this.setStatus('Overwriting file');
            json.controller.respondToMessageBox(true);
        } else {
            this.setStatus('Will not be overwriting file');
            json.controller.respondToMessageBox(false);
        }
    },

    onProgressWriteToDevice: function(json) {
	  	this.updateProgressBar(json.progress.getPercentage());
    	this.setStatus(json.progress);
    },

    onFinishWriteToDevice: function(json) {
	    this.hideProgressBar();
    	this.setStatus("Data written to the device.");
	    this.hideProgressBar();
       	this.writeDataButton.disabled = false;
       	this.cancelWriteDataButton.disabled = true;
    },

    onException: function(json) {
	    this.handleException(json.msg);
    },
    
    setWriteFilename: function() {
    	
    	if( this.writeDataText.value.indexOf("<Workouts") != -1 ) {

			this.writeDataFilename.value = "testWorkouts.wkt";
			this.garminController.gpsDataType = Garmin.DeviceControl.FILE_TYPES.wkt;
    	}
    	else if (this.writeDataText.value.indexOf("<Courses") != -1) {
    		
    		this.writeDataFilename.value = "testCourses.tcx";
    		this.garminController.gpsDataType = Garmin.DeviceControl.FILE_TYPES.crs;
    	}
    	else if( this.writeDataText.value.indexOf("<gpx") != -1 ) {

			this.writeDataFilename.value = "test.gpx";
			this.garminController.gpsDataType = Garmin.DeviceControl.FILE_TYPES.gpx;    
    	}
    	else if( this.writeDataText.value.indexOf("<Profile") != -1 ) {

			this.writeDataFilename.value = "testUserProfile.tcx";
			this.garminController.gpsDataType = Garmin.DeviceControl.FILE_TYPES.tcxProfile;    
    	}
    	else if( this.writeDataText.value.indexOf("<ActivityGoals") != -1 ) {

			this.writeDataFilename.value = "testGoal.tcx";
			this.garminController.gpsDataType = Garmin.DeviceControl.FILE_TYPES.goals;    
    	}			
    },

	handleException: function(error) {
		var msg = error.name + ": " + error.message;	
		if (Garmin.PluginUtils.isDeviceErrorXml(error)) {
			msg = Garmin.PluginUtils.getDeviceErrorMessage(error);	
		}
	    this.setStatus(msg);
	    alert(msg);
	},

	setStatus: function(statusText) {
	    this.status.innerHTML = statusText;
	}
};