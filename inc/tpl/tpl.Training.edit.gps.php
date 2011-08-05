<?php if ($Training->hasPositionData()): ?>
<?php // TODO: Check if elevationCorrection is already done ?>
	<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id=<?php echo $id; ?>" title="H&ouml;hendaten korrigieren"><strong>ElevationCorrection</strong></a><br />
	<br />
	<small>
		Mit der ElevationCorrection k&ouml;nnen die H&ouml;hendaten korrigiert werden.<br />
		Vorsicht: Die Abfrage kann lange dauern, bitte nicht abbrechen, bevor das Laden beendet ist.
	</small><br />
	<br />

	<small id="gps-results"></small>
<?php else: ?>
	<div onmouseover="javascript:createUploader()">
		<strong>TCX-Datei nachtr&auml;glich hinzuf&uuml;gen</strong><br />
			<br />
		<div class="c button" id="file-upload-tcx">Datei hochladen</div>
		<script>
			function createUploader() {
				$("#file-upload-tcx").removeClass("hide");
				new AjaxUpload('#file-upload-tcx', {
					action: '<?php echo $_SERVER['SCRIPT_NAME'].'?id='.$id.'&json=true'; ?>',
					onComplete : function(file, response){
						jLoadLink('ajax', '<?php echo $_SERVER['SCRIPT_NAME'].'?id='.$id.'&tmp=true'; ?>');
					}		
				});
			}
		</script>
	</div>
<?php endif; ?>