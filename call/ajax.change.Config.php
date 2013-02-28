<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...[&add]
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (isset($_GET['add'])) {
	$Value   = unserialize(constant('CONF_'.$_GET['key']));
	$Value[] = $_GET['value'];

	ConfigValue::update($_GET['key'], ConfigValueArray::arrayToString($Value));
} else {
	ConfigValue::update($_GET['key'], $_GET['value']);
}