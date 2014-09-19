<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...[&add]
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

// TODO: provide this in another way
if ($_GET['key'] === 'GARMIN_IGNORE_IDS') {
	Configuration::ActivityForm()->ignoreActivityID($_GET['value']);
} elseif (isset($_GET['add'])) {
	$Value   = unserialize(constant('CONF_'.$_GET['key']));
	$Value[] = $_GET['value'];

	// TODO
	//ConfigValue::update($_GET['key'], ConfigValueArray::arrayToString($Value));
} else {
	// TODO
	//ConfigValue::update($_GET['key'], $_GET['value']);
}