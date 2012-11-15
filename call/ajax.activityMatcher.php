<?php
/**
 * File for matching activities from Garmin Communicator
 * Call:   ajax.activityMatcher.php
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend();

header('Content-type: application/json');

$IDs     = array();
$Matches = array();
$Array   = explode('&', urldecode(file_get_contents('php://input')));
foreach ($Array as $String) {
	if (substr($String,0,12) == 'externalIds=')
		$IDs[] = substr($String,12);
}

foreach ($IDs as $ID) {
	$found = Mysql::getInstance()->num('SELECT 1 FROM '.PREFIX.'training WHERE activity_id="'.$ID.'" LIMIT 1') > 0;
	$Matches[$ID] = array('match' => $found);
}

$Response = array('matches' => $Matches);

echo json_encode((object)$Response);