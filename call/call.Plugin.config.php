<?php
/**
 * File for displaying the config-window for a plugin.
 * Call:   call.Plugin.config.php?id=
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

if (isset($_GET['key'])) {
	Plugin::uninstallPlugin($_GET['key']);
	echo Ajax::wrapJSforDocumentReady('Runalyze.loadOverlay("call/window.config.php");');
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$key = Plugin::getKeyForId($_GET['id']);
	$Plugin = Plugin::getInstanceFor($key);
	$Plugin->displayConfigWindow();
} else {
	Error::getInstance()->addError('ID must be set as GET-variable', __FILE__, __LINE__);
	echo '<em>'.__('Something went wrong ...').'</em>';
}