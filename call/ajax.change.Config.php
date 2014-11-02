<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...[&add]
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

switch ($_GET['key']) {
	case 'garmin-ignore':
		\Runalyze\Configuration::ActivityForm()->ignoreActivityID($_GET['value']);
		break;

	case 'leaflet-layer':
		\Runalyze\Configuration::ActivityView()->updateLayer($_GET['value']);
		break;

	default:
		if (substr($_GET['key'], 0, 5) == 'show-') {
			$key = substr($_GET['key'], 5);
			\Runalyze\Configuration::ActivityForm()->update($key, $_GET['value']);
		}
}