<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title>Garmin Display - Upload Selected Fitness Activities</title>

	<style type="text/css" media="all">@import "../tcx/garmin/communicator2.css";</style>
	<script type="text/javascript" src="../tcx/communicator-api/prototype/prototype.js">&#160;</script>
	<script type="text/javascript" src="../tcx/communicator-api/garmin/device/GarminDeviceDisplay.js">&#160;</script>
	<script type="text/javascript">	
		function load() {
		    var display = new Garmin.DeviceDisplay("garminDisplay", { 
<?php
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

if (strlen(CONF_GARMIN_API_KEY) > 10)
	echo 'pathKeyPairsArray: ["http://'.$_SERVER['HTTP_HOST'].'","'.CONF_GARMIN_API_KEY.'"],';
?>
				/*pathKeyPairsArray: ["http://developer.garmin.com/","ee3934433a35ee348583236c2eeadbc1"],*/
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
				lookingForDevices: 'Suche nach Ger&auml;ten<br /><br /><img src="../tcx/garmin/ajax-loader.gif" />',
				uploadsFinished: "&Uuml;bertragung vollst&auml;ndig",
				uploadSelectedActivities: true,
				uploadCompressedData: true,
				uploadMaximum: 20, 
				dataFound: "#{tracks} Trainings gefunden",
				showReadDataElementOnDeviceFound: true,
				postActivityHandler: function(activityXml, display) {
					window.parent.getXmlFromIFrame(activityXml);
				},
				afterFinishUploads: function(display) {
					//window.alert("Das waren alle.");
				}
			});
		}
	</script>
</head>

<body onload="load()">

	<div id="garminDisplay"></div>

</body>
</html>