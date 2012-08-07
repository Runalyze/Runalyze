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
 * A high-level UI widget for talking with Garmin Devices.
 * 
 * @fileoverview GarminDeviceDisplay.js  
 * @version 1.9
 */
 
/** Provides the easiest avenue for getting a working instance of the plug-in onto your page.
 * Generates the UI elements and places them on the page.
 *
 * @class Garmin.DeviceDisplay
 * 
 * requires Prototype
 * @requires Garmin.DeviceControl 
 * @param {String|Element} mainElement - id of the element (or DOM element itself) in which to generate the contents
 * @param {Object} options - Object with options (see {@link Garmin.DeviceDisplayDefaultOptions} for descriptions of possible options).
 */
Garmin.DeviceDisplay= function(mainElement, options){}; //just here for jsdoc
Garmin.DeviceDisplay = Class.create();
Garmin.DeviceDisplay.prototype = {

    /** Constructor.
	 * @private
     */
	initialize: function(mainElement, options) {
		if(typeof(mainElement) == "string") {
			this.mainElement = $(mainElement);
		} else {
			this.mainElement = mainElement;
		}
		
		if(this.mainElement != null) {
			this.options = null;
			this.setOptions(options);

			this.garminController = null;
			this.garminRemoteTransfer = null;
			this.activities = new Array();
			this.devices = new Array();
			this.factory = null;
        	this.tracks = null;
	        this.waypoints = null;

			this.activityDirectory = null; 					// Array of activity ID strings in the directory
			this.activityQueue = null; 						// Queue of activity IDs to sync events
			this.numQueuedActivities = null;                // Number of total queued activities for status reporting 
			this.uploadData = null;                         // Payload element for upload data
			this.activityMatcher = null;                    // The activity filter for synchronizing activities
			
			this.currentActivity = null; 					// The top of the activity queue and/or the activity being processed.
			this.finishedFirstActivity = false;
			this.xhr = null;                                // The XHR object (see GarminRemoteTransfer)
			this.advancedUploadMode = true;                 // Internal option to show the activity selection table on upload
			
			this.error = null;
			
			this._generateElements();
			
			this.originalFileTypeRead = null;
			
			if (this.options.unlockOnPageLoad) {
				this.getController(true);
			}
			if (!this.error && this.options.autoFindDevices) {
				this.startFindDevices();
			}
		}
	},

    ////////////////////////// UI GENERATION METHODS ///////////////////////////
    
    /* Primary UI build method.
     * @private  
     */
	_generateElements: function() {
		if (BrowserSupport.isBrowserSupported() || !this.options.hideIfBrowserNotSupported) {
			this._generateStatusElement();
			if(this.options.showFindDevicesElement) {
				this._generateFindDevicesElement();
			}
			if(this.options.showReadDataElement) {
				this._generateReadDataElement();
			}
			if(this.options.showActivityDirectoryElement) {
				this._generateActivityDirectoryElement();	
			}
			if(this.options.showWriteDataElement) {
				this._generateWriteDataElement();
			}
			if(this.options.showSendDataElement) {
				this._generateSendDataElement();
			}
			if(this.options.showAboutElement) {
    			this._generateAboutElement();
			}
			this.resetUI();
		}
	},

    /** Resets UI widgets based on state of application.
     */ 
	resetUI: function() {
	    this.hideProgressBar();
	    
		var noDevicesAvailable = this.garminController ? (this.getController().numDevices==0) : true;
		if(this.options.showFindDevicesElement) {
			if (this.findDevicesButton)
				this.findDevicesButton.disabled = false;
			if (this.deviceSelectInput)		
				this.deviceSelectInput.disabled = noDevicesAvailable;
			if (this.cancelFindDevicesButton)
				this.cancelFindDevicesButton.disabled = true;
			if (this.readDataTypesSelect) 
				this.readDataTypesSelect.disabled = false;
		}
		if(this.options.showReadDataElement) {
			if (this.readDataButton) 
				this.readDataButton.disabled = noDevicesAvailable;
			if (this.cancelReadDataButton)
				this.cancelReadDataButton.disabled = true;
    		if(this.loadingContentElement) {
    		    this.loadingContentElement.hide();
    		}
		}
		if(this.options.showWriteDataElement) {
			if (this.writeDataButton)
				this.writeDataButton.disabled = noDevicesAvailable;
			if (this.cancelWriteDataButton)
				this.cancelWriteDataButton.disabled = true;
		}
	},
	

    /* Build status UI components.
     * @private
     */
	_generateStatusElement: function() {
		this.statusElement = document.createElement("div");
		Element.extend(this.statusElement);
		this.statusElement.id = this.options.statusElementId;
		this.statusElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.statusElement);

		// Status text
		this.statusText = document.createElement("div");
		Element.extend(this.statusText);
		this.statusText.id = this.options.statusTextId;
		this.statusElement.appendChild(this.statusText);

        // Progress bars
        this._generateProgressBars();
	},
	
	/* Build status progress bar UI components.
	 * @private
	 */
	_generateProgressBars: function() {
		// Device transfer progress bar
		this.progressBar = document.createElement("div");
		Element.extend(this.progressBar);
		this.progressBar.id = this.options.progressBarId;
		this.progressBar.className = this.options.progressBarClass;
		this.progressBarBack = document.createElement("div");
		Element.extend(this.progressBarBack);
		this.progressBarBack.id = this.options.progressBarBackId;
		this.progressBarBack.addClassName(this.options.progressBarBackClass);
		this.progressBarBack.innerHTML = '<span/>';
		this.progressBar.appendChild(this.progressBarBack);
		this.progressBarDisplay = document.createElement("div");
		Element.extend(this.progressBarDisplay);
		this.progressBarDisplay.id = this.options.progressBarDisplayId;
		this.progressBarDisplay.addClassName(this.options.progressBarDisplayClass);
		this.progressBarDisplay.innerHTML = '<span/>';
		this.progressBar.appendChild(this.progressBarDisplay);
		this.progressBar.hide();
		this.statusElement.appendChild(this.progressBar);
		
		// Device transfer progress bar text
		this.progressBarText = document.createElement("div");
		Element.extend(this.progressBarText);
		this.progressBarText.id = this.options.progressBarTextId;
		this.progressBarText.className = this.options.progressBarTextClass;
		this.progressBar.appendChild(this.progressBarText);
		
		// Upload progress bar
		this.uploadProgressBar = document.createElement("div");
		Element.extend(this.uploadProgressBar);
		this.uploadProgressBar.id = this.options.uploadProgressBarId;
		this.uploadProgressBar.className = this.options.uploadProgressBarClass;
		this.uploadProgressBarBack = document.createElement("div");
		Element.extend(this.uploadProgressBarBack);
		this.uploadProgressBarBack.id = this.options.uploadProgressBarBackId;
		this.uploadProgressBarBack.addClassName(this.options.uploadProgressBarBackClass);
		this.uploadProgressBarBack.innerHTML = '<span/>';
		this.uploadProgressBar.appendChild(this.uploadProgressBarBack);
		this.uploadProgressBarDisplay = document.createElement("div");
		Element.extend(this.uploadProgressBarDisplay);
		this.uploadProgressBarDisplay.id = this.options.uploadProgressBarDisplayId;
		this.uploadProgressBarDisplay.addClassName(this.options.uploadProgressBarDisplayClass);
		this.uploadProgressBarDisplay.innerHTML = '<span/>';
		this.uploadProgressBar.appendChild(this.uploadProgressBarDisplay);
		this.uploadProgressBar.hide();
		this.statusElement.appendChild(this.uploadProgressBar);
		
		// Upload progress bar text
		this.uploadProgressBarText = document.createElement("div");
		Element.extend(this.uploadProgressBarText);
		this.uploadProgressBarText.id = this.options.uploadProgressBarTextId;
		this.uploadProgressBarText.className = this.options.uploadProgressBarTextClass;
		this.uploadProgressBar.appendChild(this.uploadProgressBarText);
		
		//TODO This is totally the wrong place to put this.  Move this out somewhere.
		this.cancelUploadButton = new Element(this.options.useLinks ? 'div' : 'input', {
		    id: this.options.cancelUploadButtonId,
		    className: this.options.cancelUploadButtonClass
		    });
		if (this.options.useLinks) {
			this.cancelUploadButton.update('<a href="#">'+this.options.cancelUploadButtonText+'</a>');
		} else {
			this.cancelUploadButton.type = "button";
			this.cancelUploadButton.value = this.options.cancelUploadButtonText;
		}
        this.cancelUploadButton.onclick = function() {
        	this.resetUI();
         	this.hideProgressBar();
         	
         	// Kill the string of event handlers
        	this.garminRemoteTransfer.abortRequest();  
    	    
    	    try {
            	// Update the status of the active upload.
                this.options.afterFinishSendData.call(this, 
                    this.xhr, 
                    this.currentActivityStatusElement(),
                    this);
            } catch (error) {
		        this.handleException(error);
	        }
        	
        	// Clear the queue.  Affects cancel and progress.
            this.activityQueue = null;
//         	this.clearActivityQueue();  // Clearing does not work for whatever reason... timing/async
        	
	        // Go to the finished screen
        	this.getController()._broadcaster.dispatch("onFinishUploads", { display: this });
        }.bind(this)
		this.uploadProgressBar.insert(this.cancelUploadButton);
	},
	
	_createElement: function(id, text, type, parent) {
		var elem = document.createElement(type);
		Element.extend(elem);
		if (type=="a") {
			elem.href = location;
			elem.innerHTML = text;
		} else if (type=="button"){
			elem.type = type;
			elem.value = text;
		}
		elem.id = id;
		parent.appendChild(elem);		
		return elem;
	},
	
	/** Build device browser list, a singleton.  This list will be juxtaposed with the activity directory.
	 * This is a different list than the default device select drop down.  It adds on the computer file browser
	 * as well.
	 * @private
	 */
	generateDeviceBrowserElement: function(devices) {
	    
	    if( this.deviceBrowserElement != null) {
	        throw new Error("Unable to generate device browser because an instance already exists.");
	    }
        
        this.deviceBrowserElement = new Element('div', { 
            id: this.options.deviceBrowserElementId,
            className: this.options.deviceBrowserElementClass 
            });
        this.deviceBrowserLabel = new Element('div', { 
            id: this.options.deviceBrowserLabelId,            
            className: this.options.deviceBrowserLabelClass
            }).update(this.options.deviceBrowserLabel);
        this.deviceBrowserElement.insert(this.deviceBrowserLabel);
    
		this.deviceBrowserList = document.createElement("ul");
		Element.extend(this.deviceBrowserList);
		this.deviceBrowserList.id = this.options.deviceBrowserListId;
		
		this.deviceBrowserElement.appendChild(this.deviceBrowserList);
		this.deviceBrowserElement.hide();
		this.mainElement.appendChild(this.deviceBrowserElement);
		
		// Fill up the list with real data
		this._populateDeviceList(this.deviceBrowserList, this.options.afterSelectDevice ? this.options.afterSelectDevice : 
            function(selectedDeviceNumber, devices, deviceXml){
    		    if (this.options.readDataTypes != null) {
            		this.readFromDevice(this.options.readDataTypes);
            	} 
    		});
		
		if( this.options.uploadSelectedActivities && this.options.showBrowseComputer ) {
    		this._generateBrowseComputerElement();
    		
    		// Add My Computer to the list
    		var itemLink;
    	    var listItem;
            listItem = document.createElement("li");
            Element.extend(listItem);
            listItem.className = "unselected";
            itemLink = document.createElement("a");
            Element.extend(itemLink);
            itemLink.href = "#";
            itemLink.innerHTML = this.options.browseComputerLabel;
            itemLink.onclick = function(deviceListElement) {
                this._displayBrowseComputer(deviceListElement);
            }.bind(this, this.deviceBrowserList)
            listItem.appendChild(itemLink);
            this.deviceBrowserList.appendChild(listItem);
		}
	},
	
	/* Displays the Browse Computer element and updates the device list accordingly.
	 * @private
	 * @param deviceListElement {Element}
	 */
	_displayBrowseComputer: function(deviceListElement) {
	    // Mark My Computer as selected
        var browseComputerItem = deviceListElement.childNodes[this.devices.length];
        browseComputerItem.className = "selected";
        
        // Stop any existing reads and hide stuffs
        if(this.getController() && this.isUnlocked()) { // browsing computer does not require valid plugin
            this.getController().cancelReadFromDevice();
        }
        
        // Mark all the rest of the devices as unselected
        if( this.devices != null) {
            for(var j=0; j< this.devices.length; j++) {
               var listItem = deviceListElement.childNodes[j];
               listItem.className = "unselected";
            }
        }
        
        // The callback function has to take the parameter!  Even if it ignores it.
        this.activityDirectoryElement.hide();
        this.statusElement.hide();
        this.browseComputerElement.show();
	},
	
	/* Generates the browse computer element, an iframe that contains
	 * the manual upload page.
	 * @private 
	 */
	_generateBrowseComputerElement: function() {
	    this.browseComputerElement = document.createElement("div");
        Element.extend(this.browseComputerElement);
        this.browseComputerElement.id = this.options.browseComputerElementId;
        this.browseComputerElement.className = this.options.browseComputerElementClass;
		
		var browseComputerTitle = document.createElement("div");
		browseComputerTitle.id = 'manualUploadTitle';
		browseComputerTitle.innerHTML = this.options.browseComputerLabel;
		this.browseComputerElement.appendChild(browseComputerTitle);
		
		var browseComputerContent = document.createElement("iframe");
		Element.extend(browseComputerContent);
		browseComputerContent.id = browseComputerContent.name = this.options.browseComputerElementId + 'Contents';
		browseComputerContent.src = this.options.browseComputerContentUrl;
		
		this.browseComputerElement.appendChild(browseComputerContent);
		this.browseComputerElement.hide();
		this.mainElement.appendChild(this.browseComputerElement);
		
		// Have to set these after append for IE :E
		// This doesn't work anyway!! I hate IE!!
		browseComputerContent.setAttribute('frameborder', '0'); 
		browseComputerContent.setAttribute('allowtransparency', 'true');
	},
	
	/* Generates the loading screen as the passed in element is loading.
	 * @private 
	 */
	_generateLoadingContent: function(loadingElement) {
	    if( this.loadingContentElement != null) {
	        throw new Error("Unable to generate loading screen because an instance already exists.");
	    }
	    // 'Loading' display
        this.loadingContentElement = document.createElement("div");
        Element.extend(this.loadingContentElement);
        this.loadingContentElement.className = "shortStatus";
        if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData)
        	this.loadingContentElement.innerHTML = this.evaluateTemplate(this.options.loadingHealthContentText, {deviceName:this.getShortDeviceName(this.getCurrentDevice())});
        else
        	this.loadingContentElement.innerHTML = this.evaluateTemplate(this.options.loadingContentText, {deviceName:this.getShortDeviceName(this.getCurrentDevice())});
        loadingElement.appendChild(this.loadingContentElement);
        
        this.showProgressBar();
	},
	
	/* Update the content inside of the loading content element and display it.
     * @private 
	 */
	_updateLoadingContent: function(content) {
	    // Update the device name displayed
	    if(this.loadingContentElement != null) {
            this.loadingContentElement.update(content);
            this.loadingContentElement.show();
	    }
	},
	
    /* Build find device UI components.
     * @private 
     */
	_generateFindDevicesElement: function() {
		this.findDevicesElement = document.createElement("div");
		Element.extend(this.findDevicesElement);
		this.findDevicesElement.id = this.options.findDevicesElementId;
		this.findDevicesElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.findDevicesElement);

		// Find devices button
		if( this.options.showFindDevicesButton) {
			this.findDevicesButton = document.createElement( this.options.useLinks ? "div" : "input" );
			Element.extend(this.findDevicesButton);
			if (this.options.useLinks) {
				this.findDevicesButton.innerHTML = '<a href="#">'+this.options.findDevicesButtonText+'</a>';
			} else {
				this.findDevicesButton.type = "button";
				this.findDevicesButton.value = this.options.findDevicesButtonText;
			}
			this.findDevicesButton.id = this.options.findDevicesButtonId;
			this.findDevicesButton.addClassName(this.options.actionButtonClassName);
			this.findDevicesElement.appendChild(this.findDevicesButton);		
	        this.findDevicesButton.onclick = function() {
	        	this.startFindDevices();
	        }.bind(this)
		}
		
		if(!this.options.showFindDevicesElementOnLoad) {
			if( this.findDevicesElement) {
				Element.hide(this.findDevicesElement);
			}
		}

		// Cancel Find devices button
		if (this.options.showCancelFindDevicesButton) {
			this.cancelFindDevicesButton = document.createElement( this.options.useLinks ? "div" : "input" );
			Element.extend(this.cancelFindDevicesButton);
			if (this.options.useLinks) {
				this.cancelFindDevicesButton.innerHTML = '<a href="#">'+this.options.cancelFindDevicesButtonText+'</a>';
			} else {
				this.cancelFindDevicesButton.type = "button";
				this.cancelFindDevicesButton.value = this.options.cancelFindDevicesButtonText;
			}
			this.cancelFindDevicesButton.id = this.options.cancelFindDevicesButtonId;
			this.cancelFindDevicesButton.addClassName(this.options.actionButtonClassName);
			this.cancelFindDevicesButton.disabled = true;
	        this.cancelFindDevicesButton.onclick = function() {
	        	this.cancelFindDevices();
	        }.bind(this)
			this.findDevicesElement.appendChild(this.cancelFindDevicesButton);
		}
		
		if (!this.options.showDeviceButtonsOnLoad) {
			if (this.findDevicesButton) {
				Element.hide(this.findDevicesButton);
			}
			if (this.cancelFindDevicesButton)				
				Element.hide(this.cancelFindDevicesButton);			
		}

		// Device select drop-down list
		this.deviceSelectElement = document.createElement("div");
		Element.extend(this.deviceSelectElement);
		this.deviceSelectElement.id = this.options.deviceSelectElementId;
		this.deviceSelectElement.innerHTML = '<div id="' + this.options.deviceSelectLabelId + '">' + this.options.deviceSelectLabel + '</div>';
		this.findDevicesElement.appendChild(this.deviceSelectElement);

		this.deviceSelectInput = document.createElement( this.options.useDeviceSelectList ? "ul" : "select");
		Element.extend(this.deviceSelectInput);
		this.deviceSelectInput.id = this.options.deviceSelectId;
		this.deviceSelectInput.disabled = true;
		
		if (!this.options.showDeviceSelectOnLoad || !this.options.showDeviceSelectOnSingle || this.options.autoSelectFirstDevice) {
			Element.hide(this.deviceSelectElement);	
		}
		
		/* Browse computer */
        this.browseComputerButton = new Element(this.options.useLinks ? "div" : "input", {
            id: this.options.browseComputerButtonId
            , className: this.options.browseComputerButtonClass
            });
        this.browseComputerButton.onclick = function(){
            if( this.deviceBrowserList == null) {
                this.generateDeviceBrowserElement(this.devices)
            };
            this.findDevicesElement.hide();
            this.readDataElement.hide();
            this.deviceBrowserElement.show();         
            this._displayBrowseComputer(this.deviceBrowserList);
          }.bind(this)
        if (this.options.useLinks) {
			this.browseComputerButton.innerHTML = '<a href="#" title="' + this.options.browseComputerButtonTitleText + '">'+this.options.browseComputerButtonText+'</a>';
		} else {
			this.browseComputerButton.type = "button";
			this.browseComputerButton.value = this.options.browseComputerButtonText;
		}
        if(!this.options.uploadSelectedActivities || !this.options.showBrowseComputer ) {
            this.browseComputerButton.hide();
        }
		this.findDevicesElement.appendChild(this.browseComputerButton);
	},
	
	_generateSendDataElement: function() {
		this.sendDataElement = document.createElement("div");
		Element.extend(this.sendDataElement);
		this.sendDataElement.id = this.options.sendDataElementId;
		this.sendDataElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.sendDataElement);

		this.sendDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.sendDataButton);
		if (this.options.useLinks) {
			this.sendDataButton.innerHTML = '<a href="#">'+this.options.sendDataButtonText+'</a>';
		} else {
			this.sendDataButton.type = "button";
			this.sendDataButton.value = this.options.sendDataButtonText;
		}
		this.sendDataButton.id = this.options.sendDataButtonId;
		this.sendDataButton.addClassName(this.options.actionButtonClassName);
        this.sendDataButton.onclick = function() {
        	this.setStatus(    
        	   this.evaluateTemplate(
        	       this.options.sendingDataToServer, 
        	       {deviceName:this.getShortDeviceName(this.getCurrentDevice())}
        	   )
	        );
        	Element.hide(this.findDevicesElement);
	        Element.hide(this.sendDataElement);
        	this.showProgressBar();
	        
	        setTimeout(function(){this.postToServer()}.bind(this),1000);
	        return false;
        }.bind(this)
		this.sendDataElement.appendChild(this.sendDataButton);
		
		if(this.options.showSendDataElementOnDeviceFound) {
			Element.hide(this.sendDataElement);
		}	
	},
	
	/** Post data to an external server.  Request options should be provided by the 
	 * parameters element in {@link Garmin.DeviceDisplayDefaultOptions.sendDataOptions} 
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.sendDataOptions 
	 * @see Garmin.DeviceDisplay#handleException
	 * @version 1.6
	 * @param {function} callback function executed after onSuccess of the AJAX request.  If failure, exception is thrown {@link Garmin.DeviceDisplay#handleException}
	 */
	postToServer: function(callback) {
    	var error;
    	var exceptionName = 'RemoteTransferException';
    	
    	// getSendOptions overwrites those already set in sendDataOptions
    	if (this.options.sendDataOptions != null) {
    	    if (this.options.getSendOptions != null) {
    	        this.options.sendDataOptions = this.options.getSendOptions.call(this, this.options.sendDataOptions, this.garminController.getCurrentDeviceXml(), this.readDataString);
    	    }
		}
    	
		this.options.sendDataOptions.onSuccess = function(xhr) {
			this.xhr = xhr;
			if( this.options.afterFinishSendData != null) {
				try {
					this.options.afterFinishSendData.call(this, 
						this.xhr, 
						this.currentActivityStatusElement(),
						this.activityDirectory, 
						this);
				} catch (error) {
					this.handleException(error);
				}
			}
			callback.call(this);
		}.bind(this);
		
		this.options.sendDataOptions.onComplete = function(xhr) {
			// Cross domain...
			if( xhr == null) {
				error = new Error(Garmin.RemoteTransfer.MESSAGES.generalException);
				error.name = exceptionName;
				error.xhr = xhr;
				this.handleException(error);
				throw new Error(Garmin.RemoteTransfer.MESSAGES.noResponseException);
			}
		}.bind(this);
		
		this.options.sendDataOptions.onFailure = function(xhr) {
			error = new Error(xhr.statusText);
			error.name = exceptionName;
			error.xhr = xhr;
			this.handleException(error);
		}.bind(this);
		
		// Make the request
		this.apiResponse = this.garminRemoteTransfer.openRequest(this.options.sendDataUrl, this.options.sendDataOptions);
	},
	
    /* Build read data UI components.
     * @private
     */
	_generateReadDataElement: function() {
		this.readDataElement = document.createElement("div");
		Element.extend(this.readDataElement);
		this.readDataElement.id = this.options.readDataElementId;
		this.readDataElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.readDataElement);

		this.readDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.readDataButton);
		if (this.options.useLinks) {
			this.readDataButton.innerHTML = '<a href="#" title="' + this.options.readDataButtonTitleText + '">'+this.options.readDataButtonText+'</a>';
		} else {
			this.readDataButton.type = "button";
			this.readDataButton.value = this.options.readDataButtonText;
		}
		this.readDataButton.id = this.options.readDataButtonId;
		this.readDataButton.addClassName(this.options.actionButtonClassName);
		this.readDataButton.disabled = true;
        this.readDataButton.onclick = function() {
            var isSupportedDevice = true;
        	if( this.options.restrictByDevice.length > 0){
				isSupportedDevice = this._restrictByDevice();
			}						
			
			if( isSupportedDevice) {
            	if( this.options.autoHideUnusedElements ) {
            		if(this.findDevicesElement) Element.hide(this.findDevicesElement);
            		if(this.readDataElement) Element.hide(this.readDataElement);
            		if(this.deviceSelectElement) Element.hide(this.deviceSelectElement);
            		if(this.activityDirectoryElement) Element.hide(this.activityDirectoryElement);
            	}
            	this.readDataButton.disabled = true;
            	this.cancelReadDataButton.disabled = false;
            	this.showProgressBar();
            	if (this.options.showReadDataTypesSelect) {
            		this.readSpecificTypeFromDevice(this.readDataTypesSelect.value);
            	} else if (this.options.readDataTypes != null) {				
					this.readFromDevice(this.options.readDataTypes);					
            	} else {
					this.readFromDevice(new Array(this.options.readDataType));
				}
			}
        }.bind(this)
		this.readDataElement.appendChild(this.readDataButton);
		if(!this.options.showReadDataButton) {
            Element.hide(this.readDataButton);
		}

		this.cancelReadDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.cancelReadDataButton);
		if (this.options.useLinks) {
			this.cancelReadDataButton.innerHTML = '<a href="#">'+this.options.cancelReadDataButtonText+'</a>';
		} else {
			this.cancelReadDataButton.type = "button";
			this.cancelReadDataButton.value = this.options.cancelReadDataButtonText;
		}
		this.cancelReadDataButton.id = this.options.cancelReadDataButtonId;
		this.cancelReadDataButton.addClassName(this.options.actionButtonClassName);
		this.cancelReadDataButton.disabled = true;
        this.cancelReadDataButton.onclick = function() {
        	this.resetUI();
         	this.hideProgressBar();
        	this.getController().cancelReadFromDevice();
        }.bind(this)
		this.readDataElement.appendChild(this.cancelReadDataButton);

		if(!this.options.showCancelReadDataButton) {
			Element.hide(this.cancelReadDataButton);
		}
		
		/* Upload without showing selection table */
		this.uploadNewButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.uploadNewButton);
		if (this.options.useLinks) {
			this.uploadNewButton.innerHTML = '<a href="#" title="' + this.options.uploadNewButtonTitleText + '">'+this.options.uploadNewButtonText+'</a>';
		} else {
			this.uploadNewButton.type = "button";
			this.uploadNewButton.value = this.options.uploadNewButtonText;
		}
		this.uploadNewButton.id = this.options.uploadNewButtonId;
		this.uploadNewButton.addClassName(this.options.actionButtonClassName);
        this.uploadNewButton.onclick = function() {
			
			var isSupportedDevice = true;
        	if( this.options.restrictByDevice.length > 0){
				isSupportedDevice = this._restrictByDevice();
			}						
			
			if( isSupportedDevice) {
	        	if( this.options.autoHideUnusedElements ) {
                    this.advancedUploadMode = false;
	        		this.readDataElement.hide();
	        		this.browseComputerButton.hide();
	        		this.uploadProgressBar.hide();
	        		if(this.changeDeviceElement != null) { this.changeDeviceElement.hide(); }
	        		if(this.connectedDevices != null) { this.connectedDevices.hide(); }
	        		if(this.deviceSelectInput != null) {this.deviceSelectInput.hide();}
	        		
	        		if( this.loadingContentElement == null) {
                        this._generateLoadingContent(this.statusElement);
                        this.loadingContentElement.className = "longStatus";
                        this.progressBar.className = "longProgressBar";
                        this.progressBarText.className = "longProgressText";
	        		}
	        	}
	        	this.readDataButton.disabled = true;
	        	this.cancelReadDataButton.disabled = false;
	        	
	        	if (this.options.showReadDataTypesSelect) {
	        		this.readSpecificTypeFromDevice(this.readDataTypesSelect.value);
	        	} else if (this.options.readDataTypes != null) {					
            	    this.readFromDevice(this.options.readDataTypes);
            	} else {
					this.readFromDevice(new Array(this.options.readDataType));
				} 	        	
			}
        }.bind(this)
		this.readDataElement.appendChild(this.uploadNewButton);

		if(!this.options.showUploadNewButton) {
			Element.hide(this.uploadNewButton);
		}
		
		/* Upload Health Data */
		this.uploadHealthDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.uploadHealthDataButton);
		if (this.options.useLinks) {
			this.uploadHealthDataButton.innerHTML = '<a href="#" title="' + this.options.uploadHealthDataButtonTitleText + '">' + this.options.uploadHealthDataButtonText + '</a>';
		} else {
			this.uploadHealthDataButton.type = "button";
			this.uploadHealthDataButton.value = this.options.uploadHealthDataButtonText;
		}
		this.uploadHealthDataButton.id = this.options.uploadHealthDataButtonId;
		this.uploadHealthDataButton.addClassName(this.options.actionButtonClassName);
        this.uploadHealthDataButton.onclick = function() {
			
			var isSupportedDevice = true;
        	if( this.options.restrictByDevice.length > 0){
				isSupportedDevice = this._restrictByDevice();
			}						
			
			if( isSupportedDevice) {
	        	if( this.options.autoHideUnusedElements ) {
                    this.advancedUploadMode = false;
	        		this.uploadNewButton.hide();
	        		this.readDataElement.hide();
	        		this.browseComputerButton.hide();
	        		this.uploadProgressBar.hide();
	        		if(this.changeDeviceElement != null) { this.changeDeviceElement.hide(); }
	        		if(this.connectedDevices != null) { this.connectedDevices.hide(); }
	        		if(this.deviceSelectInput != null) {this.deviceSelectInput.hide();}
	        		
	        		if( this.loadingContentElement == null) {
                        this._generateLoadingContent(this.statusElement);
                        this.loadingContentElement.className = "longStatus";
                        this.progressBar.className = "longProgressBar";
                        this.progressBarText.className = "longProgressText";
	        		}
	        	}
	        	this.uploadNewButton.disabled = true;
	        	this.readDataButton.disabled = true;
	        	this.cancelReadDataButton.disabled = false;
	        	
	        	this.readFromDevice(new Array(Garmin.DeviceControl.FILE_TYPES.fitHealthData));      	
			}
        }.bind(this)
		this.readDataElement.appendChild(this.uploadHealthDataButton);

		if(!this.options.showHealthDataUploadButton) {
			Element.hide(this.uploadHealthDataButton);
		}

		if(this.options.showReadDataTypesSelect) {
			this.readDataTypesSelect = document.createElement("select");
			Element.extend(this.readDataTypesSelect);
			this.readDataTypesSelect.id = this.options.readDataTypeSelectId;
			this.readDataTypesSelect.disabled = true;
			this.readDataElement.appendChild(this.readDataTypesSelect);

			// TODO: need a more elegant way of adding options
			this.readDataTypesSelect.options[0] = new Option(this.options.gpsData, Garmin.DeviceControl.FILE_TYPES.gpx);
			this.readDataTypesSelect.options[1] = new Option(this.options.trainingData, Garmin.DeviceControl.FILE_TYPES.tcx);			
		}
		
		if(this.options.showReadRoutesSelect) {
			this.readRoutesElement = document.createElement("div");
			Element.extend(this.readRoutesElement);
			this.readRoutesElement.id = this.options.readRoutesElementId;
			this.readRoutesElement.addClassName(this.options.readResultsElementClass);
			this.readRoutesElement.innerHTML = "<span id=\"" + this.options.readRoutesSelectLabelId + "\">" + this.options.readRoutesSelectLabel + "</span>";

			this.readRoutesSelect = document.createElement("select");
			Element.extend(this.readRoutesSelect);
			this.readRoutesSelect.id = this.options.readRoutesSelectId;
			this.readRoutesSelect.addClassName(this.options.readResultsSelectClass);
			this.readRoutesSelect.disabled = true;
			this.readRoutesSelect.onchange = function() {
				this.displayTrack( this._seriesFromSelect(this.readRoutesSelect) );
			}.bind(this);
			this.readRoutesElement.appendChild(this.readRoutesSelect);
			this.readDataElement.appendChild(this.readRoutesElement);
			
			if(!this.options.showReadResultsSelectOnLoad) {
				Element.hide(this.readRoutesElement);
			}
		}

		if(this.options.showReadTracksSelect) {
			this.readTracksElement = document.createElement("div");
			Element.extend(this.readTracksElement);
			this.readTracksElement.id = this.options.readTracksElementId;
			this.readTracksElement.addClassName(this.options.readResultsElementClass);
			this.readTracksElement.innerHTML = "<span id=\"" + this.options.readTracksSelectLabelId + "\">" + this.options.readTracksSelectLabel + "</span>";

			this.readTracksSelect = document.createElement("select");
			Element.extend(this.readTracksSelect);
			this.readTracksSelect.id = this.options.readTracksSelectId;
			this.readTracksSelect.addClassName(this.options.readResultsSelectClass);
			this.readTracksSelect.disabled = true;
			this.readTracksSelect.onchange = function() {
				this.displayTrack( this._seriesFromSelect(this.readTracksSelect) );
			}.bind(this);
			this.readTracksElement.appendChild(this.readTracksSelect);
			this.readDataElement.appendChild(this.readTracksElement);
			
			if(!this.options.showReadResultsSelectOnLoad) {
				Element.hide(this.readTracksElement);
			}
		}
		
		if(this.options.showReadWaypointsSelect) {
			this.readWaypointsElement = document.createElement("div");
			Element.extend(this.readWaypointsElement);
			this.readWaypointsElement.id = this.options.readWaypointsElementId;
			this.readWaypointsElement.addClassName(this.options.readResultsElementClass);
			this.readWaypointsElement.innerHTML = "<span id=\"" + this.options.readWaypointsSelectLabelId + "\">" + this.options.readWaypointsSelectLabel + "</span>";

			this.readWaypointsSelect = document.createElement("select");
			Element.extend(this.readWaypointsSelect);
			this.readWaypointsSelect.id = this.options.readWaypointsSelectId;
			this.readWaypointsSelect.addClassName(this.options.readResultsSelectClass);
			this.readWaypointsSelect.disabled = true;
			this.readWaypointsSelect.onchange = function() {
				this.displayWaypoint( this._seriesFromSelect(this.readWaypointsSelect) );
			}.bind(this);
			this.readWaypointsElement.appendChild(this.readWaypointsSelect);
			this.readDataElement.appendChild(this.readWaypointsElement);
			
			if(!this.options.showReadResultsSelectOnLoad) {
				Element.hide(this.readWaypointsElement);
			}
		}
		
		// Read Tracks Google Map
		if(this.options.showReadGoogleMap) {
			this.readGoogleMap = document.createElement("div");
			Element.extend(this.readGoogleMap);
			this.readGoogleMap.id = this.options.readGoogleMapId;
			this.readGoogleMap.addClassName(this.options.readResultsElementClass);
			this.readDataElement.appendChild(this.readGoogleMap);			
			this.readMapController = new Garmin.MapController(this.options.readGoogleMapId);
		}
		
		if(this.options.showReadDataElementOnDeviceFound) {
			Element.hide(this.readDataElement);
		}
	},
	
	doesDeviceSupportDataType: function(dataType)
	{
		var result = false;
		
		var deviceNumber = this.getController().deviceNumber;
		if(deviceNumber != null)
		{
			var device = this.getController().getDevices()[deviceNumber];
			
			if(device.supportDeviceDataTypeRead(dataType))
				result = true;
		}
		return result;
	},

    /* Generates the activity directory element.  Only one instance of the directory
     * can exist on a page.
     *  
     * @private 
     */
	_generateActivityDirectoryElement: function() {
	    if( this.activityDirectoryElement != null) {
	        throw new Error("Unable to generate activity directory because an instance already exists.");
	    }
		// Create the container div to hold the directory elements
		this.activityDirectoryElement = document.createElement("div");
		Element.extend(this.activityDirectoryElement);
		this.activityDirectoryElement.id = this.options.activityDirectoryElementId;
		this.activityDirectoryElement.addClassName(this.options.activityDirectoryClass);
		this.activityDirectoryElement.hide();
		
		this.mainElement.appendChild(this.activityDirectoryElement);
	},
	
	_generateActivityTableElement: function() {
		if( this.activityTable != null ) {
		    throw new Error("Unable to generate activity table with id " + this.options.activityTableId + " because an instance already exists.");
		}
		this.activities = null;
		
        this._generateActivityTableHeader();
		
		// Create container div that holds the table data only
		this.activityDirectoryData = document.createElement("div");
		Element.extend(this.activityDirectoryData);
		this.activityDirectoryData.id = this.options.activityDirectoryDataId;
		this.activityDirectoryElement.appendChild(this.activityDirectoryData);
		
		// Create the table
		this.activityTable = document.createElement("table");
		Element.extend(this.activityTable);
		this.activityTable.id = this.options.activityTableId;
		this.activityTable.setAttribute('cellspacing','0');
		this.activityTable.setAttribute('cellpadding','0');
		this.activityDirectoryData.appendChild(this.activityTable);
		
		this.readSelectedButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.readSelectedButton);
		if (this.options.useLinks) {
			this.readSelectedButton.innerHTML = '<a href="#">'+this.options.readSelectedButtonText+'</a>';
		} else {
			this.readSelectedButton.type = "button";
			this.readSelectedButton.value = this.options.readSelectedButtonText;
		}
		this.readSelectedButton.id = this.options.readSelectedButtonId;
		this.readSelectedButton.addClassName(this.options.actionButtonClassName);
		this.readSelectedButton.disabled = false;
        this.readSelectedButton.onclick = function() {
           // Read activities filtered by the API (including user selected activities) 
    	   this.readFilteredActivities();
        }.bind(this);
        this.activityDirectoryElement.appendChild(this.readSelectedButton);
	},
	
	/* Generate the singleton activity table header for the activity directory.
	 * The visible elements in the table are added dynamically after the directory is read.
	 * See _addToActivityTableHeader().
	 * 
	 * @private
	 */
	_generateActivityTableHeader: function() {
	    if( this.activityTableHeader != null) {
	        throw new Error("Unable to generate activity table header: Instance of the activity table header already exists.");
	    }
		
		// Create the table header
		this.activityTableHeader = document.createElement("table");
		Element.extend(this.activityTableHeader);
		this.activityTableHeader.id = this.options.activityTableHeaderId;
		this.activityTableHeader.setAttribute('cellspacing','0');
		this.activityTableHeader.setAttribute('cellpadding','0');
		
		this.activityDirectoryElement.appendChild(this.activityTableHeader);
	},
	
    /* Build write data UI components.
     * @private
     */
	_generateWriteDataElement: function() {
		this.writeDataElement = document.createElement("div");
		Element.extend(this.writeDataElement);
		this.writeDataElement.id = this.options.writeDataElementId;
		this.writeDataElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.writeDataElement);

		if (!this.options.getWriteData && !this.options.getGpiWriteDescription && !this.options.getBinaryWriteDescription)
			throw new Error("Can't write data because getWriteData() function nor getGpiWriteDescription() is defined");
		this.writeDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.writeDataButton);
		if (this.options.useLinks) {
			this.writeDataButton.innerHTML = '<a href="#">'+this.options.writeDataButtonText+'</a>';
		} else {
			this.writeDataButton.type = "button";
			this.writeDataButton.value = this.options.writeDataButtonText;
		}
		this.writeDataButton.id = this.options.writeDataButtonId;
		this.writeDataButton.addClassName(this.options.actionButtonClassName);
		this.writeDataButton.disabled = true;		
        this.writeDataButton.onclick = function() {
            var isSupportedDevice = true;
        	if( this.options.restrictByDevice.length > 0){
				isSupportedDevice = this._restrictByDevice();
			}						
			if( isSupportedDevice) {
            	this.writeDataButton.disabled = true;
            	this.cancelWriteDataButton.disabled = false;
            	if( this.options.autoHideUnusedElements ) {
            	    if(this.findDevicesElement) {
            	        this.findDevicesElement.hide();
            	    }
            	    if(this.writeDataElement) {
            	        this.writeDataElement.hide();
            	    }
            	}
            	this.showProgressBar();
            	this.writeToDevice();
			}
        }.bind(this);
		this.writeDataElement.appendChild(this.writeDataButton);
		
		this.cancelWriteDataButton = document.createElement( this.options.useLinks ? "div" : "input" );
		Element.extend(this.cancelWriteDataButton);
		if (this.options.useLinks) {
			this.cancelWriteDataButton.innerHTML = '<a href="#">'+this.options.cancelWriteDataButtonText+'</a>';
		} else {
			this.cancelWriteDataButton.type = "button";
			this.cancelWriteDataButton.value = this.options.cancelWriteDataButtonText;
		}
		this.cancelWriteDataButton.id = this.options.cancelWriteDataButtonId;
		this.cancelWriteDataButton.addClassName(this.options.actionButtonClassName);
		this.cancelWriteDataButton.disabled = false;
		this.cancelWriteDataButton.onclick = function() {
			this.resetUI();
			this.hideProgressBar();
			this.getController().cancelWriteToDevice();
		}.bind(this);
		this.writeDataElement.appendChild(this.cancelWriteDataButton);
		
		if(!this.options.showCancelWriteDataButton) {
			Element.hide(this.cancelWriteDataButton);
		}

		if(this.options.showWriteDataElementOnDeviceFound) {
			Element.hide(this.writeDataElement);
		}
	},

    /* Build "Powered by" UI components.
     * @private
     */
	_generateAboutElement: function() {
		this.aboutElement = document.createElement("div");
		Element.extend(this.aboutElement);
		this.aboutElement.id = "aboutElement";
		this.aboutElement.addClassName(this.options.elementClassName);
		this.mainElement.appendChild(this.aboutElement);

		this.copyrightText = document.createElement("span");
		this.copyrightText.innerHTML = this.options.poweredByGarmin;
		this.aboutElement.appendChild(this.copyrightText);
	},
	
	/* Checks the connected device against those listed in this.options.restrictByDevice
	 * and throws an error if the connected device is not supported by
	 * the application.  
	 * 
	 * @return true if the connected device is supported, false otherwise.
	 */
	_restrictByDevice: function() {
		
		// Get connected device
		var device = this.getController().getDevices()[this.deviceSelectInput.value];
		var devicePartNumber = device.getPartNumber();
		
		var isSupportedDevice = false;
		// Compare to restricted list
		for(var i=0; i < this.options.restrictByDevice.length; i++) {
		    var supportedPartNumber = this.options.restrictByDevice[i];
			if(devicePartNumber == supportedPartNumber) {
				isSupportedDevice = true;
			}
		}
		
		// Hide everything but status
		if( !isSupportedDevice ) {
			error = new Error(this.options.unsupportedDevice);
    	    error.name = "UnsupportedDeviceException";
			this.handleException(error);
		}
		
		return isSupportedDevice;
	},

    ////////////////////////// FIND DEVICES METHODS ////////////////////////// 
    
    /** Entry point for searching for connected devices.
     * Will attempt to unlock the plugin if necessary.
     * @see Garmin.DeviceDisplay#cancelFindDevices
     * @see Garmin.DeviceDisplay#event:onStartFindDevices
     */
	startFindDevices: function() {
		this.getController(true); //try to unlock plugin
		if(this.options.autoHideUnusedElements){
		    if( this.findDevicesButton) {
		      this.findDevicesButton.hide();
		    }
		    if( this.browseComputerButton ) { 
		      this.browseComputerButton.hide();
		    }
		}
		if(this.findDevicesButton) 
	       	this.findDevicesButton.disabled = true;
	    if (this.cancelFindDevicesButton)
    		this.cancelFindDevicesButton.disabled = !this.isUnlocked();
		if (this.isUnlocked()) {
       		this.getController().findDevices();
		}
	},

    /** Entry point for cancelling search for connected devices.
     * @see Garmin.DeviceDisplay#event:onCancelFindDevices
     */
	cancelFindDevices: function() {
		this.resetUI();
       	this.getController().cancelFindDevices();
	},

    /** Call-back triggered before plugin searches for connected devices.
     * @event 
     * @param {JSON} json
     * @see Garmin.DeviceControl 
     * @see Garmin.DeviceDisplay#startFindDevices
     */
    onStartFindDevices: function(json) {
        this.setStatus(this.options.lookingForDevices);
    },

    /** Call-back triggered after plugin has completed its search for devices.
     * @event
     * @param {JSON} json
     * @see Garmin.DeviceControl
     * @see Garmin.DeviceDisplay#startFindDevices
     */
    onFinishFindDevices: function(json) {
		this.resetUI();
        if(json.controller.numDevices > 0) {
            this.devices = json.controller.getDevices();               
            
            var template = (this.devices.length == 1) ? this.options.foundDevice : this.options.foundDevices;
            var values = {deviceName: this.getShortDeviceName(this.devices[0]), deviceCount: json.controller.numDevices};
            this.setStatus( this.evaluateTemplate(template, values) );
 
			if(this.options.showFindDevicesElement ) {
				Element.show(this.findDevicesElement);
				if (this.options.showDeviceButtonsOnFound) {
					if (this.findDevicesButton && this.options.showFindDevicesButton)
						Element.show(this.findDevicesButton);
					if (this.cancelFindDevicesButton)
						Element.show(this.cancelFindDevicesButton);	
				} else {
					if (this.findDevicesButton)
						Element.hide(this.findDevicesButton);
					if (this.cancelFindDevicesButton)
						Element.hide(this.cancelFindDevicesButton);
				}
				// Hide device select on single device
				if ((this.devices.length < 2 && !this.options.showDeviceSelectOnSingle) || this.options.autoSelectFirstDevice) {
					Element.hide(this.deviceSelectElement);
				} else {
					Element.show(this.deviceSelectElement);
				}
				// Populate the devices list based on UI option
				this.options.useDeviceSelectList ? this._generateDeviceListView() : this._populateDeviceSelectDropDown();
			}
			
			if(this.options.showReadDataElementOnDeviceFound) {
				Element.show(this.readDataElement);
			}
			
			if(this.options.showSendDataElementOnDeviceFound) {
				Element.show(this.sendDataElement);
			}
			
			if(this.options.showWriteDataElementOnDeviceFound) {
				Element.show(this.writeDataElement);
			}
			
			if(this.options.autoHideUnusedElements) {
				if(this.activityDirectoryElement) {
				    Element.hide(this.activityDirectoryElement);
				}
				if(this.readDataElement) {
				    Element.show(this.readDataElement);
				}
				if(this.options.showBrowseComputer) {
        		    if( this.browseComputerButton) {
                        this.browseComputerButton.show();
        		    }
        		}
			}
			
			if (this.options.autoReadData) {
	        	this.showProgressBar();
	        	if (this.options.showReadDataTypesSelect) {
	        		this.readSpecificTypeFromDevice(this.readDataTypesSelect.value);
	        	} else if (this.options.readDataType != null) {
	        		this.readSpecificTypeFromDevice(this.options.readDataType, this.options.fileListingOptions);
	        	} else {
					this.readFromDevice();
	        	}
			}
			
			if (this.options.autoWriteData) {
	        	this.showProgressBar();
        		this.writeToDevice();
			}
			
			this.displayHealthDataButtonIfNeeded();
        } else { // No devices found!
        	if ((this.options.autoReadData || this.options.autoWriteData) && !this.options.showStatusElement) {
        		alert(this.options.noDeviceDetectedStatusText);
        	}
        	
			this.setStatus(this.options.noDeviceDetectedStatusText);
            if(this.findDevicesButton) {
                this.findDevicesButton.show();  // allow user to retry
            }
            
            //allow user to browse computer in activity directory
            if(this.options.uploadSelectedActivities) {
                this.browseComputerButton.show();
            }
			
			if(this.options.showFindDevicesElement) {
				if (this.options.showCancelFindDevicesButton) {
					Element.show(this.cancelFindDevicesButton);	
				}
				if (this.options.showDeviceSelectNoDevice && !this.options.autoSelectFirstDevice) {
					Element.show(this.deviceSelectElement);
				}
			}					
        }
        if (this.options.afterFinishFindDevices) {
    		this.options.afterFinishFindDevices.call(this, this.devices);
        }
    },
    
    /*@private*/
    displayHealthDataButtonIfNeeded: function()
    {
    	var supported = this.doesDeviceSupportDataType(Garmin.DeviceControl.FILE_TYPES.fitHealthData);
		var show = this.options.showHealthDataUploadButton;
		
		if(this.uploadHealthDataButton)
		{
			//If the device said health data isn't supported, that's not always trustworthy
			//(as in the case of an FR60 or 310XT, so double check, by checking the directory
			//listing XML from the device.
			if(supported == false)
			{
				supported = this.getController().doesCurrentDeviceSupportHealth();
			}
			
			if (!show || !supported) 
			{
				//this.uploadHealthDataButton.style.display = 'none';
				Element.hide(this.uploadHealthDataButton);
			}
			else
			{
				Element.show(this.uploadHealthDataButton);
			}
		}
    },
    
    /** Call-back for find device cancelled.
     * @event
     * @param {JSON} json
     * @see Garmin.DeviceControl 
     * @see Garmin.DeviceDisplay#cancelFindDevices
     */
	onCancelFindDevices: function(json) {
		this.setStatus(this.options.findCancelled);
    	this.resetUI();
    },

    /** Load device list into select UI component.
     * @private
     */
	_populateDeviceSelectDropDown: function() {
	    this.deviceSelectElement.appendChild(this.deviceSelectInput);
		this._clearHtmlSelect(this.deviceSelectInput);
		if(this.options.showFindDevicesElement) {
			for( var i=0; i < this.devices.length; i++ ) {
           	    this.deviceSelectInput.options[i] = new Option(this.getShortDeviceName(this.devices[i]),this.devices[i].getNumber());
			    
	           	if(this.devices[i].getNumber() == this.getController().deviceNumber) {
	           		this.deviceSelectInput.selectedIndex = i;
	           		// Adding afterSelectDevice functionality to the old select UI
	                if (this.options.afterSelectDevice != null) {
	                    this.options.afterSelectDevice.call(this, this.getController().deviceNumber, this.devices, this.garminController.getCurrentDeviceXml());
	                }
	           	}
			}
			this.deviceSelectInput.onchange = function() {
				var device = this.getController().getDevices()[this.deviceSelectInput.value];
				this.setStatus(this.evaluateTemplate(this.options.usingDevice, {deviceName:this.getShortDeviceName(device)}));
				this.getController().setDeviceNumber(this.deviceSelectInput.value);
				// Adding afterSelectDevice functionality to the old select UI
				if (this.options.afterSelectDevice != null) {
					this.options.afterSelectDevice.call(this, this.getController().deviceNumber, this.devices, this.garminController.getCurrentDeviceXml());
				}
				
				this.displayHealthDataButtonIfNeeded();
			}.bind(this);
			this.deviceSelectInput.disabled = false;
		}
	},
	
	/* Load device browser content.
     * @private
     */
	_generateAndDisplayDeviceBrowser: function() {
	   
        // Create device browser
		if( this.options.useDeviceBrowser) {
            if( this.deviceBrowserElement == null ) {
                this.generateDeviceBrowserElement(this.devices); 
            }
            
            if( !this.advancedUploadMode) {
                // Simple upload
                this.devicePreviewElement.show();
                this.progressBar.hide();
            } else {
                // Advanced upload
                this.deviceBrowserElement.show();
    		    this.activityDirectoryElement.show();
            }
    		    this.statusText.hide();
    		    this.showProgressBar();

		    // Show loading screen while reading device
		    if( this.loadingContentElement == null) {
                this._generateLoadingContent(this.statusElement);
		    } else {
		        // Update the device name displayed
		        this._updateLoadingContent(this.evaluateTemplate(this.options.loadingContentText, {deviceName:this.getShortDeviceName(this.getCurrentDevice())}));
		    }
		}
	},
	
	/* Load device list into select UI component.
     * @private
     */
	_generateDeviceListView: function() {
	    var deviceSelectContainer;
	    
		this._clearHtmlSelect(this.deviceSelectInput);
		
		deviceSelectContainer = document.createElement("div");
		Element.extend(deviceSelectContainer);
		deviceSelectContainer.className = this.options.deviceSelectClass;
		
		// Display change only if there are multiple devices
		if( this.devices.length > 1) {
    		// Change device link
    		this.changeDeviceElement = document.createElement("div");
    		Element.extend(this.changeDeviceElement);
    		this.changeDeviceElement.id = this.options.changeDeviceElementId;
    		this.changeDeviceElement.className = this.options.changeDeviceClass;
    		this.changeDeviceElement.innerHTML = '<a href="#">'+this.options.changeDeviceButtonText+'</a>';
    		this.changeDeviceElement.onclick = function() {
    		    this.devicePreviewElement.toggle();
    		    this.connectedDevices.toggle();
    		    this.deviceSelectInput.toggle();
    		}.bind(this);
    		this.deviceSelectElement.appendChild(this.changeDeviceElement);
		}
		
		// Display pre-selected device (first device)
		this.devicePreviewElement = document.createElement("div");
		Element.extend(this.devicePreviewElement);
		this.devicePreviewElement.id = this.options.previewDeviceElementId;
		this.devicePreviewElement.innerHTML = '<p>'+this.getShortDeviceName(this.getCurrentDevice())+'</p>';
		deviceSelectContainer.appendChild(this.devicePreviewElement);
		this.deviceSelectElement.appendChild(deviceSelectContainer);
		
		if(this.options.showFindDevicesElement) {
		    
		    // Connected devices label
		    this.connectedDevices = new Element("div", {className: this.options.connectedDevicesClass});
		    this.connectedDevices.update('<img src="'+ this.options.connectedDevicesImg +'" />' + this.options.connectedDevicesLabel);
		    this.connectedDevices.hide();
		    deviceSelectContainer.appendChild(this.connectedDevices);
		    
            this._populateDeviceList(this.deviceSelectInput, this._updateDevicePreview);
		   
			deviceSelectContainer.appendChild(this.deviceSelectInput);
			this.deviceSelectElement.appendChild(deviceSelectContainer);
			this.deviceSelectInput.hide();
			this.deviceSelectInput.disabled = false;
		}
	},
	
	/* Returns the current selected device according to the control object.
	 * @private
	 * @return {Device} the Device object belonging to the selected device
	 * @see Garmin.Device
	 */
	getCurrentDevice: function() {
        return this.devices[this.getController().deviceNumber];
	},
	
	/* Populates the device list.  'List' is emphasized because we are counting
	 * on the fact that it is not a select drop down. See _populateDeviceSelectDropDown
	 * for that.
	 * 
	 * Sets the onclick event for each device in the list.  When a device is selected,
	 * the device number in control is set to that device.  The class names of the
	 * entire list are also updated in order to indicate visually which device is selected.
	 * 
	 * After the above is finished, the callback method is executed.
	 *   
	 * @private
	 * @param {Element} deviceListElement the list element for the device listing
	 * @param {function} callback(deviceIndex) - the callback function to use when a device is selected.
	 *     deviceIndex is the device number selected by the user.   
	 */
	_populateDeviceList: function(deviceListElement, callback) {
	    var itemLink;
	    var listItem;
	    
	     // Insert detected devices into the display list
		for( var i=0; i < this.devices.length; i++ ) {
            listItem = document.createElement("li");
            Element.extend(listItem);
	        listItem.className = (i == this.getController().deviceNumber) ? "selected" : "unselected";
	        itemLink = document.createElement("a");
	        Element.extend(itemLink);
	        itemLink.href = "#";
			itemLink.innerHTML = this.getShortDeviceName(this.devices[i]);
	        itemLink.onclick = function(deviceListElement, deviceIndex, devices, callback){
	            
	            // Stop any existing reads and hide stuffs
                this.getController().cancelReadFromDevice();
	            
	            // Hide and unselect My Computer if selected
	            if( this.browseComputerElement != null) {
	               this.browseComputerElement.hide();
	               deviceListElement.childNodes[this.devices.length].className = "unselected";
	            }
	            this.statusElement.show();
	            
	            // Set the new device to talk to
	            this.getController().setDeviceNumber(deviceIndex);
	            this.displayHealthDataButtonIfNeeded();
	            // Update the class names in the entire list
	            for(var j=0; j< this.devices.length; j++) {
	               var listItem = deviceListElement.childNodes[j];
	               listItem.className = (j == this.getController().deviceNumber) ? "selected" : "unselected";
	            }
	            // The callback function has to take these two parameters!  Even if it ignores em.
	            callback.call(this, deviceIndex, this.devices, this.getController().getCurrentDeviceXml());
	        }.bind(this,deviceListElement,i,this.devices,callback); //bind with parameter
	        listItem.appendChild(itemLink);
	        deviceListElement.appendChild(listItem);
		}
		
		// Select first device
		if(this.options.autoSelectFirstDevice) {
		    this.options.afterSelectDevice.call(this, this.getController().deviceNumber, this.devices, this.garminController.getCurrentDeviceXml());
		}
	},
	
	/* Process the filename, trim it if its too long
	 * @param {Garmin.Device} the device whose name is to be processed
	 * @return {String} the truncated device name, according to the max size set in the display options
	 * @see Garmin.DeviceDisplayDefaultOptions.deviceLabelMaxSize
	 * @see Garmin.Device
	 */
	getShortDeviceName : function(device) {
		var deviceName = device.getDisplayName();
		if (deviceName.length > this.options.deviceLabelMaxSize) {
			deviceName = deviceName.substring(0, this.options.deviceLabelMaxSize) + "...";
		}
		return deviceName;
	},
	
	/* Update the device preview display to show the device selected by the user.
	 * Hides the input list and shows the preview element.
	 * @param int deviceNumber - the device number (index) selected by the user
	 * @param String deviceXml - describes the device (unused right now)  
	 */
	_updateDevicePreview : function(deviceNumber, deviceXml){
        this.devicePreviewElement.innerHTML = '<p>'+this.getShortDeviceName(this.devices[deviceNumber])+'</p>';
        this.devicePreviewElement.show();
        if(this.deviceSelectInput) this.deviceSelectInput.hide();
        if(this.connectedDevices) this.connectedDevices.hide();
	},
	
    ////////////////////////////// READ METHODS ////////////////////////////// 
    
    /** Initiation call for reading from a device.  If a fitness device is detected reads TCX
     * otherwise reads GPX.
     * Upon completion if the afterFinishReadFromDevice method is defined
     * it will be called.  At this time you may also obtain location data using the 
     * getTracks and getWaypoints methods.
     * @param {Array} readDataTypes list of read data types
     * @see Garmin.DeviceControl.FILE_TYPES
     */
	readFromDevice: function(readDataTypes) {
		var deviceNumber = this.getController().deviceNumber;
		var device = this.getController().getDevices()[deviceNumber];
		
		// TODO remove this later 
		// Backwards compatability for deprecated method
		if( this.options.readDataType != null ) {
		    readDataTypes = new Array();
		    readDataTypes[0] = this.options.readDataType;
		}
		
		// Read the first supported type in the list
		var supported = null;
		for(var i=0; i < readDataTypes.length; i++) {
		    var datatype = readDataTypes[i];
		    if(supported == null && this.getController().checkDeviceReadSupport(datatype)) {
		        supported = datatype;
		        
		        if(this.options.uploadSelectedActivities) {
    		        // Handle directory types
    		        switch(datatype) {
    		        	case Garmin.DeviceControl.FILE_TYPES.gpxDir:
                		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
                		case Garmin.DeviceControl.FILE_TYPES.fitDir:
                		case Garmin.DeviceControl.FILE_TYPES.fitHealthData:
                		case Garmin.DeviceControl.FILE_TYPES.readableDir:
                            if( this.activityTable == null) {
                                this._generateActivityTableElement();
                            } 
                            else 
                            {
                                this._clearActivityTable();
                            }     		
                            this._generateAndDisplayDeviceBrowser();
    		        }
		        }
		        this.getController().readDataFromDevice(datatype, this.options.fileListingOptions);
		    }
		}
		
		// No supported types found, throw error
		if( supported == null) {
    	    var error = new Error(this.options.unsupportedDevice);
    	    error.name = "UnsupportedDataTypeException";
    	    this.handleException(error);
		}
	},
	
    /** Read the filtered activities.  Filtered activities are those picked
     * by the API as well as any user-selected activities, if applicable.  
	 * 
	 * Activities may be uploaded after being read.
	 * 
	 * Filtered activities are detected before
	 * the activities themselves are read, and data filters filter the data
	 * after the activities are read.
	 * 
	 * @see Garmin.DeviceDisplay#event:onFinishUploads
	 */
	readFilteredActivities: function() {
	    // Make sure there are selected activities to read
        if( this._directoryHasSelected() == false) {
            if( this.advancedUploadMode) {
                // Alert user to select
                alert(this.options.errorActivitySelect);
            } else {
                // No new activities
                this.numQueuedActivities = 0;
                this.setStatus(this.options.noFilteredActivities);
                if( this.options.uploadSelectedActivities ) {
                    this.getController()._broadcaster.dispatch("onFinishUploads", { display: this });
                }            
            }
        } else {
        	this.activities = null;
        	if( this.readTracksSelect ){
            	this.readTracksSelect.length = 0;	
        	}
        	this.readSelectedButton.disabled = true;
        	if(this.options.useLinks) {
        	    this.readSelectedButton.hide();
        	}
        	if(this.checkAllBox != null) {
        	  this.checkAllBox.disabled = true;
        	}
    	
    	    if(this.options.useDeviceBrowser && this.advancedUploadMode){
    	       this.statusElement.hide();  
    	    } else {
        	   this.showProgressBar();
    	    }
    	    
    	    this._populateActivityQueue();
    
            if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir) {
                this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.tcxDetail;
                this._readNextSelected();
            } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir) {
                this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.fit;
                this._readNextSelected();
            } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData) {
                //this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.fit;
                this._readNextSelected();
            } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir) {
                //don't change read type
                this._readNextSelected();
            } else if (this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.gpxDir) {
            	this.fileTypeRead = Garmin.DeviceControl.FILE_TYPES.gpxDetail;
            	
            	while (this.activityQueue.size() != 0) {
            		// Display "Uploading..."
            		this._displayProcessingForCurrentActivity(this.activityQueue.last());
            		this.activityQueue.pop();
            	}

            	this.garminController.readDataFromDevice(this.fileTypeRead);
            }    		     		    		    	
        }
	},
	
    /** Generic read method, supporting GPX, TCX, Courses, Workouts, User Profiles, 
	 * TCX activity directory, and TCX course directory reads. <br/> 
     * <br/>
     * Upon completion if the afterFinishReadFromDevice method is defined
     * it will be called.  At this time you may also obtain location data using the 
     * getTracks and getWaypoints methods.<br/>
	 * <br/>
	 * Fitness detail reading (one specific activity) is not supported by this read method, refer to 
	 * readDetailFromDevice for that. <br/>  
	 * 
     * @param {String} readDataType - type of data to read.
     * @param {Object} fileListingOptions - additional read options
     * @see Garmin.DeviceControl.FILE_TYPES
     * @see Garmin.DeviceDisplayDefaultOptions.afterFinishReadFromDevice
     * @see Garmin.DeviceDisplayDefaultOptions.fileListingOptions
     */
	readSpecificTypeFromDevice: function(readDataType, fileListingOptions) {
	    // Check to make sure device supports reading this type. Must do this at display layer otherwise exception will not
	    // bubble up to the user.
    	if( this.getController().checkDeviceReadSupport(readDataType) == false) {
    	    var error = new Error(this.evaluateTemplate(this.options.unsupportedReadDataType, {dataType: readDataType}));
    	    error.name = "UnsupportedDataTypeException";
    	    this.handleException(error);
    	} else {
    		var deviceNumber = this.getController().deviceNumber;
    		var device = this.getController().getDevices()[deviceNumber];
    		
    		switch(readDataType) {
        		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
        		case Garmin.DeviceControl.FILE_TYPES.fitHealthData: 
				case Garmin.DeviceControl.FILE_TYPES.readableDir:
                    if( this.activityTable == null) {
                        this._generateActivityTableElement();
                    } else {
                        this._clearActivityTable();
                    }     		
                    this._generateAndDisplayDeviceBrowser();
        			// no break!  keep on goin'
        		case Garmin.DeviceControl.FILE_TYPES.gpx:
        		case Garmin.DeviceControl.FILE_TYPES.gpxDir:
        		case Garmin.DeviceControl.FILE_TYPES.tcx:
        		case Garmin.DeviceControl.FILE_TYPES.crs:
        		case Garmin.DeviceControl.FILE_TYPES.wkt:
        		case Garmin.DeviceControl.FILE_TYPES.tcxProfile:        		
        		case Garmin.DeviceControl.FILE_TYPES.crsDir:
        		case Garmin.DeviceControl.FILE_TYPES.deviceXml:
        			this.getController().readDataFromDevice(readDataType, fileListingOptions);
        			break;
        		default:
        			var error = new Error(Garmin.DeviceControl.MESSAGES.invalidFileType + readDataType);
    				error.name = "InvalidTypeException";			
    				this.handleException(error);
        	} 
    	}
	},

    /** Call-back for device read progress.
     * @param {JSON} json the progress report in JSON format
     * @see Garmin.DeviceDisplay#onFinishReadFromDevice
     * @event
     */
    onProgressReadFromDevice: function(json) {
		if(this.options.showProgressBar) {
	    	this.updateProgressBar(this.progressBarDisplay, json.progress.getPercentage());
	    	this.updateProgressBarText(this.progressBarText, this.options.showDetailedStatus ? json.progress.text[0] + json.progress.text[1] : json.progress.text[1]);
	    } else {
    	   this.setStatus(json.progress);
	    }
    },
    
    /** Call-back for device read cancelled.
     * @see Garmin.DeviceControl
     * @param {JSON} json the progress report in JSON format
     */
	onCancelReadFromDevice: function(json) {
    	this.setStatus(this.options.cancelReadStatusText);
    	this.resetUI();
    },

    /** Call-back for device read.
     * @see Garmin.DeviceControl
     * @param {JSON} json the progress report in JSON format
     */
    onFinishReadFromDevice: function(json) {
    	//Track the original type so that the UI can react accordingly later
    	this.originalFileTypeRead = this.fileTypeRead;
    	this.fileTypeRead = json.controller.gpsDataType;
    	this.readDataDoc = json.controller.gpsData;
	    this.readDataString = json.controller.gpsDataString;
    	
		this.setStatus(this.options.dataReadProcessing);
	    this.resetUI();
	    
	    this.clearMapDisplay();

    	// select the correct factory for the parsing job, except for binary, which just passes through
    	switch(this.fileTypeRead) {
    		case Garmin.DeviceControl.FILE_TYPES.tcx:
    		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
    		case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
    			this.factory = Garmin.TcxActivityFactory;
    			break;
    		case Garmin.DeviceControl.FILE_TYPES.gpx:
    		case Garmin.DeviceControl.FILE_TYPES.gpxDir:
    		case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
    			this.factory = Garmin.GpxActivityFactory;
    			break;
    		case Garmin.DeviceControl.FILE_TYPES.fitHealthData:
    		case Garmin.DeviceControl.FILE_TYPES.fitDir:
			case Garmin.DeviceControl.FILE_TYPES.readableDir:
    			this.factory = Garmin.DirectoryFactory;
    			break;
    		case Garmin.DeviceControl.FILE_TYPES.fit:
    		case Garmin.DeviceControl.FILE_TYPES.binary:
                // Post to server immediately (and finishes reading activities on the queue)
		    	if(this.options.uploadSelectedActivities){
	    		    // Compressed data
	    		    if(this.options.uploadCompressedData) {
                	   this.readDataString = json.controller.gpsDataStringCompressed;
                	} 
                	this._postDataUpdateDisplay(this.readDataString);
	    		}
	    			this._finishReadProcessing(json);
    		    return;
    		    
    		default:
	    		var error = new Error( + this.fileTypeRead);
				error.name = "InvalidTypeException";
				this.handleException(error);
    	}

		// parse the data into activities if possible
		if (this.factory != null) {
			// Convert the data obtained from the device into activities.
			// If we're starting a new read session (as opposed to individual 
			// activity reads from the activity directory), start a new activities array
			if( this.activities == null) {
				this.activities = new Array();
			}
			
			// Populate this.activities
			switch(this.fileTypeRead) {
				case Garmin.DeviceControl.FILE_TYPES.gpxDir:
				case Garmin.DeviceControl.FILE_TYPES.tcxDir:
				case Garmin.DeviceControl.FILE_TYPES.fitHealthData:
				case Garmin.DeviceControl.FILE_TYPES.fitDir:
				case Garmin.DeviceControl.FILE_TYPES.readableDir:
				    // TODO should merge tcx and fit directory types at some point so we can share code
				    if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir) {
                        this.activities = this.factory.parseDocument(this.readDataDoc);
                        this._createActivityDirectory(Garmin.DeviceControl.FILE_TYPES.tcxDir, this.activities);
				    } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir ||
				              this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir ) {
	    			    var files = this.factory.parseDocument(this.readDataDoc);
	    			    if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir)
	    			    {
    	    				// Only use activity files for the activity directory
                        	files = Garmin.DirectoryFactory.getActivityFiles(files);
    	    			}
    	    			this._createActivityDirectory(this.fileTypeRead, files);
				    } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData) {
	    			    var files = this.factory.parseDocument(this.readDataDoc);
                        var healthFiles = Garmin.DirectoryFactory.getHealthDataFiles(files);
    	    			// Only use activity files for the activity directory
    	    			this._createActivityDirectory(Garmin.DeviceControl.FILE_TYPES.fitHealthData, healthFiles);				    
				    } else if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.gpxDir) {
				    	this.activities = this.factory.parseDocumentByType(this.readDataDoc, Garmin.GpxActivityFactory.GPX_TYPE.tracks);
				    	if(this.options.uploadSelectedActivities) {
				    	   this._createActivityDirectory(Garmin.DeviceControl.FILE_TYPES.gpxDir, this.activities);
				    	}				    	
				    }
	    			
	    			if( this.options.detectNewActivities && this.options.uploadSelectedActivities) {
        	            // No activities on device  
                		if( this.activityDirectory.size() == 0) {
        	                if(this.advancedUploadMode) {
                    		    this._updateLoadingContent(this.options.noActivitiesOnDevice);
                    		} else {
                                this.getController()._broadcaster.dispatch("onFinishUploads", { display: this });
        	                }
                		}
        	            else {
    	    			    // There are activities to compare
    	    			    this.activityMatcher = new Garmin.ActivityMatcher(this.garminController.getCurrentDeviceXml(), 
                                this.activityDirectory.getIds(), this.options.syncDataUrl, this.options.syncDataOptions, 
                                function(){this._finishReadProcessing(json)}.bind(this));
    	    			    this.activityMatcher.run();
        	            }
	    			} else {
	    			    // Finished reading activities in queue, if any, so list them.
	    			    this._finishReadProcessing(json);
	    			}
					break;
				case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
				    if(this.options.uploadSelectedActivities){      
                        this._postDataUpdateDisplay(this.readDataString);
                    }
                    break;          
				case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
	    			
	    			// Store this read activity
	    			// TODO: May not need this line, merge logic with binary type
	    			this.activities = this.activities.concat( this.factory.parseDocument(this.readDataDoc) );
	    			
		    		// Post to server (and finishes reading activities on the queue)
		    		if(this.options.uploadSelectedActivities){
		    		    // Compressed data
		    		    if(this.options.uploadCompressedData) {
                    	   this.readDataString = json.controller.gpsDataStringCompressed;
                    	} 
                    	this._postDataUpdateDisplay(this.readDataString);
		    		}
		    		// Finished reading activities in queue, if any, so list them.
		    		this._finishReadProcessing(json);
					break;			
	    		default:
	    			this.activities = this.factory.parseDocument(this.readDataDoc);
	    			// filter the activities
    				this._applyDataFilters();
            		// Finished reading activities in queue, if any, so list them.
    				this._finishReadProcessing(json);
	    			break;
			}
	
		}
    },
    
    _postDataUpdateDisplay: function(data) {
        // Post to server (and finishes reading activities on the queue)
        if( this.loadingContentElement != null) {
        	this.loadingContentElement.innerHTML = this.options.uploadingStatusText;
        	this.loadingContentElement.show();
        }
		this._postActivityToServer(data);
    },

	_applyDataFilters: function() {
		var dataFilters = this.options.dataFilters;
		if (dataFilters != null) {
			for (var i = 0; i < dataFilters.length; i++) {
				if (dataFilters[i].run != null) {
					dataFilters[i].run(this.activities, garminFilterQueue);
				}
			}
		}
	},

    /** Process the read data.  Calls afterFinishReadFromDevice when finished.
     * @see Garmin.DeviceDisplay.afterFinishReadFromDevice
     * @param {JSON} json the progress report in JSON format
     */
	_finishReadProcessing: function(json) {
		if (garminFilterQueue != null && garminFilterQueue.length > 0) {
			//console.debug("waiting for filters to finish...");
			setTimeout(function(){this._finishReadProcessing(json);}.bind(this), 500);
		} else {
			
			// list activities and set status to indicate how many were found
			if( this.activityQueue == null || this.activityQueue.length == 0 ) {
		    	
	    		// List the activities
		    	if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir ||
		    	   this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData ||
		    	   this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir ){
		    	    var summary = this._listDirectory(this.activityDirectory);
	    		    var template = null; 
                	if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir ) { 
                		template = this.options.filesFound;
            		}else {
                		template = this.options.dataFound
            		}
	    		    this.setStatus( this.evaluateTemplate(template, summary) );
		    	}
				if( this.activities != null && this.activities.length > 0) {
					var summary = null;
					if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir ||
					    this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.gpxDir) {
						summary = this._listDirectory(this.activityDirectory);
					} else {
		    			summary = this._listActivities(this.activities);
					}
					
				    // Display # of activities found
	    		    this.setStatus( this.evaluateTemplate(this.options.dataFound, summary) );
				}
		    	
		    	// Disable appropriate buttons after read is finished
		    	if(this.options.uploadSelectedActivities) {
    		    	switch(this.fileTypeRead) {
    		    		case Garmin.DeviceControl.FILE_TYPES.gpx:
    		    		case Garmin.DeviceControl.FILE_TYPES.gpxDir:
    		    		case Garmin.DeviceControl.FILE_TYPES.tcx:
    		    		case Garmin.DeviceControl.FILE_TYPES.crs:
    		    		case Garmin.DeviceControl.FILE_TYPES.tcxDir:
    		    		case Garmin.DeviceControl.FILE_TYPES.crsDir:
    		    		case Garmin.DeviceControl.FILE_TYPES.fitHealthData:
    		    		case Garmin.DeviceControl.FILE_TYPES.fitDir:
    		    		case Garmin.DeviceControl.FILE_TYPES.readableDir:
    		    			this.deviceSelectInput.disabled = true;
    		    			if( this.advancedUploadMode) {
    		    			    // Advanced upload
    		    			    if(this.loadingContentElement != null) {
                                    this.loadingContentElement.hide();
    		    			    }
    		    			} else { 
        		    			// Simple upload
        		    			this.progressBar.hide();
    	        	            this.uploadProgressBar.show();
    		    			    this.readFilteredActivities();		    			    
    		    			}
    		    			break;
    		    		case Garmin.DeviceControl.FILE_TYPES.gpxDetail:
    		    		case Garmin.DeviceControl.FILE_TYPES.tcxDetail:
    		    		case Garmin.DeviceControl.FILE_TYPES.crsDetail:
    		    		case Garmin.DeviceControl.FILE_TYPES.fit:
    		    		case Garmin.DeviceControl.FILE_TYPES.binary:
    		    			this.readSelectedButton.disabled = false;
    		    			if( this.options.useLinks) {
    		    			    this.readSelectedButton.show();
    		    			}
    		    			this.readSelectedButton.disabled = false;
    		    			if(this.checkAllBox != null) {
    		    			    this.checkAllBox.disabled = false;
    		    			}
    		    			break;
    		    	}
		    	}
			}
			
			// pass data to the user if they want it			
			if (this.options.afterFinishReadFromDevice) {
				var dataString = this.factory != null ? this.factory.produceString(this.activities) : json.controller.gpsDataString;
				var dataDoc = this.factory != null ? Garmin.XmlConverter.toDocument(dataString): json.controller.gpsData;
	    		this.options.afterFinishReadFromDevice(dataString, dataDoc, json.controller.gpsDataType, this.activities, this);
			}
		}
	},

    /** As uploads continue processing, this method will be called.  This is called once
     * per upload item.  This does not track byte-progress of a single upload.
     * @event
     * @param {JSON} json the progress report in JSON format
     * @see Garmin.DeviceDisplay#event:onFinishUploads
     */
    onProgressUpload: function(json) {
        if(this.options.showProgressBar) {
	    	this.updateProgressBar(this.uploadProgressBarDisplay, json.progress.percentage);
	    	this.updateProgressBarText(this.uploadProgressBarText, json.progress.text);
	    } else {
    	   this.setStatus(json.progress);
	    }
    },
    
    /** Returns the current status of the upload progress based on the activity queue.
     * If there is no upload in progress, all values will be 0. 
     * @returns {JSON} json object with report values and current DeviceDisplay instance
     * @returns json.progress
     * @returns {String} json.progress.current the current upload index from the activity queue
     * @returns {String} json.progress.total whole number of uploads finished
     * @returns {String} json.progress.percentage percentage value of uploads finished 
     * @returns {String} json.progress.text upload progress text to display to user
     * @returns {Garmin.DeviceDisplay} json.display the current DeviceDisplay instance for UI purposes
     */
    getUploadProgressJson: function() {
        
        var currentVal;
        var totalVal;
        var percentageVal;
        
        if( this.numQueuedActivities == null || this.activityQueue == null || this.activityQueue.length == 0) {
            currentVal = 0;
            totalVal = 0;
            percentageVal = 0;
        } else {
            currentVal = this.numQueuedActivities - this.activityQueue.length;
            totalVal = this.numQueuedActivities;
            percentageVal = currentVal / totalVal * 100;
        }
        
        return {
                    progress: {
                        current: currentVal, 
                        total: totalVal,
                        percentage: percentageVal,
                        text: this.evaluateTemplate(this.options.uploadProgressStatusText, {currentUpload: currentVal, totalUploads: totalVal})
                    },
                    display: this
                };
    },
    
	/* Reads the user-selected activities from the device by using the activity queue. 
     */
    _readNextSelected: function() {    	
    	// Look at the next selected activity on the queue.  (The queue only holds selected activities)    	
		this._displayProcessingForCurrentActivity(this.activityQueue.last());
    	this.setStatus(this.options.uploadingActivities);
    	
    	var currentActivityId = $(this.currentActivity).value;
    	if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDetail ) {
    	   this.garminController.readDetailFromDevice(this.fileTypeRead, currentActivityId);
    	} else if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fit ||
    			   this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData ||
    			   this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.binary ||
    			   this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir) {
    	   var deviceNumber = this.getCurrentDevice().getNumber();
    	   this.garminController.getBinaryFile(deviceNumber, this.activityDirectory.getEntry(currentActivityId).path); 
    	}
    },
    
    /**
     * Displays the processing icon for a given activity
     * 
     * @param activity - the activity that will have the processing icon
     */
    _displayProcessingForCurrentActivity: function(activity) {
    	this.currentActivity = activity;
    	
    	// Display 'processing' image next to corresponding activity in table 
        var statusCellIdElement = this.currentActivityStatusElement();
        if( statusCellIdElement ) {
            statusCellIdElement.innerHTML = this.options.statusCellProcessingImg;
        }
    },
    
    /**
     * Returns status element for current activity in queue
     * 
     * @returns {Element}
     */
    currentActivityStatusElement: function() {
        return this.currentActivity ? $(this.currentActivity.replace(/Checkbox/,"Status")) : null;
    },
    
    /** Stop uploading activities in the queue, and go on to finished screen.
     * Useful for certain error cases.
     * @see Garmin.DeviceDisplay#event:onFinishUploads
     */
    stopQueuedUploads: function() {
        this.clearActivityQueue();
    	// Broadcast all uploads finished
        this.getController()._broadcaster.dispatch("onFinishUploads", { display: this });  
    },
    
    /* Posts the last read activity data from the activityQueue.  See {@link this.options.sendDataUrl}, 
     * {@link this.options.sendDataOptions} for designating the server and options for the AJAX request. 
     * 
     * A custom handler is also possible by defining {@link this.options.postActivityHandler}.  Defining 
     * this method will override the default Send Data implementation provided by this API.
     * 
     * @param String dataString - the data string to post to server  
     * @see Garmin.DeviceDisplayDefaultOptions.postActivityHandler
     */
    _postActivityToServer: function(dataString) {
    	if( this.options.sendDataUrl == null && this.options.postActivityHandler == null ) {
            throw new Error("Need to define either sendDataUrl or the postActivityHandler in display" +
                    " options, depending on desired behavior.");
    	}
    	else {
    	    // nested function
    	    var finishPostProcessing = function() {
                // Exceptions are handled in postToServer. Even if errors occur, doesn't necessarily mean
                // that the rest of the uploads should be stopped.  The uploadQueue needs to be cleared 
                // if that is the desired behavior.
            	this.activityQueue.pop();
            	
                // Broadcast upload progress
                this.getController()._broadcaster.dispatch("onProgressUpload", this.getUploadProgressJson());
                    
            	// TODO: This doesn't quite belong here, but it's the only way to ensure synchronization.
            	if(this.activityQueue.length > 0) {
        	    	// Read what's left in the queue
        			this._readNextSelected();
            	} else { 
            	    // Broadcast all uploads finished
            	    this.getController()._broadcaster.dispatch("onFinishUploads", { display: this });
            	}
    	    }.bind(this);
    	    
    	    if( this.options.sendDataUrl != null ) {
    	           // post the activity and then read the next one
    	           this.postToServer(finishPostProcessing);
    	    }
    	    else if( this.options.postActivityHandler != null) {
                this.options.postActivityHandler(dataString, this);
                finishPostProcessing();
    	    }
    	}
    },
    
    /** Callback when all uploads are finished. The display is passed in as the single param.
     * @event
     * @param {Garmin.DeviceDisplay} the current DeviceDisplay instance
     * @see Garmin.DeviceDisplayDefaultOptions.afterFinishUploads
     */
    onFinishUploads: function(display) {
        
        // Activities were uploaded
        if( this.loadingContentElement != null &&
            this.numQueuedActivities > 0 ) {
            this.loadingContentElement.hide();
        }
        // Nothing to upload, so show it 
        else if( !this.advancedUploadMode ) {
            if( this.loadingContentElement != null ) {
                this.loadingContentElement.className = 'shortStatus';
            }
            this.activityTable.hide();
            this._updateLoadingContent('No new activities to upload.');            
        }
        // Show the directory for results 
        this.activityDirectoryElement.show();
        this.uploadProgressBar.hide();
        this.findDevicesElement.hide();
        this.readSelectedButton.hide();
        if( this.deviceBrowserElement != null ) {
            this.deviceBrowserElement.hide();
        }
        if( this.options.afterFinishUploads ) {
            this.options.afterFinishUploads.call(this, this);
        } 
    },
    
    _clearActivityTable: function() {
    	//clear previous data, if any, including the header
    	while(this.activityTableHeader.rows.length > 0) {
    	    this.activityTableHeader.deleteRow(0);
    	}
		while(this.activityTable.rows.length > 0) {
			this.activityTable.deleteRow(0);
		}
    },
    
    /** Creates the activity directory of all activities (activity IDs) on the device
     * of the user-selected type.  Most recent entries are first.
     * @param listType String type of directory described by the list
     * @param list Array list of directory entries, of any type. Currently expects activities (tcx) or files (fit)
     * @private 
     */
    _createActivityDirectory: function(listType, list) {
        
        if( this.advancedUploadMode ) {
            this.activityDirectoryElement.show();
        }
    	this.activityQueue = new Array(); // Initialized here so that we can detect activity selection read status    	
    	
    	this.activityDirectory = new Garmin.ActivityDirectory();
    	
    	for( var jj = 0; jj < list.length; jj++) {
            var id;
            var name;
            var duration;
            var entry;            
            
    	    if( listType == Garmin.DeviceControl.FILE_TYPES.tcxDir) {
    	        // list of Garmin.Activity
    	        var activity = list[jj];
    	        id = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
    	        name = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime).getValue().getTimeString();
    	        duration = activity.getStartTime().getDurationTo(activity.getEndTime()); // Correct time zone
    	        
                entry = this.activityDirectory.addEntry(id, name, duration, null);
                
    	    } else if( listType == Garmin.DeviceControl.FILE_TYPES.fitDir ) {
    	        // list of Garmin.Files
    	        var file = list[jj];
    	        id = file.getIdValue(Garmin.FileId.KEYS.id);
    	        name = file.getAttribute(Garmin.File.ATTRIBUTE_KEYS.creationTime).getTimeString();
    	        
                this.activityDirectory.addEntry(id, name, null, null);
                this.activityDirectory.getEntry(id).path = file.getAttribute(Garmin.File.ATTRIBUTE_KEYS.path);                
    	    } else if(listType == Garmin.DeviceControl.FILE_TYPES.readableDir ) {
    	        var file = list[jj];
                this.activityDirectory.addFileEntry(file);
    	    }else if( listType == Garmin.DeviceControl.FILE_TYPES.fitHealthData) {
    	        // list of Garmin.File
    	        var file = list[jj];
    	        id = Garmin.DeviceDisplay.MISC.health_data_id;
	        	name = this.options.health_data_label;
    	        
                this.activityDirectory.addEntry(id, name, null, null);
                this.activityDirectory.getEntry(id).path = file.getAttribute(Garmin.File.ATTRIBUTE_KEYS.path);                
    	    } else if (listType == Garmin.DeviceControl.FILE_TYPES.gpxDir) {
    	    	// list of Garmin.Activity
    	    	var activity = list[jj];
    	    	var summaryValue = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime);
    	    	var attribute = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
    	    	
    	    	// Make sure these are not null or else we will skip
    	    	if (summaryValue != null && attribute != null)
    	    	{
	    	    	id = summaryValue.getValue().getXsdString();
	    	    	name = attribute;
	    	    	
	    	    	this.activityDirectory.addEntry(id, name, null, null);
    	    	} 	    	    	    
    	    }
    	}
    },
    
    /* Creates the activity queue of selected activities to read in detail from device.
     * Called after the user has finished selecting activities and also after the API 
     * does its synchronization thing).  The queue is an Array that is constructed and 
     * then reversed to simulate a queue.
     */
    _populateActivityQueue: function() {
    	var checkBoxName = "activityItemCheckbox";
        // TODO Create a class for the activity queue
    	for( var jj = 0; jj < this.activityDirectory.size(); jj++) {
    		var checkBoxElementId = checkBoxName + jj; 
    				
    		if($(checkBoxElementId).checked == true){
    			this.activityQueue.push(checkBoxElementId);
    		}
    		
    		var activityId = this.activityDirectory.getIds()[jj];
    		this.activityDirectory.getEntry(activityId).displayElementId = checkBoxElementId;
    	}
    	// Reverse the array to turn it into a queue
    	this.activityQueue.reverse(); 
    	
    	// Save the original size for status reporting
    	this.numQueuedActivities = this.activityQueue.length;
    },
    
    /** Empties the activity queue if it has any entries.
     */
    clearActivityQueue: function() {
        for( var i=0; i < this.activityQueue.length; i++) {
            this.activityQueue.pop();
        }
    },
    
	/* The activityTable object is the HTML table element on the demo page.  This function
	 * adds the necessary row to the table with the activity data.
	 * @param int index - the internal index assigned to the activity value in order
	 * to update the table status
	 * @param {Garmin.ActivityDirectory.Entry} entry - entry to add to the table
	 * @see afterTableInsert
	 */
	_addToActivityTable: function(index, entry) {
		
		var tableIndex = 0;
		
		var activityId = entry.id;
		
		var row = this.activityTable.insertRow(this.activityTable.rows.length); // append a new row to the table
		// Color odd rows
		if( (index+2) % 2 != 0) {
            row.setAttribute('bgcolor', '#f3f3f3');
		}
		
		var selectCell = row.insertCell(tableIndex++);
		selectCell.width = '40'; // Set widths to match header 
		selectCell.align = 'right';

		var checkbox = document.createElement("input");
		Element.extend(checkbox);
		checkbox.id = "activityItemCheckbox" + index;
		checkbox.type = "checkbox";
		checkbox.value = activityId;
		
		// When checkbox is clicked, pass last 2 args to callback method, which is bounded to the display object
		// TODO pass the entire directory object and handle appropriately
		checkbox.observe('click', this.onActivitySelect.bind(this, checkbox.id, this.activityDirectory.getIds())); 
		selectCell.appendChild(checkbox);

		var nameCell = row.insertCell(tableIndex++);
		nameCell.width = '220';

        if( entry.duration != null) {
    		var durationCell = row.insertCell(tableIndex++);
		    durationCell.width = '210';
		}
		
		var statusCell = row.insertCell(tableIndex++);
		statusCell.id = "activityItemStatus" + index;
		statusCell.className = 'activityStatusCell';
		
		// Name and duration cells 
		if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir ||
		    this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir || 
		    this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData ||
		    this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.gpxDir ||
		    this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir) {
			nameCell.innerHTML = entry.name;
			
			if( durationCell != null) {
			    durationCell.innerHTML = entry.duration;
			}
		}
		else if( this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.crsDir ) {
			nameCell.innerHTML = activityId;
		}
		
		if( this.options.afterTableInsert ) {
		    this.options.afterTableInsert.call(this, index, entry, statusCell, checkbox, row, this.activityMatcher);
		}

		if(activityId == Garmin.DeviceDisplay.MISC.health_data_id)
		{
			row.style.display = 'none';
		}
	},
	
	/**
	 * Adds the single row to the activity table header.  The columns in the table
	 * are determined by the data available in the directory, using an all or nothing 
	 * check.
	 * @param directory {Garmin.ActivityDirectory} the activity directory to build the table header off of
	 */
	_addToActivityTableHeader: function(directory) {
	    
	    var tableIndex = 0;
		
	    var row = this.activityTableHeader.insertRow(0); // append a new row to the table
		
		var selectCell = row.insertCell(tableIndex++);
		selectCell.id = 'selectAllHeader';
		selectCell.width = '40';
		selectCell.align = 'right';

		var nameCell = row.insertCell(tableIndex++);
		nameCell.id = 'nameHeader';
		nameCell.width = '220';
		nameCell.align = 'left';
		nameCell.innerHTML = this.options.getActivityDirectoryHeaderIdLabel.call(this); 
        
		if( directory.size() > 0 &&
		    directory.getFirstEntry().duration != null) {
            var durationCell = row.insertCell(tableIndex++);
    		durationCell.id = 'durationHeader';
    		durationCell.width = '210';
    		durationCell.align = 'left';
    		durationCell.innerHTML = this.options.activityDirectoryHeaderDuration;
        }

		var statusCell = row.insertCell(tableIndex++);
        statusCell.id = 'activityStatusHeader';
		statusCell.innerHTML = this.options.activityDirectoryHeaderStatus;

		// Only display 'check all' box if there's no upload limit 
		if( this.options.uploadMaximum < 1) {
    		this.checkAllBox = document.createElement("input");
    		Element.extend(this.checkAllBox);
    		this.checkAllBox.id = "checkAllBox";
    		this.checkAllBox.type = "checkbox";
    		selectCell.appendChild(this.checkAllBox);
    		this.checkAllBox.title = this.options.activityDirectoryCheckAllTooltip;
    		this.checkAllBox.checked = true; // entries are "new" by default
    		
    		if( this.options.uploadMaximum == 0) {
    		    this.checkAllBox.hide();
    		}
    		
    		this.checkAllBox.onclick = function() { this._checkNewDirectoryEntries(); }.bind(this);
		}
	},
	
	/** Callback for enforcing upload selection limit.  Called each time the user modifies selection.
	 * @param String elementId - the ID of the input element selected to trigger this callback
	 * @param Array activityDirectory - array of all activity IDs listed in the activity directory  
	 * @see Garmin.DeviceDisplayDefaultOptions.uploadMaximum
	 */
	onActivitySelect: function(elementId, activityDirectory) {
	    var selectedCount = 0;
	    for( var jj = 0; jj < activityDirectory.length; jj++) {
    		if( $("activityItemCheckbox" + jj).checked == true){
    			selectedCount++;
    		}
    	}
    	if( this.options.uploadMaximum > 0 ) {
            if( selectedCount > this.options.uploadMaximum ) {
                // Cancel the selection
                $(elementId).checked = false;
                alert( this.evaluateTemplate(this.options.uploadMaximumReached, {activities:this.options.uploadMaximum}) );
            }
    	}
	},
	
	/* Selects all checkboxes in the activity directory, which selects all activities to be read from the device.
	 * uploadMaximum must be -1 or 0 (no limit) for this method to be called.
	 */
	_checkNewDirectoryEntries: function() {
		for( var boxIndex=0; boxIndex < this.activityDirectory.size(); boxIndex++ ) {
		    var activityId = $("activityItemCheckbox" + boxIndex).value;
		    if( this.activityDirectory.getEntry(activityId).isNew ) {
                $("activityItemCheckbox" + boxIndex).checked = this.checkAllBox.checked;
		    }
		}
	},
	
	/* Checks if any activities in directory listing are selected.  Returns true if so, false otherwise.
	 */
	_directoryHasSelected: function() {
		for( var boxIndex=0; boxIndex < this.activityDirectory.size(); boxIndex++ ) {
			if ( $("activityItemCheckbox" + boxIndex).checked == true) {
				return true;
			}
		}
		
		return false;
	},
	
	/* Lists the directory and returns summary data (# of entries).
	 * @param entries {Garmin.ActivityDirectory} 
	 */
	_listDirectory: function(activityDirectory) {
		// clear existing entries
		this._clearHtmlSelect(this.readTracksSelect);
		this._addToActivityTableHeader(activityDirectory);
		
		// loop through each entry
	    var entries = activityDirectory.getEntries();
		for (var i = 0; i < activityDirectory.size(); i++) {
			var entry = entries[i];
			
			// Directory entry
			if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.tcxDir 
    			|| this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.crsDir
    			|| this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitDir
    			|| this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.fitHealthData
    			|| this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir
    			|| this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.gpxDir ){				
				this._addToActivityTable(i, entry);
			}
		}
        if(this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir)
        {
		  return {files: activityDirectory.size()};
        } else {
		  return {tracks: activityDirectory.size()};
        }
	},
	
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
				if (curSeries.getSeriesType() == Garmin.Series.TYPES.history) {
					// activity contains a series of type history, list the track
					this._listTrack(activity, curSeries, i, j);
					numOfTracks++;
				} else if (curSeries.getSeriesType() == Garmin.Series.TYPES.route) {
					// activity contains a series of type route, list the route
					this._listRoute(activity, curSeries, i, j);
					numOfRoutes++;
				} else if (curSeries.getSeriesType() == Garmin.Series.TYPES.waypoint) {
					// activity contains a series of type waypoint, list the waypoint
					this._listWaypoint(activity, curSeries, i, j);				
					numOfWaypoints++;
				}
			}
		}
		if (this.options.showReadRoutesSelect) {
			if(numOfRoutes > 0) {
				Element.show(this.readRoutesElement);
				this.readRoutesSelect.disabled = false;
				this.displayTrack( this._seriesFromSelect(this.readRoutesSelect) );
			} else {
				Element.hide(this.readRoutesElement);
				this.readRoutesSelect.disabled = true;
			}
		}
		if (this.options.showReadTracksSelect) {		
			if(numOfTracks > 0) {
				Element.show(this.readTracksElement);
				this.readTracksSelect.disabled = false;
				this.displayTrack( this._seriesFromSelect(this.readTracksSelect) );
			} else {
				Element.hide(this.readTracksElement);
				this.readTracksSelect.disabled = true;
			}
		}
		if (this.options.showReadWaypointsSelect) {		
			if(numOfWaypoints > 0) {
				Element.show(this.readWaypointsElement);
				this.readWaypointsSelect.disabled = false;
				this.displayWaypoint( this._seriesFromSelect(this.readWaypointsSelect) );
			} else {
				Element.hide(this.readWaypointsElement);
				this.readWaypointsSelect.disabled = true;			
			}
		}	
		return {routes: numOfRoutes, tracks: numOfTracks, waypoints: numOfWaypoints};
	},

    /* Load route names into select UI component.
     * 
     */    
	_listRoute: function(activity, series, activityIndex, seriesIndex) {
		// make sure the select component exists
		if (this.readRoutesSelect) {
			var routeName = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
			this.readRoutesSelect.options[this.readRoutesSelect.length] = new Option(routeName, activityIndex + "," + seriesIndex);
		}		
	},

    /* Load track name into select UI component.
     * 
     */    
	_listTrack: function(activity, series, activityIndex, seriesIndex) {
		// make sure the select component exists
		if (this.readTracksSelect) {
			var startDate = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.startTime).getValue();
			var endDate = activity.getSummaryValue(Garmin.Activity.SUMMARY_KEYS.endTime).getValue();
			var values = {date:startDate.getDateString(), duration:startDate.getDurationTo(endDate)}
			var trackName = this.evaluateTemplate(this.options.trackListing, values)
			this.readTracksSelect.options[this.readTracksSelect.length] = new Option(trackName, activityIndex + "," + seriesIndex);
		}
	},

    /* Load waypoint name into select UI component.
     * 
     */
	_listWaypoint: function(activity, series, activityIndex, seriesIndex) {
		// make sure the select component exists
		if (this.readWaypointsSelect) {
			var wptName = activity.getAttribute(Garmin.Activity.ATTRIBUTE_KEYS.activityName);
			this.readWaypointsSelect.options[this.readWaypointsSelect.length] = new Option(wptName, activityIndex + "," + seriesIndex);
		}
	},

    
    /* Retreive the two index string value from the selected index.
     * Activities are stored in Select objects as strings with 2 
     * numbers: "(index of array), (index of series)", for example:  "1,1".
     * @param Select select - the Select DOM instance
     * @type Garmin.Series
     * @return a Series instance
     */
    _seriesFromSelect: function(select) {
    	var indexesStr = select.options[select.selectedIndex].value;
    	var indexes = indexesStr.split(",", 2);
    	var activity = this.activities[parseInt(indexes[0])];
    	var series = activity.getSeries()[parseInt(indexes[1])];
    	return series;
    },

    
    /** Draws a simple line on the map using the Garmin.MapController.
     * 
     * @param Garmin.Series series - that contains a track. 
     */
    displayTrack: function(series) {
		if(this.options.showReadGoogleMap) {
			this.readMapController.map.clearOverlays();
			this.readMapController.drawTrack(series);
	    }
    },

    /** Draws a point (usualy as a thumb tack) on the map using the Garmin.MapController.
     * @param Garmin.Series series - that contains the lat/lon position of the point. 
     */
    displayWaypoint: function(series) {
		if(this.options.showReadGoogleMap) {
			this.readMapController.map.clearOverlays();
	        this.readMapController.drawWaypoint(series);
		}
    },

    /** Clears overlays from map.
     * 
     */
	clearMapDisplay: function() {
		if(this.options.showReadGoogleMap) {
			this.readMapController.map.clearOverlays();
		}
	},


    ////////////////////////////// WRITE METHODS ////////////////////////////// 
    
    /** Writes any supported data to the device.
     * 
     * Requires that the option writeDataType field be set correctly to any of the following values 
     * located in Garmin.DeviceControl.FILE_TYPES:
     * 
     * 		gpx, crs, binary, goals
     * 
     * @throws InvalidTypeException
     * @see Garmin.DeviceDisplayDefaultOptions.writeDataType
     * @see Garmin.DeviceControl.FILE_TYPES
     */
    writeToDevice: function() {
		var dataType = null;
		var supported = null;
		
		// TODO remove this later 
		// Backwards compatability for deprecated method
		if( this.options.writeDataType != null ) {
		    this.options.writeDataTypes = new Array();
		    this.options.writeDataTypes[0] = this.options.writeDataType;
		}
		
		for (var i = 0; i < this.options.writeDataTypes.length; i++) {
			dataType = this.options.writeDataTypes[i];
			var deviceWriteSupport = this.getController().checkDeviceWriteSupport(dataType);
			//var deviceWriteSupport = this.getController().checkDeviceWriteSupport(this.options.writeDataType);
			
			if (supported == null && deviceWriteSupport == true) {
				supported = dataType;
				
				switch (dataType) {
                    case Garmin.DeviceControl.FILE_TYPES.goals:
					case Garmin.DeviceControl.FILE_TYPES.gpx:
					case Garmin.DeviceControl.FILE_TYPES.crs:					
					case Garmin.DeviceControl.FILE_TYPES.wkt:
					case Garmin.DeviceControl.FILE_TYPES.tcxProfile:
					case Garmin.DeviceControl.FILE_TYPES.nlf:                    
                        var writeData = this.options.getWriteData();
                        var writeDataFileName = this.options.getWriteDataFileName();                        
                        this.getController().writeDataToDevice(dataType, writeData, writeDataFileName);
                        break;
					// TODO Deprecated.  Delete this fella.
					case Garmin.DeviceControl.FILE_TYPES.gpi:
						var xmlDescription = Garmin.GpiUtil.buildMultipleDeviceDownloadsXML(this.options.getGpiWriteDescription());
						this.getController().downloadToDevice(xmlDescription);
						break;					
					case Garmin.DeviceControl.FILE_TYPES.fitSettings:
					case Garmin.DeviceControl.FILE_TYPES.fitSport:
					case Garmin.DeviceControl.FILE_TYPES.fitCourse:
					case Garmin.DeviceControl.FILE_TYPES.binary:
						var xmlDescription = Garmin.GpiUtil.buildMultipleDeviceDownloadsXML(this.options.getBinaryWriteDescription());
						this.getController().downloadToDevice(xmlDescription);
						break;					
					default:
						var error = new Error(Garmin.DeviceControl.MESSAGES.invalidFileType + dataType);
						error.name = "InvalidTypeException";
						this.handleException(error); 
				}
			}
		}
		
		// No supported types found, throw error
		if (supported == null) {
			var error = new Error(this.evaluateTemplate(this.options.unsupportedWriteDataType, {dataType: dataType}));
			error.name = "UnsupportedDataTypeException";
			this.handleException(error);
		}
    },

	/** Call-back triggered before writing to a device.
     * @see Garmin.DeviceControl
     * @event
	 */
    onStartWriteToDevice: function(json) { 
     	this.setStatus(this.options.writingToDevice);
    },

	/** Call-back triggered when write has been cancelled.
     * @see Garmin.DeviceControl
     * @event
	 */
    onCancelWriteToDevice: function(json) { 
    	this.setStatus(this.options.writingCancelled);
    },

    /** Call-back when the device already has a file with this name on it.  Do we want to override?  1 is yes, 2 is no
     * @see Garmin.DeviceControl
     * @event
     */ 
    onWaitingWriteToDevice: function(json) { 
        if(confirm(json.message.getText())) {
            this.setStatus(this.options.overwritingFile);
            json.controller.respondToMessageBox(true);
        } else {
            this.setStatus(this.options.notOverwritingFile);
            json.controller.respondToMessageBox(false);
        }
    },

    /**
     * @event
     */
    onProgressWriteToDevice: function(json) {
	  	if(this.options.showProgressBar) {
	  		this.updateProgressBar(this.progressBarDisplay, json.progress.getPercentage());
	  	}
  		this.setStatus( json.progress.percentage==100 ? this.options.dataDownloadProcessing : json.progress );
    },

    /**
     * @event
     */
    onFinishWriteToDevice: function(json) {
    	this.setStatus(this.options.writtenToDevice);
	    this.resetUI();
		if (this.options.afterFinishWriteToDevice) {
    		this.options.afterFinishWriteToDevice.call(this, json.success);
		}
    },
    
    ////////////////////////////// UTILITY METHODS ////////////////////////////// 
    
    /** Sets up the device control which handles most of the work that isn't user
     * interface related.  The controller is lazy loaded the first time it is called.
     * Early calls must specify the unlock parameter, but read and write methods should
     * not because they should follow a call to findDevice.
     * 
     * Also initializes the RemoteTransfer object used to transfer data to remote servers.
     *   
     * @param Boolean optional request to unlock the plugin if not already done.
     */
	getController: function(unlock) {
		if (!this.garminController) {
			try {
				this.garminController = new Garmin.DeviceControl();
				this.garminController.register(this);
				this.garminController.setPluginRequiredVersion(this.options.pluginRequiredVersion);
				this.garminController.setPluginLatestVersion(this.options.pluginLatestVersion);
				this.garminController.validatePlugin();
	        } catch (e) {
	            this.handleException(e);
	            return null;
	        }
		}
		if (!this.error && unlock && !this.isUnlocked()) {
			if(this.garminController.unlock(this.options.pathKeyPairsArray)) {
	        	this.setStatus(this.options.pluginUnlocked);
			} else {
	        	this.setStatus(this.options.pluginNotUnlocked);
			}
			this.garminRemoteTransfer = new Garmin.RemoteTransfer();
		}
		return this.garminController;
	},

	/** Plugin unlock status
	 * @returns Boolean
	 */
	isUnlocked: function() {
		return (this.garminController && this.garminController.isUnlocked());
	},
	
	/** Sets options for this display object.  Any options that are set will override
	 * the default values.
	 *
	 * @see Garmin.DeviceDisplayDefaultOptions for possible options and default values.
	 * @throws InvalidOptionException
	 * @param Object options - Object with options.
	 */
	setOptions: function(options) {
		for(key in options || {}) {
			if ( ! (key in Garmin.DeviceDisplayDefaultOptions) ) {
				var err = new Error(key+" is not a valid option name, see Garmin.DeviceDisplayDefaultOptions");
				err.name = "InvalidOptionException";
				throw err;
			}
		}
		this.options = Object.extend(Garmin.DeviceDisplayDefaultOptions, options || {});
	},

	/*Sets the size of the select to zero which essentially clears it from 
	 * any values.
	 * 
	 * @param {HTMLElement} select DOM element
	 */
    _clearHtmlSelect: function(select) {
		if(select) {
			select.length = 0;
		}
    },

    /** Set status text if showStatusElement is visible.
     * @param String text to display.
     */
	setStatus: function(statusText) {
	    if(statusText == null) {
	        statusText = '';
	    }
		if(this.options.showStatusElement) {
			
			var statusDisplayString;
			
			if( this.options.showDetailedStatus) {
				statusDisplayString = this._buildDescriptiveStatusString(statusText);
			} else {
				if( statusText.getText ) {
					
					var text = statusText.getText();
					if( text instanceof Array) {
						if( text.length == 0) { 
							statusDisplayString = '';
						} else { 
							statusDisplayString = statusText.getText()[0];
						}
					}
				} else {
					statusDisplayString = statusText;
				}
			} 
		   	this.statusText.innerHTML = statusDisplayString;
		}
	},
	
	/** Builds a descriptive string from the status response (typically JSON, but also can be a plain ol' string).
	 */
	_buildDescriptiveStatusString: function(statusText) {
		if(statusText == null) {
	        statusText = '';
	    }
		var resultString = statusText;
		
		if( statusText.getTitle ) {
			resultString = statusText.getTitle() + "<br />";
		}
		if( statusText.getText ) {
			resultString += statusText.getText();
		}
		
		return resultString;
	},
	
	/** Helper method. Evaluates Prototype's Template object, and returns the evaluated string.
	 * 
	 * Templates should contain fields as in the following format: 'My cow has #{numSpots}' spots!'
	 * Fields are in the following format: {numSpots: 22}
	 * 
	 * @param String template - the template to evaluate     
	 * @param Hash fields - the fields referenced in template
	 * @return the evaluated template string   
	 */
	// TODO Move out to js-util
	evaluateTemplate: function(template, fields) {
	    return new Template(template).evaluate(fields);
	},

    /** Makes progress bar visible.
     * @deprecated
     */
	showProgressBar: function() {
		if(this.options.showStatusElement && this.options.showProgressBar) {
		    Element.show(this.progressBar);
		}
	},

    /** Hides progress bar.
     */
	hideProgressBar: function() {
		if(this.options.showStatusElement && this.options.showProgressBar) {
		    Element.hide(this.progressBar);
		}
	},

    /** Update percentage representation of progress bar.
     * The progress bar starts out as full and then resets to empty.  This is to
     * take care of extremely short transfers.
     * @param {Element} progressBarDisplay - the progress bar display DOM element to update. 
     * @param int percentage completion: 0-100 
     */
	updateProgressBar: function(progressBarDisplay, value) {
		if(this.options.showStatusElement && this.options.showProgressBar && value) {
			var percent = (0 < value && value <= 100) ? value : 100;
		    progressBarDisplay.style.width = percent + "%";
		}
	},
	
	/** Update the progress text of the progress bar.
	 * @param {Element} progressBarText - the progress bar text DOM element to update.
	 * @param String the progress text to display near the progress bar
	 */
	updateProgressBarText: function(progressBarText, text) {
	    if(this.options.showStatusElement && this.options.showProgressBar && text) {
	        progressBarText.innerHTML = text;
	    }
	},
	
    /** Call-back for asynchronous method exceptions.
     * @see Garmin.DeviceControl
     * @event
     */
	onException: function(json) {
		this.handleException(json.msg);
	},
	
    /** Central exception dispatch method will delegate to options.customExceptionHandler
     * if defined or call defaultExceptionHandler otherwise.
     * @param {Error} error to process.
     */	
	handleException: function(error) {
		this.error = true;
   	    //console.debug("Display.handleException error="+error)
		if (this.options.customExceptionHandler) {
			this.options.customExceptionHandler.call(this, error);
		} else {
			this.defaultExceptionHandler(error);
		}
	},
    /** Default exception handler method handles plug-in support/downloads/upgrades. 
     * If options.showStatusElement is true then put error messages in the status div otherwise
     * put it in a alert popup.
     * @param {Error} error - error to process.
     */	
	defaultExceptionHandler: function(error) {
		var errorStatus;
		var hideFromBrowser = false;
		if(error.name == "BrowserNotSupportedException") {
			errorStatus = error.message;
			if (this.options.hideIfBrowserNotSupported) {
				hideFromBrowser = true;
			}
		} else if (error.name == "PluginNotInstalledException" || error.name == "OutOfDatePluginException") {
			errorStatus = error.message;
			errorStatus += " <a href=\""+Garmin.DeviceDisplay.LINKS.pluginDownload+"\" target=\"_blank\">"  + this.options.downloadAndInstall + "</a>";
		} else if (Garmin.PluginUtils.isDeviceErrorXml(error)) {
			errorStatus = Garmin.PluginUtils.getDeviceErrorMessage(error);	
		} else if (error.name == "UnsupportedDeviceException" || error.name == "UnsupportedDataTypeException" || error.name == "RemoteTransferException") {
		    errorStatus = error.message;
		} else {
			errorStatus = error.name + ": " + error.message;	
		}						

		this.setStatus(errorStatus);
		this.resetUI();
		//if no status UI div is defined, make sure the user sees the error
		if (!this.options.showStatusElement && !hideFromBrowser) {
			if (error.name == "PluginNotInstalledException" || error.name == "OutOfDatePluginException") {
				if (window.confirm(error.message+"\n" + this.options.installNow)) {
					window.open(Garmin.DeviceDisplay.LINKS.pluginDownload, "_blank");
				}
			} else {
				alert(errorStatus);
			}
		}
	}
    
};

Garmin.DeviceDisplay.MISC = {
		health_data_id: 'weight'
}

/** Constant defining links referenced in the DeviceDisplay
 * 
 * @struct {public} Garmin.DeviceDisplay.LINKS
 */
Garmin.DeviceDisplay.LINKS = {
	pluginDownload:	"http://www.garmin.com/products/communicator/",
	/** Function that returns a direct download link based on the user's OS.
	 */
	 // TODO Use this when we have a way of getting the latest version all the time.
	pluginDownloadDetectOs: function() { 
            if( BrowserDetect.OS == "Windows") return "http://www.garmin.com/products/communicator/";
            else if( BrowserDetect.OS == "Mac") return "http://www.garmin.com/products/communicator/";
            else return "http://www.garmin.com/products/communicator/"
	      }
};

/** A queue of filters to be applied to activities after data is
 * obtained from the device.  Also used by display to determine
 * if the filtering process is finished; 
 */
var garminFilterQueue = new Array();

/** The default display options for the generated plug-in elements including
 * booleans for which sub-items to show.  Override specific option values by 
 * calling setOptions(optionsHash) on your instance of Garmin.DeviceDisplay
 * to customize your display options.
 *
 * @class Garmin.DeviceDisplayDefaultOptions  
 * @name Garmin.DeviceDisplayDefaultOptions 
 */
Garmin.DeviceDisplayDefaultOptions =
    /** @lends Garmin.DeviceDisplayDefaultOptions */ 
    {
	// ================== Plugin unlock ======================

	/**Unlock plugin when user lands on containing page which may result in security or
	 * plugin-not-installed messages.  Set to false to supress plugin acivity
	 * until user initiates an action.
	 * 
	 * @default true
	 * @type Boolean
	 */
	unlockOnPageLoad:			true,

	/**The array of strings that contain the unlock codes for the plugin.
	* @example [URL1,KEY1,URL2,KEY2] 
	* Add as many url/key pairs as you'd like.
	* @type String[]
	*/
	pathKeyPairsArray:			["file:///C:/dev/", "bd04dc1f5e97a6ff1ea76c564d133b7e"],


	// ================== Global Options ======================
	/**The class name used by various parts of the display to make
	 * CSS styling easier.
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.statusElementId
	 * @see Garmin.DeviceDisplayDefaultOptions.readDataElementId
	 * @see Garmin.DeviceDisplayDefaultOptions.findDevicesElementId
	 * @default "pluginElement"
	 * @type String
	 */
	elementClassName:			"pluginElement",
	
	/**Display link instead of buttons.  Currently this only applies to the 'Find Devices' button.
	 * @default false
	 * @type Boolean
	 */
	useLinks:					false,
	
	/**Action to take if browser is not supported:
	 * if true on't display the application,
	 * else if status bar is visible, display message, otherwise popup an alert dialog
	 * @default false
	 * @type Boolean
	 */
	hideIfBrowserNotSupported:		false,

	/**The function called when an error occurs.  This is here to allow
	 * custom error handling logic.  <br/>
	 * <br/>
	 * The function should accept an arguement of type Error (Javascript 
	 * Error Object) and a second arguement for the DeviceDisplay instance.<br/>
	 * <br/>
	 * <br/>Error.name - the type of the error in a String format.
	 * <br/>Error.message - the detailed message of the error.<br/>
	 *<br/>
	 * Some Errors:<br/>
	 * 	PluginNotInstalledException - the plugin is not installed<br/>
	 *  OutOfDatePluginException - the plugin is out of date<br/>
	 *  BrowserNotSupportedException - the browser is not support by the site<br/>
	 * @example
	 * function(error, display){ display.defaultExceptionHandler(error); }
	 * @type function 
	 * @function
	 */				
	customExceptionHandler:		null, 

	/**Class name to add for all buttons/links that perform an action
	 * @default "actionButton"
	 * @type String 
	 */
	actionButtonClassName:		"actionButton",
	
	/**DEPRECATED - Use autoHideUnusedElements instead!  
	 * Auto-hides read elements when they are not in use.  This is currently used for the Upload Activities use case.
	 * @default false
	 * @type String 
	 */
	autoHideUnusedReadElements: false,
	
	/**Auto-hides elements when they are not in use.  This is used to simulate a UI with different screens, until we design a better way to do this. 
	 * @default false
	 * @type String
	 */
	autoHideUnusedElements:		false,
	
	/**The choice to display the about ('Powered by...') element
	 * @default true
	 * @type Boolean
	 */
	showAboutElement:          true,
	
	/** Optional - Restricts transfer of data to specifically listed devices ONLY.  This is an array list of 
	 * device part numbers, which can be found in the GarminDevice.XML.  
	 * @example ["006-B1018-00"] // Forerunner 310 only
	 * @type Array
	 */
	restrictByDevice:			[],
	
	/** Sets the required version of the Communicator Plugin that is compatible with the given application.
	 * This value is set using an array of the major and minor build numbers.	 * 
	 * @example version 2.2.0.1 is given by [2,2,0,1].
	 * @default [2,2,0,1] (for version 2.2.0.1)
	 * @type Array
	 */
	pluginRequiredVersion:			[2,2,0,1],

	/** Sets the latest plugin version number.  This represents the latest version available for download at Garmin.
	 * We will attempt to keep the default value of this up to date with each API release, but this is not guaranteed,
	 * so set this to be safe or if you don't want to upgrade to the latest API.
	 * 
	 * @param reqVersionArray Array  The latest version to set to.  In the format [versionMajor, versionMinor, buildMajor, buildMinor]
	 * 			i.e. [2,2,0,1]
	 * @default [2,5,1,0] (for version 2.5.1.0)
	 * @type Array
	 */	
	pluginLatestVersion:			[2,5,2,0],

	// ================== Status Element Options ======================
	/**The choice to display the feedback regarding the communications 
	 * with the device.
	 *
	 * @default true 
	 * @type Boolean
	 */
	showStatusElement:			true,
	
	/** The choice to display more detailed status when transferring data to and from device.
	 * @default false
	 * @type Boolean
	 */
	showDetailedStatus:		false,
	
	/**The id of the HTML element where the statusBox is to be rendered.
	 * @type String
	 * @default "statusBox"
	 */
	statusElementId:			"statusBox",
	
	/**The id of the HTML element where the status text messages are to be displayed.
	 * @default "statusText"
	 * @type String
	 */
	statusTextId:				"statusText",
	
	/**The progress bar is a graphical percentage bar used to display 
	 * the amount of reading/writing is complete.
	 * @default true
	 * @type Boolean 
	 */
	showProgressBar:			true,
	
	/** The id of the HTML element where the progress status text is to be displayed.  
	 * This element will be a child of the statusText element.
	 * @default "progressText"
	 * @type String 
	 */
	progressTextId:             "progressText",
	
	/** The class name for the progress status text.  
	 * This element will be a child of the statusText element.
	 * @type String  
	 */
	progressTextClass:          "progressTextClass",
	
	/**The class name for the progress bar container element.
	 * @default 'progressBarClass'
	 * @type String 
	 */
	progressBarClass:           "progressBarClass",
	
	/** The background of the progress bar.  This stays static during transfer.
	 * @default 'progressBarBackClass'
	 * @type String
	 */
	progressBarBackClass:       "progressBarBackClass",
	
	/** The class name for the dynamic progress bar that is overlaid
	 * on top of the progressBar element.  This controls
	 * the part that 'moves'.
	 * @default 'progressBarDisplayClass'
	 * @type String 
	 */
	progressBarDisplayClass:    "progressBarDisplayClass",
	
	/**The container for the progress bar.
	 * @default 'progressBar'
	 * @type String 
	 */
	progressBarId:				"progressBar",
	
	/** The background of the progress bar.  This stays static during transfer.
	 * @default 'progressBarBack'
	 * @type String 
	 */
	progressBarBackId:          "progressBarBack",
	
	/**The id of the displayed progress element.  This is dynamic during transfer.
	 * @default 'progressBarDisplay'
	 * @type String 
	 */
	progressBarDisplayId:		"progressBarDisplay",
	
	/** The class name for the progress bar text.  
	 * @default progressBarTextClass 
	 * @type String 
	 */
	progressBarTextClass:      "progressBarTextClass",
	
	/** The id of the progress bar text.  This is generally the percentage
	 * value text displayed, but could also be in the context of what is being transferred
	 * (i.e. 1 of 5 activities).
	 * @default progressBarText
	 * @type String 
	 */
	progressBarTextId:          "progressBarText",
	
	// Upload Progress bar
	
	/** The id of the HTML element where the upload progress status text is to be displayed.  
	 * This element will be a child of the statusText element.
	 * @type String
	 * @default "uploadProgressText"
	 */
	uploadProgressTextId:             "uploadProgressText",
	
	/** The class name for the uploadProgress status text.  
	 * This element will be a child of the statusText element.
	 * @type String
	 * @default "uploadProgressTextClass"
	 */
	uploadProgressTextClass:          "uploadProgressTextClass",
	
	/**The class name for the uploadProgress bar container element.
	 * @default 'uploadProgressBarClass'
	 * @type String 
	 */
	uploadProgressBarClass:           "uploadProgressBarClass",
	
	/** The background of the uploadProgress bar.  This stays static during transfer.
	 * @default 'uploadProgressBarBackClass'
	 * @type String
	 */
	uploadProgressBarBackClass:       "uploadProgressBarBackClass",
	
	/** The class name for the dynamic uploadProgress bar that is overlaid
	 * on top of the uploadProgressBar element.  This controls
	 * the part that 'moves'.
	 * @default 'uploadProgressBarDisplayClass'
	 * @type String 
	 */
	uploadProgressBarDisplayClass:    "uploadProgressBarDisplayClass",
	
	/**The container for the uploadProgress bar.
	 * @default 'uploadProgressBar'
	 * @type String 
	 */
	uploadProgressBarId:				"uploadProgressBar",
	
	/** The background of the uploadProgress bar.  This stays static during transfer.
	 * @default 'uploadProgressBarBack'
	 * @type String 
	 */
	uploadProgressBarBackId:          "uploadProgressBarBack",
	
	/**The id of the displayed uploadProgress element.  This is dynamic during transfer.
	 * @default 'uploadProgressBarDisplay'
	 * @type String 
	 */
	uploadProgressBarDisplayId:		"uploadProgressBarDisplay",
	
	/** The class name for the uploadProgress bar text.  
	 * @default uploadProgressBarTextClass 
	 * @type String 
	 */
	uploadProgressBarTextClass:      "uploadProgressBarTextClass",
	
	/** The id of the uploadProgress bar text.  This is generally the percentage
	 * value text displayed, but could also be in the context of what is being transferred
	 * (i.e. 1 of 5 activities).
	 * @default uploadProgressBarText
	 * @type String
	 */
	uploadProgressBarTextId:          "uploadProgressBarText",
	
	/** The text to display above the upload progress bar.  This is a static string.
	 * @default "Uploading activities..."
	 * @type String
	 */
	uploadingStatusText:                 "Uploading activities...",
	
	/** Templated string used to display the upload progress status.
	 * The template parameters currentUpload and totalUploads must be included
	 * to work properly, as the default value does.
     * 
     * @default #{currentUpload} of #{totalUploads} completed.
     * @type String
     */
    uploadProgressStatusText: '#{currentUpload} of #{totalUploads} completed.',
	
	//===================  Find Devices Element Options ===============
	
	/**Choice to display the find devices area that will search for connected devices.
	 * @type Boolean
	 * @default true
	 */
	showFindDevicesElement:		true,
	
	/**Choice to display the find devices area that will search for connected devices when the page loads.
	 * @type Boolean
	 * @default true
	 */
	showFindDevicesElementOnLoad:	true,
	
    /**Looks for devices as soon as the page is loaded and the plugin unlocked.
     * This might be particularly annoying in many situations since the plugin 
     * requires the user to authorize access to device information via a 
     * dialog box.
	 * @type Boolean
	 * @default false
     */
	autoFindDevices:			false,
	
	/**Controls the view of the buttons related to find devices (find & cancel) if 
	 * based on if the plugin finds one or more devices.  
	 * When set to <b>false</b> and  
	 * used with {@link showDeviceButtonsOnLoad} =false 
	 * and {@link autoFindDevices} =true these buttons will only
	 * show up if a device is not found (minimizing confusion for the user).
	 * <p>
	 * More granular control is provided on each of the device buttons 
	 * {@link showFindDevicesButton}  and {@link showCancelFindDevicesButton} .
	 * </p>
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement
	 * @type Boolean
	 * @default true
	 */
	showDeviceButtonsOnFound:	true,
	
	/**If true the buttons will show when the page is rendered. 
	 * If false, the buttons will not be displayed until the plugin detects that a device is not found. 
	 * If you choose not to see the buttons at all (regardless if device is found or not) then 
	 * {@link showFindDevicesElement}  should be set to false.
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceButtonsOnFound
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement
	 * @type Boolean
	 * @default true 
	 */
	showDeviceButtonsOnLoad:	true,
	
	/**Allows granular control to hide the find devices button independent
	 * of the {@link showCancelFindDevicesButton}  cancel button contol.
	 * @type Boolean 
	 * @default true
	 */
	showFindDevicesButton:		true,
	
	/**The id referencing the HTML container around the find devices buttons.
	 * This is useful for CSS customizations. 
	 * <p>
	 * @default deviceBox
	 * </p>
	 * @type String 
	 * @default "deviceBox"
	 */
	findDevicesElementId:		"deviceBox",
	
	/**The id referencing the find devices button.  This is useful for
	 * CSS customizations.
	 * 
	 * @type String 
	 * @default findDevicesButton
	 */
	findDevicesButtonId:		"findDevicesButton",
	
	/**The text for the find device button.
	 * @type String
	 * @default "Find Devices" 
	 */		
	findDevicesButtonText:			"Find Devices",	
	
	/**Controls the view of the cancel find devices button. When
	 * set to <b>false</b> the button will never show.  When
	 * set to <b>true</b> the button's behavior will depend on other
	 * settings such as {@link showFindDevicesButton} , 
	 * {@link showDeviceButtonsOnFound} , {@link showDeviceButtonsOnLoad} ,
	 * and {@link showFindDevicesElement} .
	 * @default false
	 * @type Boolean 
	 */	
	showCancelFindDevicesButton:		false,
	
	/**The id referencing the cancel find devices button.  This is useful for
	 * CSS customizations.
	 * @default cancelFindDevicesButton
	 * @type String 
	 */	
	cancelFindDevicesButtonId:	"cancelFindDevicesButton",
	
	/**The text for the cancel find device button.
	 * @type String 
	 * @default "Cancel Find Devices"
	 */		
	cancelFindDevicesButtonText:		"Cancel Find Devices",

	/**Controls the view of the device select box.
	 * When set to <b>true</b> the select device box will show even when only
	 * one device is found.
	 * When set to <b>false</b> the select device box will hide when only
	 * one device is found.
	 * When {@link showFindDevicesElement}  is set to false, the device select
	 * box will never show.
	 * @default false
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectNoDevice
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectOnLoad	  	 
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement	 
	 * @type Boolean 
	 */
	showDeviceSelectOnSingle:	false,
	
	/**Controls the view of the device select box.
	 * When set to <b>true</b> the select device box will show even when
	 * no device is found.
	 * When set to <b>false</b> the select device box will hide when
	 * no device is found.
	 * When {@link showFindDevicesElement}  is set to false, the device select
	 * box will never show.
	 * 
	 * @default true
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectOnSingle
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectOnLoad	  
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement	 
	 * @type Boolean 
	 */	
	showDeviceSelectNoDevice:	false,
	
	/**Controls the view of the device select box.
	 * When set to <b>true</b> the select device box will show when
	 * the display loads.
	 * When set to <b>false</b> the select device box will never be visible.
	 * When {@link showFindDevicesElement}  is set to false, the device select
	 * box will never show.
	 * 
	 * @default true
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectOnSingle
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectNoDevice	  
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement	 
	 * @type Boolean 
	 */		
	showDeviceSelectOnLoad:		true,
	
	/**When more than one device is detected automaticly pick the first device.
	 * This allows single button interfaces to avoid having to ask the user to 
	 * choose the device and keeps the deviceSelect hidden.
	 * 
	 * @default false
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectOnSingle
	 * @see Garmin.DeviceDisplayDefaultOptions.showDeviceSelectNoDevice	  
	 * @see Garmin.DeviceDisplayDefaultOptions.showFindDevicesElement	 
	 * @type Boolean
	 */		
	autoSelectFirstDevice:		false,
	
	//===================  Upload UI Options ===============
	
	/**The id referencing the device select box.  This is useful for
	 * CSS customizations.
	 * 
	 * @default deviceSelectBox
	 * @type String 
	 */		
	deviceSelectElementId:		"deviceSelectBox",
	
	/**The label for the device select box.  Shows up next to the
	 * device select box.
	 * @type String 
	 * @default "Devices: "
	 */		
	deviceSelectLabel:			"Devices: ",	

	/**The id referencing the device select box label.  This is useful for
	 * CSS customizations.
	 * 
	 * @default deviceSelectLabel
	 * @type String 
	 */			
	deviceSelectLabelId:		"deviceSelectLabel",	
	
	/** The class name referencing the device select element.  This is useful for CSS customizations.
	 * 
	 * @default deviceSelectClass
	 * @type String
	 */
	deviceSelectClass:          "deviceSelectClass",
	
	/**The id referencing the device select element.  This is useful for
	 * CSS customizations.
	 * 
	 * @default deviceSelect
	 * @type String 
	 */			
	deviceSelectId:				"deviceSelect",
	
	/**The id referencing the element that displays what device was selected.  This is useful for CSS customizations.
	 *
	 * @default deviceSelected
	 * @type String 
	 */
	deviceSelectedElementId:	"deviceSelected",
	
	/**The label for the device selected element.  This label preceeds the device selected element.  This is useful for CSS customizations.
	 *
	 * @default "Previewing "
	 * @type String 
	 */
	deviceSelectedLabel:		"Previewing ",
	
	/**The id referencing the label for the element that displays what device was selected.  This is useful for CSS customizations.
	 *
	 * @default deviceSelectedLabel
	 * @type String 
	 */
	deviceSelectedLabelId:		"deviceSelectedLabel",
	
	/**The status text that is displayed when no devices are found.  The Find Devices button is 
	 * displayed to allow the user to try again.  To change the button text, set findDevicesButtonText.
	 * 
	 * @type String 
	 * @default "No devices found."
	 */			
	noDeviceDetectedStatusText:	"No devices found.",
	/**The status text that prepends itself to the device name when a single device is found.
	 * 
	 * @type String
	 * @default "Found " 
	 */
	singleDeviceDetectedStatusText: "Found ",

    /**The function called when device search completes successfully or unsuccessfully.
     * The function should have two arguments:
     *  devices {Array<Garmin.Device>}  - an array of device descriptors or an empty array in none were found.
     *  display {Garmin.DeviceDisplay}  - the current instance of the DeveiceDisplay
	 * @example function(devices){...}
	 * @type function 
	 * @function
	 */				
	afterFinishFindDevices:	null,

    /** The function called after all item uploads complete successfully or unsuccessfully.
     * The function will have one argument:
     * display {Garmin.DeviceDisplay - the current instance of the DeviceDisplay
     * @example
     * function(display) {...}
     * @type function
     * @function
     */
    afterFinishUploads: null,

	// ================== Read Element Options ======================
	/**Start reading data from the device when one or more device(s)
	 * is found.
	 * 
	 * @default false
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.autoFindDevices
	 * @see Garmin.DeviceDisplayDefaultOptions.autoWriteData	  
	 * @type Boolean 
	 */					
	autoReadData:				false,
	
	/**Display the user interface associated with reading from
	 * a connected device.
	 * 
	 * @default true
	 * @type Boolean 
	 */
	showReadDataElement:		true,
	
	/**Controls the view of the read data button. When
	 * set to <b>false</b> the button will never show.  When
	 * set to <b>true</b> the button's behavior will depend on other
	 * settings such as {@link showReadDataElement} .
	 * 
	 * @default true
	 * @type Boolean 
	 */	
	showReadDataButton:        true,
	
	/**Controls the view of the read data element. When
	 * set to <b>true</b> the element will only show after a
	 * device has been found.  When set to <b>false</b> the
	 * element will show on page load.
	 * Behavior will depend on other settings such as
	 * and {@link showReadDataElement} .
	 * 
	 * @default false
	 * @type Boolean 
	 */
	showReadDataElementOnDeviceFound:		false,
	
	/**The id referencing the box containing read elements.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readBox
	 * @type String 
	 */		
	readDataElementId:			"readBox",
	
	/**The id referencing the read data button.  This is useful for
	 * CSS customizations.
	 * 
	 * @default readDataButton
	 * @type String 
	 */			
	readDataButtonId:			"readDataButton",
	
	/**The text on the read button.
	 * 
	 * @type String
	 */		
	readDataButtonText:			"Get Data",
	
	/**The text on the read button.
	 * 
	 * @type String
	 */		
	readDataButtonTitleText:			"Get Data",
	
	/**Controls the view of the cancel read data button. When
	 * set to <b>false</b> the button will never show.  When
	 * set to <b>true</b> the button's behavior will depend on other
	 * settings such as {@link showReadDataButton} , 
	 * and {@link showReadDataElement} .
	 * 
	 * @default true
	 * @type Boolean 
	 */	
	showCancelReadDataButton:		true,
	
	/**The id referencing the cancel read data button.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default cancelReadDataButton
	 * @type String 
	 */		
	cancelReadDataButtonId:		"cancelReadDataButton",
	
	/**The text on the cancel read button.
	 * 
	 * @type String 
	 */		
	cancelReadDataButtonText:	"Cancel Get Data",
	
	/**The status text that is displayed when user cancels the
	 * read progress.
	 * 
	 * @type String
	 */		
	cancelReadStatusText:		"Read cancelled",
	
	/**Controls the view of the device select box.
	 * When set to <b>true</b> the select device box will show when
	 * the display loads.
	 * When set to <b>false</b> the select device box will hide when
	 * the display loads.
	 * When {@link showReadDataElement}  is set to false, the results select
	 * box will never show.
	 * 
	 * @default false
	 *  
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement	 
	 * @type Boolean 
	 */		
	showReadResultsSelectOnLoad:	false,

	/**The class to set for select lists that are displaying results
	 * from a read operation.  This is useful for CSS customizations.
	 * 
	 * @default readResultsSelect
	 * @type String 
	 */		
	readResultsSelectClass:			"readResultsSelect",
	
	/**The class to set for results elements.  This is useful for CSS customizations.
	 * 
	 * @default readResultsElement
	 * @type String 
	 */		
	readResultsElementClass:		"readResultsElement",

	/**Display the route select dropdown.  When
	 * <@link showReadDataElement> is set to false, the select
	 * track dropdown will not show.
	 * 
	 * @default true
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */
	showReadRoutesSelect:		true,

	/**The id referencing the read routes element.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readRoutesElement
	 * @type String
	 */		
	readRoutesElementId	:		"readRoutesElement",
		
	/**The id referencing the route select dropdown.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readRoutesSelect
	 * @type String 
	 */		
	readRoutesSelectId:			"readRoutesSelect",
	
	/**The label for the read routes select box.  Shows up next to the
	 * read routes select box.
	 * 
	 * @type String 
	 */		
	readRoutesSelectLabel:		"Routes: ",	
		
	/**The id referencing the read routes select box label.  This is useful for
	 * CSS customizations.
	 * 
	 * @default readRoutesSelectLabel
	 * @type String 
	 */			
	readRoutesSelectLabelId:	"readRoutesSelectLabel",
	
	/** The id referencing the button for uploading selected activities button.  This is useful for CSS customizations.
	 * 
	 * @default readSelectedButton
	 * @type String 
	 */
	readSelectedButtonId: "readSelectedButton",
	
	/** The text label for the upload selected data button.  This is useful for CSS customizations.
	 * 
	 * @default Upload Selected
	 * @type String 
	 */
	readSelectedButtonText: "Upload Selected",		
		
	/**Display the track select dropdown.  When
	 * <@link showReadDataElement> is set to false, the select
	 * track dropdown will not show.
	 * 
	 * @default true
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */
	showReadTracksSelect:		true,		
		
	/**The id referencing the read tracks element.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readTracksElement
	 * @type String 
	 */		
	readTracksElementId:		"readTracksElement",
	
	/**The id referencing the track select dropdown.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readTracksSelect
	 * @type String 
	 */		
	readTracksSelectId:			"readTracksSelect",

	/**The label for the read tracks select box.  Shows up next to the
	 * read tracks select box.
	 * 
	 * @type String 
	 */		
	readTracksSelectLabel:		"Tracks: ",

	/**The id referencing the read tracks select box label.  This is useful for
	 * CSS customizations.
	 * 
	 * @default deviceSelectLabel
	 * @type String 
	 */			
	readTracksSelectLabelId:	"readTracksSelectLabel",

	/**The id referencing the read tracks element.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readTracksElement
	 * @type String 
	 */		
	readWaypointsElementId:		"readWaypointsElement",

	/**Display the waypoint select dropdown.  When
	 * <@link showReadDataElement> is set to false, the select
	 * waypoint dropdown will not show.
	 * 
	 * @default true
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */	
	showReadWaypointsSelect:	true,
	
	/**The id referencing the waypoint select dropdown.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readWaypointsSelect
	 * @type String 
	 */		
	readWaypointsSelectId:		"readWaypointsSelect",
	
	/**The label for the read waypoints select box.  Shows up next to the
	 * read tracks select box.
	 * 
	 * @type String 
	 */		
	readWaypointsSelectLabel:	"Waypoints: ",

	/**The id referencing the read waypoints select box label.  This is useful for
	 * CSS customizations.
	 * 
	 * @default readWaypointsSelectLabel
	 * @type String
	 */			
	readWaypointsSelectLabelId:	"readWaypointsSelectLabel",
	
	/**Display Google map to show tracks and laps that have been read.  When <@link showReadDataElement> is 
	 * set to false, the Google map will not show.
	 * 
	 * @default false
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */		
	showReadGoogleMap:			false,
	
	/**The id referencing the google map display.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readMap
	 * @type String 
	 */		
	readGoogleMapId:			"readMap",
	
	/**DEPRECATED - Use {@link Garmin.DeviceDisplayDefaultOptions.readDataTypes} Tells the plug-in what data type to read from the device.  Options for this
	 * are currently constants listed in {@link Garmin.DeviceControl.FILE_TYPES} , 
	 * and the values are: crs, gpx, gpi, or null to skip this option altogether and get the default data type from 
	 * the device.
	 * <p>
	 * This property works in conjunction with the following functions, based on the datatype:
	 * <p>
	 * For CRS and GPX:	Define the getWriteData() and getWriteDataFileName() functions in your options section.
	 * <p>
	 * For GPI: Define the getWriteData() and getWriteDataFileName() functions in your options section.
	 * 			The getGpiWriteDescription() function replaces getWriteData().
	 * <p>
	 * @default Garmin.DeviceControl.FILE_TYPES.gpx
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @see Garmin.DeviceControl.FILE_TYPES
	 * @see Garmin.DeviceDisplayDefaultOptions.readDataTypes
	 * @type String 
	 * @deprecated
	 */		
	readDataType:	null,
	
	/** OVERRIDES readDataType! 
	 * 
	 * Tells the plug-in what data types to read from the device, in order of preference.  Options for this
	 * are currently constants listed in {@link Garmin.DeviceControl.FILE_TYPES}.
     *
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @see Garmin.DeviceControl.FILE_TYPES
	 * @default TCX, GPX
	 * @example ["FitnessHistory", "GPSData"]
	 * @type Array 
	 */		
	readDataTypes: ["GPSData"],
	
	/**Specify additional options for file listing operations. <br/>
	 * Some readDataTypes {@link Garmin.DeviceControl.FILE_TYPES} such as "readableDir" 
	 * require these options to be set in order to complete successfully. <br/>
     * <strong>fileListingOptions properties:</strong> <br/>
     * {String} dataTypeName: Name from GarminDevice.xml <br/>
     * {String} dataTypeID: Identifier from GarminDevice.xml<br/>
     * {Boolean} computeMD5: Compute MD5 checksum for each listed file<br/>
     * @type {Object} fileListingOptions
	 * @see Garmin.DeviceDisplayDefaultOptions.readDataTypes
	 * @see Garmin.DeviceControl.readDataFromDevice
	 * @see Garmin.DevicePlugin.startReadableFileListing
	 * @default null
     * @example
     * var theOptions = {dataTypeName: 'UserDataSync',
     *                     dataTypeID: 'http://www.topografix.com/GPX/1/1',
     *                     computeMD5: false};
	 */
	fileListingOptions:	null,

	/**Display the dropdown select box for selecting what type
	 * of data to read from the device.  When 
	 * <@link showReadDataElement> is set to false, 
	 * this device type select will not show.
	 * 
	 * @default false
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */		
	showReadDataTypesSelect:	false,
	
	/**The id referencing the data type select.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default readDataTypesSelect
	 * @type String 
	 */			
	readDataTypesSelectId:		"readDataTypesSelect",
	
	/**The function called when data is successfully read from
	 * the device.  The function should have three arguements:<br/>
	 * <br/>
	 * 	dataString - the xml received from the device in String format<br/>
	 *  dataDoc - the xml received from the device in Document format<br/>
	 *  extension - the file type extension of the data, used to determine
	 * 				the type of data received.<br/>
	 *  activities - list of <@link Garmin.Activity> parsed from the xml.<br/>
	 *  display - the display object<br/>
	 * @see Garmin.DeviceDisplayDefaultOptions.Garmin.Activity
	 * @example function(dataString, dataDoc, extension, activities, display){...} 
	 * @type function
	 * @function 
	 */				
	afterFinishReadFromDevice:	null,

	/**Load tracks even if they don't have a timestamp (technically these are
	 * routes).  Set to false if you need to do synchronization with existing
	 * track log database.
	 * 
	 * @default true
	 * @see Garmin.DeviceDisplayDefaultOptions._listTracks
	 * @type Boolean 
	 */		
	loadTracksWithoutATimestamp:	true,
	
	// ================== Write Element Options ======================
	
	/**Start writing data to the device when one or more device(s)
	 * is found.
	 * 
	 * @default false
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.autoFindDevices
	 * @see Garmin.DeviceDisplayDefaultOptions.autoReadData
	 * @type Boolean 
	 */					
	autoWriteData:				false,
	
	/**Display the user interface associated with writing to
	 * a connected device.
	 * 
	 * @default false
	 * @type Boolean 
	 */	
	showWriteDataElement:		false,

	/**Controls the view of the write data element. When
	 * set to <b>true</b> the element will only show after a
	 * device has been found.  When set to <b>false</b> the
	 * element will show on page load.
	 * Behavior will depend on other settings such as
	 * and {@link showWriteDataElement} .
	 * 
	 * @default false
	 * @type Boolean 
	 */
	showWriteDataElementOnDeviceFound:		false,
	
	/**The id referencing the box containing write elements.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default writeBox
	 * @type String 
	 */
	writeDataElementId:			"writeBox",
	
	/**The id referencing the write data button.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default writeDataButton
	 * @type String 
	 */		
	writeDataButtonId:			"writeDataButton",
	
	/**The text on the write button.
	 * 
	 * @type String 
	 */		
	writeDataButtonText:		"Write",
	
	/**Controls the view of the cancel write data button. When
	 * set to <b>false</b> the button will never show.  When
	 * set to <b>true</b> the button's behavior will depend on other
	 * settings such as {@link showWriteDataButton} , 
	 * and {@link showWriteDataElement} .
	 * 
	 * @default true
	 * @type Boolean 
	 */	
	showCancelWriteDataButton:		true,
	
	/**The id referencing the cancel write data button.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default cancelWriteDataButton
	 * @type String 
	 */		
	cancelWriteDataButtonId:	"cancelWriteDataButton",
	
	/**The text on the cancel write button.
	 * 
	 * @type String 
	 */		
	cancelWriteDataButtonText:  "Cancel Write",
	
	/**The function called when data is successfully written to
	 * the device.  This method takes two parameters:
	 *  success Boolean  - true if data was written
	 *  display {Garmin.DeviceDisplay}  - the current instance of the DeviceDisplay
	 * @type function 
	 * @example function(success, display) {...}
	 * @function
	 */				
	afterFinishWriteToDevice:	null,
	
	/**Array of filters to sequencialy apply to activities before being sent or displayed.
	 * 
	 * @see Garmin.FILTERS
	 * @type Array dataFilters
	 * @example [Garmin.FILTERS.historyOnly]
	 */				
	dataFilters:				[],	
	
	/**The function called by the display in order to acquire the data
	 * that will be written to the device during the writing operation.
	 * 
	 * This function should return a String.
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.getWriteDataFileName
	 * @type function 
	 * @example function() { return $("myTextAreaId").value; }
	 * @function
	 */
	getWriteData:				null,
	
	/**The function called by the display in order to acquire the filename
	 * of the data that will be written to the device during the writing 
	 * operation.
	 * 
	 * This function should return a String.
	 * @default function(){ return "myData.gpx"; }
	 * @see Garmin.DeviceDisplayDefaultOptions.getWriteData
	 * @type function
	 */
	getWriteDataFileName:		function(){ return "myData.gpx"; } ,
	
	/**DEPRECATED (see {@link getBinaryWriteDescription}) - The function called by the display in order to acquire the data
	 * that will be written to the device during the writing operation.
	 * 
	 * This function should return an array of strings where adjacent items
	 * indicate the source (URL) of the gpi to be written and the destination
	 * (device path and filename) to write to the device.
	 *
	 * e.g.: [SOURCE,DESTINATION,SOURCE2,DESTINATION2] add as many source/destination 
	 * pairs as you'd like.
	 * 
	 * @example function() { return ["http://developer.garmin.com/SampleGpi.gpi", "Garmin\\POI\\Test.gpi"] } 
	 * @type function 
	 * @function
	 */
	getGpiWriteDescription:		null,
	
	/**The function called by the display in order to acquire the data
	 * that will be written to the device during the writing operation.
	 * 
	 * This function should return an array of strings where adjacent items
	 * indicate the source (URL) of the binary data to be written and the destination
	 * (device path and filename) to write to the device.
	 *
	 * e.g.: [SOURCE,DESTINATION,SOURCE2,DESTINATION2] add as many source/destination 
	 * pairs as you'd like.
	 * 
	 * @example function() { return ["http://developer.garmin.com/SampleGpi.gpi", "Garmin\\POI\\Test.gpi"] }
	 * @type function
	 * @function 
	 */
	getBinaryWriteDescription:		null,
	
	/**DEPRECATED - Use {@link Garmin.DeviceDisplayDefaultOptions.writeDataTypes}
	 * Tells the plug-in what data type to write to the device.  
	 * Options are "gpx" which will use {@link getWriteData}  to get the data
	 * or "gpi" which will use {@link getGpiWriteDescription}  to get the data to
	 * save to the device.
	 *
	 * @default Garmin.DeviceControl.FILE_TYPES.gpx
	 * @see Garmin.DeviceDisplayDefaultOptions.showWriteDataElement
	 * @see Garmin.DeviceDisplayDefaultOptions.getWriteData
	 * @see Garmin.DeviceDisplayDefaultOptions.getGpiWriteDescription
	 * @see Garmin.DeviceDisplayDefaultOptions.writeDataTypes
	 * @type String 
	 * @deprecated
	 */		
	writeDataType:	null,
	
	/** OVERRIDES writeDataType!
	 *
	 * Tells the plug-in what data type to write to the device.  
	 * Options are "gpx" which will use {@link getWriteData}  to get the data
	 * or "gpi" which will use {@link getGpiWriteDescription}  to get the data to
	 * save to the device.
	 *
	 * @default Garmin.DeviceControl.FILE_TYPES.gpx
	 * @see Garmin.DeviceDisplayDefaultOptions.showWriteDataElement
	 * @see Garmin.DeviceDisplayDefaultOptions.getWriteData
	 * @see Garmin.DeviceDisplayDefaultOptions.getGpiWriteDescription
	 * @type Array 
	 */		
	writeDataTypes:	["GPSData"],
	
	//===================  Activity Directory Element Options ===============
	
	/** Displays the activity directory table, which essentially 
	 * allows users to select individual activities to read from 
	 * the device.  showReadDataElement must be true for this 
	 * to display.
	 * 
	 * @default true
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.showReadDataElement
	 * @type Boolean 
	 */
	showActivityDirectoryElement:	true,
	
	/**
	 * The classname referencing the element that lists individual activities. This element includes the 
	 * data table as well as the related buttons (such as for uploading data). This is useful for CSS customizations.
	 * @default activityDirectoryClass
	 * @type String 
	 */
	activityDirectoryClass:        "activityDirectoryClass",
	
	/** The id referencing the table that holds the activity directory data.  This is useful for CSS customizations.
	 * 
	 * @default activityDirectoryData
	 * @type String 
	 */
	activityDirectoryDataId: "activityDirectoryData",
	
	/** The id referencing the element that lists individual activities. This element includes the 
	 * data table as well as the related buttons (such as for uploading data). This is useful for CSS customizations.
	 * 
	 * @default activityDirectory
	 * @type String 
	 */
	activityDirectoryElementId: "activityDirectory",
	
	/** The id referencing the header of the activity table that lists individual activities. 
	 * This is useful for CSS customizations.
	 * @default activityTableHeader
	 * @type String 
	 */
	activityTableHeaderId:      "activityTableHeader",
	
	/** The id referencing the activity table that lists individual activities. This table contains
	 * the activity data. This is useful for CSS customizations.
	 * 
	 * @default activityTable
	 * @type String 
	 */
	activityTableId:           "activityTable",
	
	/** The function called after an activity entry is added to the activity listing table.
	 * Useful for updating the status cell in a unique way.
	 * @param Boolean index - the index in the table where the activity was added
	 * @param {Garmin.Activity} activity
	 * @param {Element} statusCell - the status td element associated with the activity
	 * @param {Element} checkbox - the checkbox input element associated with the activity
	 * @param Object row - the table row element associated with the activity
	 * @param {Garmin.ActivityMatcher} activityMatcher - the activity matcher object, if available.
	 * @param {Garmin.DeviceDisplay} display - the current instance of the display object
	 * @type function
	 * @function
	 * @example function(index, activity, statusCell, checkbox, row, this.activityMatcher, this) {...}
	 */
	afterTableInsert:          null,
	
	//===================  Upload Options ===============

    /** Class name for the cancel upload button. 
     * Useful for CSS customizations.
     * @default cancelUploadButtonClass
     * @type String 
     */
	cancelUploadButtonClass: 'cancelUploadButtonClass',
	
	/** Element ID for the cancel upload button.
	 * @default 'cancelUploadButton'
	 * @type String 
	 */
	cancelUploadButtonId: 'cancelUploadButton',
	
	/** Text to display on Cancel upload button.
	 * @default '(Cancel)'
	 * @type String 
	 */
	cancelUploadButtonText: '(Cancel)',
	
	/**Display the user interface associated with uploading new activities from a connected device.  
	 * 
	 * @default true
	 * @type Boolean 
	 */
	showUploadNewButton: false,
	
	/**Display the user interface associated with uploading health data from a connected device.  
	 * 
	 * @default true
	 * @type Boolean 
	 */
	showHealthDataUploadButton: false,
	
	/** The id referencing the button for uploading data to a server. This is useful for CSS customizations.
	 * 
	 * @default uploadNewButton
	 * @type String  
	 */
	uploadNewButtonId: "uploadNewButton",
	
	/** The id referencing the button for uploading data to a server. This is useful for CSS customizations.
	 * 
	 * @default uploadNewButton
	 * @type String  
	 */
	uploadHealthDataButtonId: "uploadHealthDataButton",
	
	/** The text label for the upload data button.  This is useful for CSS customizations.
	 * 
	 * @default activityDirectory
	 * @type String 
	 */
	uploadNewButtonText: "Upload new activities",
	
	/** The title text for the upload data button.  This is useful for CSS customizations.
	 * 
	 * @default activityDirectory
	 * @type String 
	 */
	uploadNewButtonTitleText: "Upload new activities",
	
	/** The text label for the upload data button.  This is useful for CSS customizations.
	 * 
	 * @default activityDirectory
	 * @type String 
	 */
	uploadHealthDataButtonText: "Upload Health Data",
	
	/** The title for the upload data button.  This is useful for CSS customizations.
	 * 
	 * @default activityDirectory
	 * @type String 
	 */
	uploadHealthDataButtonTitleText: "Upload Health Data",
	
	/** Select activities to upload, rather than all activities read off the device.
	 * 
	 * @default false
	 * @type Boolean 
	 */
	uploadSelectedActivities: false,
	
	/** Upload compressed data.  Compressed data is gzip base 64 encoded.  Compression is supported
	 * for fitness history activities only.
	 * 
	 * @default false
	 * @type Boolean
	 */
	uploadCompressedData: false,
	
	/** Maximum number of activities allowed for upload selection.  Users are notified during the selection
	 * process if they try to exceed this value.  uploadSelectedActivities must be set to true 
	 * for this to work.  Note that if this value is > 0, the 'select all activities' feature will not be available.  
	 * 
	 * Set this value to 0 for no limit with NO 'select all activities' checkbox.
	 * 
	 * @default -1 (no limit with 'select all' checkbox)
	 * @type int
	 * @see Garmin.DeviceDisplayDefaultOptions.uploadSelectedActivities
	 */
	uploadMaximum: -1, 
	
	// ================== Post to Server ======================
	
	/** The function called when a single activity is finished reading, and the data is ready to post.
	 * Use this if you need a custom way of uploading data to your server (advanced users).<br/>  
	 * <br/>
	 * Otherwise, if you just need an AJAX call, use Send Data. (See {@link this.options.sendDataUrl} 
	 * and {@link this.options.sendDataOptions}.)
	 *  
     * Parameters to this function:<br/>
     *  <br/>datastring String  - The XML datastring of the activity read from the device.<br/>
     *  <br/>display {GarminDeviceDisplay} - the display object
     * @example function(datastring, display){...}
     * @function
     * @type function  
	 */
	postActivityHandler: 		null, 
	
	/** Show the element to send data to a remote server.
	 * 
	 * @type Boolean 
	 */
	showSendDataElement:		false,
	
	/**Controls the view of the send data element. When
	 * set to <b>true</b> the element will only show after a
	 * device has been found.  When set to <b>false</b> the
	 * element will show on page load.
	 * Behavior will depend on other settings such as
	 * and {@link showSendDataElement} .
	 * 
	 * @default false
	 * @type Boolean 
	 */
	showSendDataElementOnDeviceFound:		false,
	
	/** The callback function to set the request options when hitting the remote server.
	 * This function is passed these parameters:
	 * 
	 * options - The options object.  Use this object to set the property values.  
	 *           Some may already be set by sendDataOptions.  getSendOptions will overwrite any existing ones. 
	 * deviceXml - the active device's XML
	 * data - read data from the device, if any
	 * 
	 * Don't forget to return the options object!
	 * 
	 * @type function 
	 * @example function(options, deviceXml, data) {} 
	 * @function
	 */
	getSendOptions:					null,
	
	/** The URL to send the data to.
	 * 
	 * @type String 
	 */
	sendDataUrl:					null,
	
	/** The AJAX request options to use for sending data to a server.  To be used in conjunction with {@link sendDataUrl} .
	 * 
	 * See the <a href="http://www.prototypejs.org/api/ajax/options">AJAX options page</a> for configurable options and default values. 
	 * 
	 * @type Object 
	 */
	sendDataOptions:				null,
	
	/**The id referencing the box containing send elements.  This is 
	 * useful for CSS customizations.
	 * 
	 * @default sendBox
	 * @type String 
	 */		
	sendDataElementId:			"sendBox",
	
	/**The id referencing the send data button.  This is useful for
	 * CSS customizations.
	 * 
	 * @default sendDataButton
	 * @type String 
	 */			
	sendDataButtonId:			"sendDataButton",
	
	/**The text on the read button.
	 * 
	 * @type String 
	 */		
	sendDataButtonText:			"Send Data",
	
	/** The callback function that will be passed the AJAX response 
	 * after making the request.  The display object is passed in, so you can 
	 * make follow-up read or write calls if so desired.
	 *
	 * @see Garmin.DeviceDisplayDefaultOptions.sendDataUrl
	 * @see Garmin.DeviceDisplayDefaultOptions.getSendOptions
	 * @param response object (see <a href="http://www.prototypejs.org/api/ajax/response">Ajax.Response</a> for response attributes)
	 * @default null
	 * @type function
	 * @function 
	 * @example function(response){}
	 */
	afterFinishSendData:			null,

	//===================  Device Browser Element Options ===============
	
	/** The callback function that will be called when the user selects a
	 * device from the device browser. 
	 *
	 * @default null
	 * @param deviceNum the device number of the selected device
	 * @param devices an array of the detected devices 
	 * @param deviceXml the device xml of the selected device
	 * @type function 
	 * @function
	 * @example function(deviceXml){...}
	 */
	afterSelectDevice:       null,
	
	/** Show the device browser list.  Currently the browser list is only available
	 * when activity directory reading is on (readDataType = Garmin.DeviceControl.FILE_TYPES.tcxDir). 
	 * 
	 * @default true
	 * 
	 * @see Garmin.DeviceDisplayDefaultOptions.uploadSelectedActivities
	 * @type Boolean 
	 */
	useDeviceBrowser:   true,
	
	/**Display list instead of select drop down.  
	 * @default false
	 * @type Boolean 
	 */
	useDeviceSelectList:                 false,
	
	/** The classname for the device browser element.  This is useful for custom CSS.
	 * @default deviceBrowserBoxClass
	 * @type String 
	 */
	deviceBrowserElementClass:    "deviceBrowserBoxClass",
	
	/** The id referencing the device browser element.  This is useful for custom CSS.
	 * @default deviceBrowserList
	 * @type String 
	 */
	deviceBrowserElementId:    "deviceBrowserBox",
	
	/** The classname for the device browser text label.  This is useful for custom CSS.
	 * @default deviceBrowserLabelClass
	 * @type String  
	 */
	deviceBrowserLabelClass:      "deviceBrowserLabelClass",
	
	/** The id referencing the device browser text label.  This is useful for custom CSS.
	 * @default deviceBrowserLabelId
	 * @type String  
	 */
	deviceBrowserLabelId:      "deviceBrowserLabel",
	
	/** The text label to display above the device browser list.  This is useful for custom CSS.
	 * @default 'Browse devices:'
	 * @type String  
	 */
	deviceBrowserLabel:        "Browse devices:",
	
	/** The id referencing the device browser list.  This is useful for
	 * CSS customizations.
	 * 
	 * @default deviceBrowserList
	 * @type String 
	 */
	deviceBrowserListId:       "deviceBrowserList",
	
	/** The classname referencing the button that displays the UI for browsing the user's
	 * file system.  
	 * @default browseComputerButtonClass
	 * @type String 
	 */
	browseComputerButtonClass:    "browseComputerButtonClass",
	
	/** The id referencing the button that displays the UI for browsing the user's
	 * file system.  
	 * @default browseComputerButton
	 * @type String 
	 */
	browseComputerButtonId:    "browseComputerButton",
	
	/** The text for the button that displays the UI for browsing the user's file system.
	 * @default "Browse Computer"
	 * @type String 
	 */
	browseComputerButtonText:  "Browse Computer",
	
	/** The title the button that displays the UI for browsing the user's file system.
	 * @default "Browse Computer"
	 * @type String 
	 */
	browseComputerButtonTitleText:  "Browse Computer",
	
	/** The classname for the browse computer container.
	 * @type String
	 */
	browseComputerElementClass:    "browseComputerElementClass",
	
	/** The id for the browse computer container. 
	 * @type String
	 */
	browseComputerElementId:       "browseComputerElement",
	
    /** The url of the iframe to load into the browse computer container.
     * The iframe object is generated by the API.  See browseComputerElementClass
     * to change the dimensions of the iframe.
     * @type String
     */	
	browseComputerContentUrl:       "about:blank",
	
	/** The text label to display in the browser list for browsing the computer.
	 * @default My Computer
	 * @type String 
	 */
	browseComputerLabel:       "My Computer",
	
	/** The text to display on the loading content screen.  This is useful for internationalization.
	 * @default 'Loading content...'
	 * @type String 
	 */
	loadingContentText:        "Loading content from #{deviceName}, please wait...",
	
	/** The text to display on the loading health content screen.  This is useful for internationalization.
	 * @default 'Loading content...'
	 * @type String 
	 */
	loadingHealthContentText:        "Loading health content from #{deviceName}, please wait...",
	
	/** The text to display to change the device.  This is useful for internationalization.
	 * @default '(Change)'
	 * @type String 
	 */
	changeDeviceButtonText: "(Change)",
	
	/** The classname referencing the change device element, which is a link that allows
	 * the user to change device in list mode.
	 * @default changeDevice
	 * @type String 
	 */
	changeDeviceClass:            "change",
	
	/** The id referencing the change device element, which is a link that allows
	 * the user to change device in list mode.
	 * @default changeDevice
	 * @type String 
	 */
	changeDeviceElementId:                "changeDevice",
	
	/** The class name referencing the connected devices label displayed when using
	 * the device select list.
	 * @default connectedDevicesClass
	 * @type String 
	 */
	connectedDevicesClass:                 "connectedDevicesClass",
	
	/** The image to display in the connected devices label.
	 * @type String 
	 */
	connectedDevicesImg:                   null,
	
	/** The label for connected devices, displayed when using the device select list.
	 * @default 'Connected devices:'
	 * @type String 
	 */
	connectedDevicesLabel:                 "Connected devices:",
	
	/** The classname referencing the preview device element, which displays an image
	 * depending on the device selected.
	 * 
	 * @default previewDevice
	 * @see Garmin.DeviceDisplayDefaultOptions.previewDeviceDefaultImg
	 * @type String 
	 */
	previewDeviceClass:           "preview",
	
	/** The id referencing the change device element, which is a link that allows
	 * the user to change device in list mode.
	 * @default changeDevice
	 * @type String 
	 */
	previewDeviceElementId:                 "previewDevice",
	
	/** The default image URL to display for any selected device. 
	 * @see Garmin.DeviceDisplayDefaultOptions.previewDeviceId
	 * @see Garmin.DeviceDisplayDefaultOptions.useDeviceSelectList
	 * @type String 
	 */
	previewDeviceDefaultImg:   "../../../theme/upload/images/icon-edge.png",
	
	/**The maximum number of characters to display for a device name.
	 *@type int 
	 */
	deviceLabelMaxSize: 20,
	
	/** Allows the user to browse their file system for upload. 
	 * {@link uploadSelectedActivities} must be set to true for this
	 * option to take effect.
	 * @default false
	 * @type Boolean 
	 */
	showBrowseComputer: false,
	
	// ================== Synchronization ======================
	
	/**Detect new activities in the activity listing by comparing the device list to a server list.
	 * @default true
	 * @type Boolean 
	 */
	detectNewActivities:                  false,
	
	/**
	 * The URL to make the sync request to.  To be used in conjuntion with {@link syncDataOptions}.
	 * {@link detectNewActivities} must be set to true.
	 * @type String 
	 */
	syncDataUrl:                          null,
	
	/** The AJAX request options to use for posting the parameters to a server.  To be used in conjunction with {@link syncDataUrl} .
	 * 
	 * See the <a href="http://www.prototypejs.org/api/ajax/options">AJAX options page</a> for configurable options and default values. 
	 * 
	 * @type Object 
	 */
	syncDataOptions:                      null,
	
	// ================== Internationalization ======================
	/** Status message exposed for internationalization. @type String  */
	pluginUnlocked: "Plug-in initialized.  Find some devices to get started.",
	/** Status message exposed for internationalization. @type String  */
	pluginNotUnlocked: "The plug-in was not unlocked successfully",
	/** Read data selection option exposed for internationalization. @type String  */
	gpsData: "GPS Data",
	/** Read data selection option exposed for internationalization. @type String  */
	trainingData: "Training Data",
	/** Status message exposed for internationalization. Prepended to the device name after user selects which device to use.  
	 * i.e. "Using Diana's Forerunner 305"  
	 * @default "Using "
	 * @type String  
	 */
	usingDevice: "Using #{deviceName}",
	/** Track list box item template exposed for internationalization. @type String  */
	trackListing: "#{date}  (Duration: #{duration} )",
	/** Status message template exposed for internationalization. @type String  */
	dataFound: "#{routes}  routes, #{tracks}  tracks and #{waypoints}  waypoints found",
	/** Status message template for file listings exposed for internationalization. @type String  */
	filesFound: "#{files} files found",
	/** Status message exposed for internationalization. @type String  */
	writingToDevice: "Writing data to the device",
	/** Status message exposed for internationalization. @type String  */
	writtenToDevice: "Data written to the device",
	/** Status message exposed for internationalization. @type String  */
	writingCancelled: "Writing cancelled",
	/** Status message exposed for internationalization. @type String  */
	overwritingFile: "Overwriting file",
	/** Status message exposed for internationalization. @type String  */
	notOverwritingFile: "Will not be overwriting file",
	/** Status message exposed for internationalization. @type String  */
	lookingForDevices: "Looking for connected devices...",
	/** Status template exposed for internationalization. When single device is found. @type String  */
	foundDevice: "Found #{deviceName} ",
	/** Status template exposed for internationalization. When multiple devices are found. @type String  */
	foundDevices: "Found #{deviceCount}  devices",
	/** Status message exposed for internationalization. When user cancels Find Devices.@type String  */
	findCancelled: "Find cancelled",
	/** Status message exposed for internationalization. When reading data from the device. @type String  */
	dataReadProcessing: "Data read from device. Processing...",
	/** Status message exposed for internationalization. When large files are being written to device.  @type String  */
	dataDownloadProcessing: "Processing data to write... ",
	/** Status message exposed for internationalization. When uploads have completed.  @type String  */
	uploadsFinished: "Transfer Complete!",
	/** Error message exposed for internationalization. @type String  */
	noParseSupportForType: "The plugin does not have parsing support for file type ",
	/** Request message exposed for internationalization. @type String  */
	installNow: "Install now?",
	/** Request message exposed for internationalization. @type String  */
	downloadAndInstall: "Download and install now",
	/** Powered-by message. Required for plugin license agreement. @type String  */
	poweredByGarmin: "Powered by <a href='http://www.garmin.com/products/communicator/' target='_new'>Garmin Communicator</a>",
	/** Status message for devices that are not in the allowed devices list, or do not support any of the application's supported filetypes. @type String  */
	unsupportedDevice:	"Your device is not supported by this application.",
	/** Error message to display when user attempts to upload 0 activities. @type String  */
	errorActivitySelect: "No selected or new activities to upload.",
	/** Tooltip message to describe the check all box for the activity directory. Useful for CSS customizations and internationalization.  @type String */
	activityDirectoryCheckAllTooltip: "Check all new",
	/** DEPRECATED Column header for the Date fields in the activity directory.  Useful for CSS customizations and internationalization.  @type String @deprecated @see Garmin.DeviceDisplayDefaultOptions.getActivityDirectoryHeaderIdLabel */
	activityDirectoryHeaderDate: "<b>Date</b>",
	/** Column header for the Date/Name fields in the activity directory.  Useful for CSS customizations and internationalization. Default to Date if nothing is specified @type String  */
	getActivityDirectoryHeaderIdLabel: function () { return this.fileTypeRead == Garmin.DeviceControl.FILE_TYPES.readableDir ? "<b>File</b>" : "<b>Date</b>"; },
	/** Column header for the Duration fields in the activity directory.  Useful for CSS customizations and internationalization.  @type String  */
	activityDirectoryHeaderDuration: "<b>Duration</b>",
	/** Column header for the Status fields in the activity directory.  Useful for CSS customizations and internationalization.  @type String  */
	activityDirectoryHeaderStatus: "<b>Status</b>",
	/** Error message to display when attempting to write to a device that does not support the provided datatype. Useful for CSS customizations and internationalization.  @type String  */
	unsupportedReadDataType: "Your device does not support reading of the type #{dataType}.",
	/** Error message to display when attempting to write to a device that does not support the provided datatype. Useful for CSS customizations and internationalization.  @type String  */
	unsupportedWriteDataType: "Your device does not support writing of the type #{dataType}.",
	/** Status message to display when activities are being uploaded.  @type String  */
	uploadingActivities: "Uploading activities...",
	/** Error message exposed for internationalization.  When maximum selection for upload is reached. @type String  */
	uploadMaximumReached: "Maximum upload selection of #{activities} activities reached.",
	/** The innerHTML to use for status while an activity is processing (for upload).  Define using the img tag if you wish to use an image.  
	 * 
	 * @default '<img src="style/ajax-loader.gif" />'
	 * 
	 * which is an animated loader image.  You can customize this to be text instead of an image by not using the image tags.   
	 * @type String  */
	statusCellProcessingImg: '<img src="style/ajax-loader.gif" width="15" height="15" />',
	/** Status text to display when the plugin is sending data to a remote server.  @type String  */
	sendingDataToServer: "Sending data from #{deviceName} to server...",
	/** Error message to display when there is an error getting the HTTP response back from the HTTP request.  @type String  */
	errorHttpResponse: "Unable to get valid response from HTTP request object.  Ensure that your options are set correctly and try again.",
	/** Status text to display when none of the activities from the device meet the filter requirements. @type String  */
	noFilteredActivities: "No new activities found on device.",
	noActivitiesOnDevice: "No activities found on selected device.",
	
	/**
	 * The content to be displayed as the label for a Health (Weight/Monitoring) file
	 */
	
	health_data_label: "Your health data"
} ;


/*
 * DisplayBootstrap - not sure what form this should take: class or global var 
 * It should probably be in the Garmin namesapce.
 * 
 * Dynamic include of required libraries and check for Prototype
 * Code taken from scriptaculous (http://script.aculo.us/) - thanks guys!
 */
var GarminDeviceDisplay = {
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
			throw("GarminDeviceDisplay requires the Prototype JavaScript framework >= 1.5.0");
		}

		$A(document.getElementsByTagName("script"))
		.findAll(
			function(s) {
				return (s.src && s.src.match(/GarminDeviceDisplay\.js(\?.*)?$/))
			}
		)
		.each(
			function(s) {
				var path = s.src.replace(/GarminDeviceDisplay\.js(\?.*)?$/,'../../');
				var includes = s.src.match(/\?.*load=([a-z,]*)/);
				var dependencies = 'garmin/device/GarminDeviceControl' +
									',garmin/device/GarminDevicePlugin' +
									',garmin/device/GarminGpsDataStructures' +
									',garmin/device/GoogleMapController' +
									',garmin/device/GarminDevice' +
									',garmin/device/GarminPluginUtils' +
									',garmin/api/GarminRemoteTransfer' +
									',garmin/util/Util-XmlConverter' +
									',garmin/util/Util-Broadcaster' +
									',garmin/util/Util-DateTimeFormat' +
									',garmin/util/Util-BrowserDetect' +
									',garmin/util/Util-PluginDetect' +
									',garmin/device/GarminObjectGenerator' +
									',garmin/activity/GarminMeasurement' +
									',garmin/activity/GarminSample' +
									',garmin/activity/GarminSeries' +
									',garmin/activity/GarminActivity' +
									',garmin/activity/GarminActivityDirectory' +
									',garmin/activity/GarminActivityFilter' +
									',garmin/activity/GarminActivityMatcher' +
									',garmin/activity/TcxActivityFactory' +									
									',garmin/activity/GpxActivityFactory'+
									',garmin/directory/GarminDirectoryFactory'+
									',garmin/directory/GarminDirectoryUtils'+
									',garmin/directory/GarminFile';
			    (includes ? includes[1] : dependencies).split(',').each(
					function(include) {
						GarminDeviceDisplay.require(path+include+'.js') 
					}
				);
			}
		);
	}	
}

GarminDeviceDisplay.load();
