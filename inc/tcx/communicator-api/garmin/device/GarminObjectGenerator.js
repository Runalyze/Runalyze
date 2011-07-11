/**
 * This snippet of code is required to generate the object tag
 * only when the browser reports that the plugin is installed.
 * 
 * @requires PluginDetect, BrowserDetect
 */
if (PluginDetect.detectGarminCommunicatorPlugin()) {
	
	// Insert object tag based on browser
	switch(BrowserDetect.browser) {
	    // TODO pull these out into constants later, in BrowserDetect
	    case "Explorer":
            document.write('<object id="GarminActiveXControl" style="WIDTH: 0px; HEIGHT: 0px; visible: hidden" height="0" width="0" classid="CLSID:099B5A62-DE20-48C6-BF9E-290A9D1D8CB5">&#160;</object>');
            break;
	    case "Firefox":
	    case "Mozilla":
	    case "Safari":
        	// Outer div necessary for Safari and Chrome
        	// TODO try removing the divs to see if Safari 3+ has fixed their bug  
        	document.write('<div style="height:0px; width:0px;">');
        	document.write('<object id="GarminNetscapePlugin" type="application/vnd-garmin.mygarmin" width="0" height="0">&#160;</object>');
        	document.write('</div>');
            break;
	    default:
        	document.write('<div style="height:0px; width:0px;"><object id="GarminActiveXControl" style="WIDTH: 0px; HEIGHT: 0px; visible: hidden" height="0" width="0" classid="CLSID:099B5A62-DE20-48C6-BF9E-290A9D1D8CB5">');
        	document.write('	<object id="GarminNetscapePlugin" type="application/vnd-garmin.mygarmin" width="0" height="0">&#160;</object>');
        	document.write('</object></div>');
        	break;
	}
	
}
