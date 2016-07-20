<?php
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);
?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Garmin Communicator</title>

	<link rel="stylesheet" href="../lib/garmin/communicator2.css">
	<script src="../lib/garmin/prototype/prototype.js"></script>
	<script src="../lib/garmin/garmin/device/GarminDeviceDisplay.js"></script>
	<script>
		function ignoreID(id,e) {
			var p = e.parentNode;
			p.innerHTML = 'ignoriert';
			p.toggleClassName('upload-new');
			p.toggleClassName('upload-exists');
			p.parentNode.toggleClassName('ignored');

			$$("input[value="+id+"]").each(function(box){
				box.checked = false;
			});
			window.parent.Runalyze.Config.ignoreActivityID(id);
		}

		var currentActivity = 0, uploadedActivities = [], display, newIndex = 0;
		function load() {
		    display = new Garmin.DeviceDisplay("garminDisplay", {
				pluginNotUnlocked: "<em>The plug-in was not unlocked successfully.</em><br>The administrator has to set the correct API-key.",
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
				readDataButtonText: "<?php _e('Connect'); ?>",
				showCancelReadDataButton: false,
				lookingForDevices: '<?php _e('Searching for devices'); ?><br><br><img src="../img/wait.gif">',
				uploadsFinished: "<?php _e('Transfer complete'); ?>",
				uploadSelectedActivities: true,
				uploadCompressedData: true,
				uploadMaximum: 40, 
				browseComputerButtonText: "<?php _e('Browse computer'); ?>",
				cancelUploadButtonText: "<?php _e('Cancel'); ?>",
				changeDeviceButtonText: "<?php _e('Cancel'); ?>",
				connectedDevicesLabel: "<?php _e('Connected devices'); ?>: ",
				deviceBrowserLabel: "<?php _e('Browse devices'); ?>: ",
				deviceSelectLabel: "<?php _e('Devices'); ?>: ",
				findDevicesButtonText: "<?php _e('Search for decives'); ?>",
				loadingContentText: "<?php _e('Retrieving data from #{deviceName}, please wait ...'); ?>",
				readSelectedButtonText: "<?php _e('Please wait ...'); ?>",
				dataFound: "<?php _e('Found #{tracks} activities'); ?>",
				noDeviceDetectedStatusText: "<?php _e('No devices found'); ?>",
				singleDeviceDetectedStatusText: "<?php _e('Found'); ?>: ",
				foundDevice: "<?php _e('Found'); ?>: #{deviceName}",
				foundDevices: "<?php _e('#{deviceCount} devices found'); ?>",
				showReadDataElementOnDeviceFound: true,
				getActivityDirectoryHeaderIdLabel: function () { return '<?php _e('Date'); ?>'; },
				activityDirectoryHeaderDuration: '<?php _e('Duration'); ?>',
				activityDirectoryHeaderStatus: '<?php _e('Status'); ?>',
				statusCellProcessingImg: 'upload',
				detectNewActivities: true,
				syncDataUrl: '<?php echo System::getFullDomainWithProtocol(); ?>call/ajax.activityMatcher.php',
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
						statusCell.innerHTML = '<?php _e('present'); ?>';
					} else {
						entry.isNew = true;
						checkbox.checked = true;
						statusCell.className = statusCell.className + ' upload-new';
						statusCell.innerHTML = 'neu<br><small onclick="ignoreID(\''+activityId+'\', this)">[<?php _e('ignore'); ?>]</small>';

						// Rearrange: Put to the top
						if (index != newIndex) {
							var rows = $("activityTable").getElementsByTagName('tr');
							var firstOldRow = rows[newIndex];
							var removedRow = rows[index].parentNode.removeChild(rows[index]);
							rows[0].parentNode.insertBefore(removedRow, firstOldRow);
						}

						newIndex = newIndex + 1;
					}
				},
				postActivityHandler: function(activityXml, display) {
					$("readSelectedButton").value = "<?php _e('Import'); ?>";
					var currentName = display.activities[currentActivity].attributes.activityName.replace(/:/gi, "-");

					uploadedActivities.push(currentName);
					currentActivity = currentActivity + 1;

					if (display.numQueuedActivities > 1)
						window.parent.Runalyze.Training.saveTcx(activityXml, currentName, currentActivity, display.numQueuedActivities, uploadedActivities);
					else
						window.parent.Runalyze.Training.loadXML(activityXml);
				},
				afterFinishUploads: function(display) {
					// Done from Runalyze.Training.saveTcx() with callback
					//if (uploadedActivities.length > 1)
					//	window.parent.Runalyze.Training.loadSavedTcxs(uploadedActivities);
				},
				afterFinishReadFromDevice: function(dataString, dataDoc, extension, activities, display) {
					$("readSelectedButton").value = "<?php _e('Import'); ?>";

					$("selectAllHeader").innerHTML = '<a href="#" id="selectAllButton" style="cursor:pointer;"><?php _e('all'); ?></a>';

					var checkboxes = $$("input[type=checkbox]");
					var cbControl = $("selectAllButton");

					cbControl.observe("click", function(e){
						e.preventDefault();
						cbControl.toggleClassName('checked');
						checkboxes.each(function(box){
							box.checked = cbControl.hasClassName('checked');
						});
					});
				}
<?php
if (strlen(GARMIN_API_KEY) > 10)
	echo ',pathKeyPairsArray: ["'.Request::getProtocol().'://'.$_SERVER['HTTP_HOST'].'","'.GARMIN_API_KEY.'"]';
?>
			});
		}
	</script>
</head>

<body onload="load()" style="background:none;">

	<div id="garminDisplay"></div>

</body>
</html>