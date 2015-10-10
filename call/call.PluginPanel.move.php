<?php
/**
 * File for moving a panel-plugin.
 * Call:   call.PluginPanel.move.php?id=&mode=
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (is_numeric($_GET['id'])) {
	$Factory = new PluginFactory();
	$Panel = $Factory->newInstanceFor( $_GET['id'] );

	if ($Panel->type() == PluginType::PANEL) {
		$Panel->move( filter_input(INPUT_GET, 'mode') );
	}
}