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

$IgnoreIDs = \Runalyze\Configuration::ActivityForm()->ignoredActivityIDs();
$Request = DB::getInstance()->prepare('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `activity_id`=:id LIMIT 1');

foreach ($IDs as $ID) {
	$Request->execute(array('id' => $ID));
	$found = in_array($ID, $IgnoreIDs) || $Request->fetchColumn() > 0;
	$Matches[$ID] = array('match' => $found);
}

$Response = array('matches' => $Matches);

echo json_encode((object)$Response);