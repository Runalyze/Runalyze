<?php
/**
 * File for clapping a panel-plugin.
 * Call:   call.PluginPanel.clap.php?id=
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (is_numeric($_GET['id'])) {
	$Factory = new PluginFactory();
	$Panel = $Factory->newInstanceFor( $_GET['id'] );

	if ($Panel->type() == PluginType::PANEL) {
		$Panel->clap();
	}
}