	<h1>Garmin Communicator</h1>

	<div class="c fullWidth" style="position:relative;">
		<small style="position:absolute;right:8px;top:2px;">
			Bei Problemen:
			<img class="link" style="vertical-align:middle;" src="<?php echo Icon::getSrc(ICON::$REFRESH); ?>" onclick="$('#GCapi').attr('src', 'inc/tpl/tpl.garminCommunicator.php')" />
		</small>

		<iframe src="inc/tpl/tpl.garminCommunicator.php" id="GCapi" width="500px" height="210px"></iframe>
	</div>