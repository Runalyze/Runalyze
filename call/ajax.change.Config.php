<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...
 */
require_once '../inc/class.Frontend.php';

new Frontend();

// TODO: select value cannot be given as string in an easy way
Config::update($_GET['key'], $_GET['value']);
?>