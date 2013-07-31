<?php
/**
 * File for displaying plugins.
 * Call:   call.Plugin.display.php?id= [&sport= &jahr= &dat= ]
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$Key = Plugin::getKeyForId($_GET['id']);
$Plugin = Plugin::getInstanceFor($Key);

if ($Plugin === false)
	echo HTML::error('Das Plugin konnte nicht gefunden werden.');

if ($Plugin instanceof PluginPanel)
	$Plugin->setSurroundingDivVisible(false);

$Plugin->display();
?>