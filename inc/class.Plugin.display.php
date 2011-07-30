<?php
/**
 * File for displaying statistic plugins.
 * Call:   class.Plugin.display.php?id= [&sport= &jahr= &dat= ]
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

$Key = Plugin::getKeyForId($_GET['id']);
$Plugin = Plugin::getInstanceFor($Key);

if ($Plugin instanceof PluginPanel)
	$Plugin->setSurroundingDivVisible(false);
$Plugin->display();

$Frontend->displayFooter();
$Frontend->close();
?>