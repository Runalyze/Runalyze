<?php
/**
 * File for displaying the config-window for a plugin.
 * Call:   call.Plugin.config.php?id=
 */

$Factory = new PluginFactory();

if (isset($_GET['key'])) {
	$Factory->uninstallPlugin( filter_input(INPUT_GET, 'key') );

	echo Ajax::wrapJSforDocumentReady('Runalyze.Overlay.load("call/window.config.php");');
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$Plugin = $Factory->newInstanceFor( $_GET['id'] );
	$Plugin->displayConfigWindow();
} else {
	echo '<em>'.__('Something went wrong ...').'</em>';
}