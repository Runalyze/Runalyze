<?php
/**
 * File for matching activities from Garmin Communicator
 * Call:   ajax.activityMatcher.php
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

use Runalyze\Activity\DuplicateFinder;
use Runalyze\Util\LocalTime;

header('Content-type: application/json');

/**
 * Adjusted strtotime
 * Timestamps are given in UTC but local timezone offset has to be considered!
 * @param $string
 * @return int
 */
function parserStrtotime($string) {
	if (substr($string, -1) == 'Z') {
		return LocalTime::fromServerTime((int)strtotime(substr($string, 0, -1).' UTC'))->getTimestamp();
	}

	return LocalTime::fromString($string)->getTimestamp();
}

$IDs     = array();
$Matches = array();
$Array   = explode('&', urldecode(file_get_contents('php://input')));
foreach ($Array as $String) {
	if (substr($String,0,12) == 'externalIds=')
		$IDs[] = substr($String,12);
}

$IgnoreIDs = \Runalyze\Configuration::ActivityForm()->ignoredActivityIDs();
$DuplicateFinder = new DuplicateFinder(DB::getInstance(), SessionAccountHandler::getId());

$IgnoreIDs = array_map(function($v){
	try {
		return (int)floor(parserStrtotime($v)/60)*60;
	} catch (Exception $e) {
		return 0;
	}
}, $IgnoreIDs);

foreach ($IDs as $ID) {
	$dup = $DuplicateFinder->checkForDuplicate((int)floor(parserStrtotime($ID)/60)*60);
	$found = $dup || in_array($ID, $IgnoreIDs);
	$Matches[$ID] = array('match' => $found);
}

$Response = array('matches' => $Matches);

echo json_encode((object)$Response);
