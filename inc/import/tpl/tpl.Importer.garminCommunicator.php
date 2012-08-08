	<h1>Garmin Communicator</h1>

	<div class="c fullWidth" style="position:relative;">
		<small style="position:absolute;right:8px;top:2px;">
			Bei Problemen:
			<img class="link" style="vertical-align:middle;" src="<?php echo Icon::getSrc(ICON::$REFRESH); ?>" onclick="$('#GCapi').attr('src', 'inc/tpl/tpl.garminCommunicator.php')" />
		</small>

<?php
if ($hideGarmin):
?>
		<div id="iframe-spacer" style="width:500px;height:210px;">
			<em>Der Communicator wird gleich geladen.</em>
		</div>
<?php
	echo Ajax::wrapJSasFunction('$("#iframe-spacer").hover(function(){
			$(\'<iframe src="inc/tpl/tpl.garminCommunicator.php" id="GCapi" width="500px" height="210px"></iframe>\').insertAfter($("#iframe-spacer"));
			$("#iframe-spacer").remove();
		});');

else:
?>
		<iframe src="inc/tpl/tpl.garminCommunicator.php" id="GCapi" width="500px" height="210px"></iframe>
<?php
endif;
?>
	</div>