<?php
/**
 * Script to refactor coordinates to geohashes
 *
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */

// This time we are automatically reading your config.php.
// This will ensure that you moved your configuration to its new place
// and this refactor script does not include any security issues.
// Still, we encourage you to delete this file after refactoring your database.

define('CLI', false); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql


// Uncomment these lines to unset time/memory limits
#@ini_set('memory_limit', '-1');
#if (!ini_get('safe_mode')) { @set_time_limit(0); }


/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

define('FRONTEND_PATH', 'inc/');

include_once 'data/config.php';
require_once 'inc/core/Util/TimezoneLookup.php';
require 'vendor/autoload.php';
use League\Geotools\Geotools;

$starttime = microtime(true);
$maxtime = ini_get('max_execution_time');

$TZL = new \Runalyze\Util\TimezoneLookup();


/**
 * Protect script
 */
define('NL', CLI ? PHP_EOL : '<br>'.PHP_EOL);

if(file_exists('.refactortimezone')) {
    echo 'This script has already been executed';
    exit;
}

if (empty($database) && empty($host)) {
	echo 'Database connection has to be set within the file.'.NL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	try {
		$PDO = new PDO('mysql:dbname='.$database.';host='.$host.';port='.$port, $username, $password);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		if (SET_GLOBAL_PROPERTIES) {
			$PDO->exec('SET GLOBAL max_allowed_packet=1073741824;');
			$PDO->exec('SET GLOBAL key_buffer_size=1073741824;');
		}
	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage().NL;
		exit;
	}
}

/**
 * Check version
 */
$columns = $PDO->query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'.$database.'" AND TABLE_NAME ="'.PREFIX.'training"')->fetchAll(PDO::FETCH_COLUMN, 0);

if (!in_array('timezone', $columns)) {
	echo 'Update your installation from v2.4 to v2.5 via update.php.'.NL;
	exit;
}

/**
 * Overview for data
 */
$Activities = $PDO->query('SELECT tr.id, tr.time, tr.routeid, r.startpoint FROM '.PREFIX.'training tr JOIN '.PREFIX.'route r ON (tr.routeid = r.id) GROUP BY tr.id');
$geotools       = new Geotools();

while ($Activity = $Activities->fetch()) {
	$Time = new DateTime('', new DateTimeZone('Europe/Berlin'));
	$Time->setTimestamp($Activity['time']);
	$Offset = $Time->getOffset() / 60;
	//TODO SET new UTC Time in database
	$UTCTime = $Activity['time'] - $Time->getOffset();
	if(!is_null($Activity['startpoint'])) {
	    $decoded = $geotools->geohash()->decode($Activity['startpoint'])->getCoordinate();
	    $tzid = $TZL->getTimezoneForCoordinate($decoded->getLongitude(), $decoded->getLatitude());
	    if($tzid) {
		$timezone = new DateTime('', new DateTimeZone($tzid));
		$timezone->setTimestamp($UTCTime);
		//TODO Set offset in db
		$tzoffset = $timezone->getOffset() / 60;
	    }
	}
	echo ".";
}
$done = true;
if($done) {
    $file = fopen(".refactortimezone","w");
    fclose($file);

}

if (CLI) {
	echo NL.NL;
}

echo 'done;'.NL;
echo NL;
echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
echo NL;

echo 'You are done. All trainings are refactored.'.NL;
echo NL;
echo 'Remember to delete this file if you are not working on our git repository.'.NL;