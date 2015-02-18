	<div class="c fullwidth" style="position:relative;">
		<small style="position:absolute;right:4px;top:2px;">
			<span class="link" onclick="$('#GCapi').attr('src', 'call/call.garminCommunicator.php')"><?php echo Icon::$REFRESH; ?></span>
		</small>

<?php
if (!$this->visible):
?>
		<div id="iframe-spacer" style="width:490px;height:310px;">
			<em><?php _e('Move your mouse here to start loading the communicator.'); ?></em>
		</div>
<?php
	echo Ajax::wrapJSasFunction('$("#iframe-spacer").hover(function(){
			$(\'<iframe src="call/call.garminCommunicator.php" id="GCapi" name="GCapi" width="490px" height="310px"></iframe>\').insertAfter($("#iframe-spacer"));
			$("#iframe-spacer").remove();
		});');

else:
?>
		<iframe src="call/call.garminCommunicator.php" id="GCapi" name="GCapi" width="490px" height="310px"></iframe>
<?php
endif;
?>
	</div>