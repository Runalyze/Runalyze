<?php
/**
 * File for changing a config-value
 * Call:   ajax.change.Config.php?key=...&value=...
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

Filesystem::writeFile('import/files/'.$_POST['activityId'].'.tcx', $_POST['data']);