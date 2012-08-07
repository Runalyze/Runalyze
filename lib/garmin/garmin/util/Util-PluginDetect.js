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

 * @fileoverview PluginDetect from http://developer.apple.com/internet/webcontent/detectplugins.html. Not API.
 * @version 1.9
 */
/** A library for detecting the browser's plugins, by Apple
 * Found at http://developer.apple.com/internet/webcontent/detectplugins.html
 *
 * Modification has been made to the original source.
 * @class PluginDetect
 */
var detectableWithVB = false;
var PluginDetect = {
	
	init: function() {
		// Here we write out the VBScript block for MSIE Windows
		if ((navigator.userAgent.indexOf('MSIE') != -1) 
			&& (navigator.userAgent.indexOf('Win') != -1)) {
		    document.writeln('<script language="VBscript">');
		
		    document.writeln('\'do a one-time test for a version of VBScript that can handle this code');
		    document.writeln('detectableWithVB = False');
		    document.writeln('If ScriptEngineMajorVersion >= 2 then');
		    document.writeln('  detectableWithVB = True');
		    document.writeln('End If');
		
		    document.writeln('\'this next function will detect most plugins');
		    document.writeln('Function detectActiveXControl(activeXControlName)');
		    document.writeln('  on error resume next');
		    document.writeln('  detectActiveXControl = False');
		    document.writeln('  If detectableWithVB Then');
		    document.writeln('     detectActiveXControl = IsObject(CreateObject(activeXControlName))');
		    document.writeln('  End If');
		    document.writeln('End Function');
		
		    document.writeln('</script>');
		}
	},
	
	canDetectPlugins: function() {
	    if( detectableWithVB || (navigator.plugins && navigator.plugins.length > 0) ) {
			return true;
	    } else {
			return false;
	    }			
	},
	
	detectFlash: function() {
	    var pluginFound = PluginDetect.detectPlugin('Shockwave','Flash'); 
	    // if not found, try to detect with VisualBasic
	    if(!pluginFound && detectableWithVB) {
			pluginFound = detectActiveXControl('ShockwaveFlash.ShockwaveFlash.1');
	    }
	    // check for redirection
	    return pluginFound;
	},
	
	detectGarminCommunicatorPlugin: function() {
	    var pluginFound = PluginDetect.detectPlugin('Garmin Communicator');
	    // if not found, try to detect with VisualBasic
	    if(!pluginFound && detectableWithVB) {
			pluginFound = detectActiveXControl('GARMINAXCONTROL.GarminAxControl_t.1');
	    }
	    return pluginFound;		
	},
	
	detectPlugin: function() {
	    // allow for multiple checks in a single pass
	    var daPlugins = PluginDetect.detectPlugin.arguments;
	    // consider pluginFound to be false until proven true
	    var pluginFound = false;
	    // if plugins array is there and not fake
	    if (navigator.plugins && navigator.plugins.length > 0) {
			var pluginsArrayLength = navigator.plugins.length;
			// for each plugin...
			for (pluginsArrayCounter=0; pluginsArrayCounter < pluginsArrayLength; pluginsArrayCounter++ ) {
			    // loop through all desired names and check each against the current plugin name
			    var numFound = 0;
			    for(namesCounter=0; namesCounter < daPlugins.length; namesCounter++) {
				// if desired plugin name is found in either plugin name or description
					if( (navigator.plugins[pluginsArrayCounter].name.indexOf(daPlugins[namesCounter]) >= 0) || 
					    (navigator.plugins[pluginsArrayCounter].description.indexOf(daPlugins[namesCounter]) >= 0) ) {
					    // this name was found
					    numFound++;
					}   
			    }
			    // now that we have checked all the required names against this one plugin,
			    // if the number we found matches the total number provided then we were successful
			    if(numFound == daPlugins.length) {
					pluginFound = true;
					// if we've found the plugin, we can stop looking through at the rest of the plugins
					break;
			    }
			}
	    }
	    return pluginFound;		
	}
}

PluginDetect.init();