<?php
/**
 * File for displaying statistic plugins.
 * Call:   call.Plugin.install.php?key=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (Plugin::installPlugin($_GET['key'])) {
	$Plugin = Plugin::getInstanceFor($_GET['key']);
	$Plugin->displayConfigWindow();
} else {
	echo '<h1>'.__('Install').' '.$_GET['key'].'</h1>';
	echo __('There was a problem, the plugin could not be installed.');
}
?>