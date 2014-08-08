<?php
/**
 * File for displaying statistic plugins.
 * Call:   call.Plugin.install.php?key=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();
$Factory = new PluginFactory();

$Pluginkey = filter_input(INPUT_GET, 'key');

if ($Factory->installPlugin($Pluginkey)) {
	$Plugin = $Factory->newInstance($Pluginkey);
	$Plugin->displayConfigWindow();
} else {
	echo '<h1>'.__('Install').' '.$Pluginkey.'</h1>';
	echo __('There was a problem, the plugin could not be installed.');
}