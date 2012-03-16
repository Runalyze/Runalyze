<?php
/**
 * File for clapping a panel-plugin.
 * Call:   call.PluginPanel.clap.php?id=
 */
require_once '../inc/class.Frontend.php';

new Frontend();

if (is_numeric($_GET['id'])) {
	$key   = Plugin::getKeyForId($_GET['id']);
	$Panel = Plugin::getInstanceFor($key);
	if ($Panel->get('type') == Plugin::$PANEL)
		$Panel->clap();
}
?>