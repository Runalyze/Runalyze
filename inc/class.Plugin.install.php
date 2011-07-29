<?php
/**
 * File for displaying statistic plugins.
 * Call:   class.Plugin.install.php?key=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

if (Plugin::installPlugin('plugin/class.'.$_GET['key'].'.php')) {
	$Plugin = Plugin::getInstanceFor($_GET['key']);
	$Plugin->displayConfigWindow();
} else {
	echo '<h1>Installation von '.$_GET['key'].'</h1>';
	echo 'Bei der Installation ist ein Problem aufgetaucht: Das Plugin konnte nicht installiert werden.';
}

$Frontend->displayFooter();
$Frontend->close();
?>