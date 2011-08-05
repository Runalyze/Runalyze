<?php
/**
 * File for moving a panel-plugin.
 * Call:   call.PluginPanel.move.php?id=&mode=
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

if (is_numeric($_GET['id'])) {
	$key   = Plugin::getKeyForId($_GET['id']);
	$Panel = Plugin::getInstanceFor($key);
	if ($Panel->get('type') == Plugin::$PANEL)
		$Panel->move($_GET['mode']);
}

Error::getInstance()->display();
?>