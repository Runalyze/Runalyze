<?php
require_once '../class.Frontend.php';

new Frontend(true);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Garmin Display - Upload Selected Fitness Activities</title>

	<style type="text/css" media="all">@import "../../lib/garmin/communicator2.css";</style>
	<script type="text/javascript" src="../../lib/garmin/prototype/prototype.js"></script>
	<script type="text/javascript" src="../../lib/garmin/garmin/device/GarminDeviceDisplay.js"></script>
	<script type="text/javascript">	
		function load() {
		    var display = new Garmin.DeviceDisplay("garminDisplay", {
				showReadDataElement: true,
				showProgressBar: true,
				showFindDevicesElement: true,
				showFindDevicesButton: false,
				showDeviceButtonsOnLoad: false,
				showDeviceButtonsOnFound: false,
				autoFindDevices: true,
				showDeviceSelectOnLoad: true,
				autoHideUnusedElements: true,
				showReadDataTypesSelect: false,
				readDataType: Garmin.DeviceControl.FILE_TYPES.tcxDir,
				deviceSelectLabel: "Ausw&auml;hlen:<br />",
				readDataButtonText: "Verbinden",
				showCancelReadDataButton: false,
				lookingForDevices: 'Suche nach Ger&auml;ten<br /><br /><img src="../../img/wait.gif" />',
				uploadsFinished: "&Uuml;bertragung vollst&auml;ndig",
				uploadSelectedActivities: true,
				uploadCompressedData: true,
				uploadMaximum: 20, 
				browseComputerButtonText: "Computer durchsuchen",
				cancelUploadButtonText: "Abbrechen",
				changeDeviceButtonText: "Abbrechen",
				connectedDevicesLabel: "Verbundene Ger&auml;te: ",
				deviceBrowserLabel: "Ger&auml;te durchsuchen: ",
				deviceSelectLabel: "Ger&auml;te: ",
				findDevicesButtonText: "Ger&auml;te suchen",
				dataFound: "#{tracks} Trainings gefunden",
				noDeviceDetectedStatusText: "Keine Ger&auml;te gefunden",
				singleDeviceDetectedStatusText: "Gefunden: ",
				showReadDataElementOnDeviceFound: true,
				getActivityDirectoryHeaderIdLabel: function () { return 'Datum'; },
				activityDirectoryHeaderDuration: 'Dauer',
				activityDirectoryHeaderStatus: '',
				postActivityHandler: function(activityXml, display) {
					window.parent.Runalyze.loadXML(activityXml);
				},
				afterFinishUploads: function(display) {
					//window.alert("Das waren alle.");
				}
<?php
if (strlen(CONF_GARMIN_API_KEY) > 10)
	echo ',pathKeyPairsArray: ["http://'.$_SERVER['HTTP_HOST'].'","'.CONF_GARMIN_API_KEY.'"]';
?>
			});
		}
	</script>
</head>

<body onload="load()" style="background:none;">

	<div id="garminDisplay"></div>

</body>
</html>