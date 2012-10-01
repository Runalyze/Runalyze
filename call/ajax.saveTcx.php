<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

ImporterTCX::saveTCX($_POST['activityId'], $_POST['data']);