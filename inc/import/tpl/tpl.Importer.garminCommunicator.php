	<div class="c fullwidth" style="position:relative;">
		<small style="position:absolute;right:8px;top:2px;">
			<span class="link" title="Bei Problemen: Neuladen" onclick="$('#GCapi').attr('src', 'call/call.garminCommunicator.php')"><?php echo Icon::$REFRESH; ?></span>
		</small>

<?php
if (!$this->visible):
?>
		<div id="iframe-spacer" style="width:500px;height:310px;">
			<em>Der Communicator wird gleich geladen.</em>
		</div>
<?php
	echo Ajax::wrapJSasFunction('$("#iframe-spacer").hover(function(){
			$(\'<iframe src="call/call.garminCommunicator.php" id="GCapi" name="GCapi" width="500px" height="310px"></iframe>\').insertAfter($("#iframe-spacer"));
			$("#iframe-spacer").remove();
		});');

else:
?>
		<iframe src="call/call.garminCommunicator.php" id="GCapi" name="GCapi" width="500px" height="310px"></iframe>
<?php
endif;
?>
	</div>