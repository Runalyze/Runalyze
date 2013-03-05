<?php
require_once '../class.Frontend.php';

$Frontend = new Frontend(true);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Garmin Display - Upload Selected Fitness Activities</title>

	<style type="text/css" media="all">@import "../../lib/garmin/communicator2.css";</style>
	<script type="text/javascript" src="../../lib/garmin/prototype/prototype.js"></script>
	<script type="text/javascript" src="../../lib/garmin/garmin/device/GarminDeviceDisplay.js"></script>
	<script type="text/javascript">
		function ignoreID(id,e) {
			var p = e.parentNode;
			p.innerHTML = 'ignoriert';
			p.toggleClassName('upload-new');
			p.toggleClassName('upload-exists');
			p.parentNode.toggleClassName('ignored');

			$$("input[value="+id+"]").each(function(box){
				box.checked = false;
			});
			window.parent.Runalyze.changeConfig('GARMIN_IGNORE_IDS',id,true);
		}

		var currentActivity = 0, uploadedActivities = [], display;
		function load() {
		    display = new Garmin.DeviceDisplay("garminDisplay", {
				pluginNotUnlocked: "<em>The plug-in was not unlocked successfully.</em><br />Der Garmin-API-Key ist entweder nicht in der Konfiguration eingetragen oder falsch.",
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
				readDataTypes: [Garmin.DeviceControl.FILE_TYPES.tcxDir, Garmin.DeviceControl.FILE_TYPES.gpxDir, Garmin.DeviceControl.FILE_TYPES.fitDir],
				deviceSelectLabel: "Ausw&auml;hlen:<br />",
				readDataButtonText: "Verbinden",
				showCancelReadDataButton: false,
				lookingForDevices: 'Suche nach Ger&auml;ten<br /><br /><img src="../../img/wait.gif" />',
				uploadsFinished: "&Uuml;bertragung vollst&auml;ndig",
				uploadSelectedActivities: true,
				uploadCompressedData: true,
				uploadMaximum: 40, 
				browseComputerButtonText: "Computer durchsuchen",
				cancelUploadButtonText: "Abbrechen",
				changeDeviceButtonText: "Abbrechen",
				connectedDevicesLabel: "Verbundene Ger&auml;te: ",
				deviceBrowserLabel: "Ger&auml;te durchsuchen: ",
				deviceSelectLabel: "Ger&auml;te: ",
				findDevicesButtonText: "Ger&auml;te suchen",
				loadingContentText: "Daten werden von #{deviceName} gelesen, bitte warten...",
				readSelectedButtonText: "Bitte warten ...", // "Importieren"
				dataFound: "#{tracks} Trainings gefunden",
				noDeviceDetectedStatusText: "Keine Ger&auml;te gefunden",
				singleDeviceDetectedStatusText: "Gefunden: ",
				foundDevice: "Gefunden: #{deviceName}",
				foundDevices: "#{deviceCount} Ger&auml;te gefunden",
				showReadDataElementOnDeviceFound: true,
				getActivityDirectoryHeaderIdLabel: function () { return 'Datum'; },
				activityDirectoryHeaderDuration: 'Dauer',
				activityDirectoryHeaderStatus: 'Status',
				statusCellProcessingImg: 'upload',
				detectNewActivities: true,
				syncDataUrl: '<?php echo System::getFullDomain(); ?>call/ajax.activityMatcher.php',
				syncDataOptions: {method:'post'},
				afterTableInsert: function(index, entry, statusCell, checkbox, row, activityMatcher) {
					var activityId = entry.id, isMatch = false;

					try {
						isMatch = activityMatcher.get(activityId).match;
					} catch(e) {
						console.log(e);
					}

					if (isMatch) {
						entry.isNew = false;
						checkbox.checked = false;
						statusCell.className = statusCell.className + ' upload-exists';
						statusCell.innerHTML = 'vorhanden';
					} else {
						entry.isNew = true;
						checkbox.checked = true;
						statusCell.className = statusCell.className + ' upload-new';
						statusCell.innerHTML = 'neu<br /><small onclick="ignoreID(\''+activityId+'\', this)">[ignorieren]</small>';
					}
				},
				postActivityHandler: function(activityXml, display) {
					$("readSelectedButton").value = "Importieren";
					var currentName = display.activities[currentActivity].attributes.activityName.replace(/:/gi, "-");
	
					if (display.numQueuedActivities > 1)
						window.parent.Runalyze.saveTcx(activityXml, currentName);
					else
						window.parent.Runalyze.loadXML(activityXml);
	
					uploadedActivities.push(currentName);
					currentActivity = currentActivity + 1;
				},
				afterFinishUploads: function(display) {
					if (uploadedActivities.length > 1)
						window.parent.Runalyze.loadSavedTcxs(uploadedActivities);
				},
				afterFinishReadFromDevice: function(dataString, dataDoc, extension, activities, display) {
					$("readSelectedButton").value = "Importieren";
					$("selectAllButton").setStyle({display:'inline'});

					var checkboxes = $$("input[type=checkbox]");
					var cbControl = $("selectAllButton");

					cbControl.observe("click", function(){
						cbControl.toggleClassName('checked');
						checkboxes.each(function(box){
							box.checked = cbControl.hasClassName('checked');
						});
					});
				}
<?php
if (strlen(CONF_GARMIN_API_KEY) > 10)
	echo ',pathKeyPairsArray: ["'.Request::getProtocol().'://'.$_SERVER['HTTP_HOST'].'","'.GARMIN_API_KEY.'"]';
?>
			});
		}
	</script>
</head>

<body onload="load()" style="background:none;">

	<span href="#" id="selectAllButton" style="position:absolute;top:1px;left:8px;display:none;cursor:pointer;font-size:.8em;color:#666;">alle w&auml;hlen</span>

	<div id="garminDisplay"></div>

</body>
</html>