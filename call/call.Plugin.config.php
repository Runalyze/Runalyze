<?php
/**
 * File for displaying the config-window for a plugin.
 * Call:   call.Plugin.config.php?id=
 */
require_once '../inc/class.Frontend.php';

new Frontend(true);

if (is_numeric($_GET['id'])) {
	$key = Plugin::getKeyForId($_GET['id']);
	$Plugin = Plugin::getInstanceFor($key);
	$Plugin->displayConfigWindow();
} else {
	Error::getInstance()->addError('ID must be set as GET-variable', __FILE__, __LINE__);
	echo('<em>Hier ist etwas schiefgelaufen ...</em>');
}
?>