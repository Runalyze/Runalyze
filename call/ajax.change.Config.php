<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

// TODO: select value cannot be given as string in an easy way
ConfigValue::update($_GET['key'], $_GET['value']);