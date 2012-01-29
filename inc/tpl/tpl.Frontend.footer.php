<br class="clear" />

<div id="copy">
	<a class="right" id="copy" href="http://www.runalyze.de/" title="Runalyze" target="_blank">
		<strong>&copy; Runalyze v<?php echo RUNALYZE_VERSION; ?></strong>
	</a>

	<span class="left b">
		<?php echo Config::getOverlayLink(); ?>
		<?php echo PluginTool::getOverlayLink(); ?>
		<?php echo Frontend::getHelpOverlayLink(); ?>
	</span>

	<div class="c"><img id="wait" src="img/ajax-loader-download.gif" alt="Bitte warten ... es wird geladen." /></div>
</div>

<?php Ajax::initJSlibrary(); ?>

</body>
</html>