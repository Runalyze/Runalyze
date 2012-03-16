<?php
/**
 * File for displaying statistic plugins.
 * Call:   call.Plugin.install.php?key=
 */
require '../inc/class.Frontend.php';

new Frontend();

if (Plugin::installPlugin($_GET['key'])) {
	$Plugin = Plugin::getInstanceFor($_GET['key']);
	$Plugin->displayConfigWindow();
} else {
	echo '<h1>Installation von '.$_GET['key'].'</h1>';
	echo 'Bei der Installation ist ein Problem aufgetaucht: Das Plugin konnte nicht installiert werden.';
}
?>