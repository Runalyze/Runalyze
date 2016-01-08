<?php
/**
 * Script to refactor coordinates to geohashes
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
require 'vendor/autoload.php';

$host = 'localhost';
$port = '3306';
$database = 'runalyze';
$username = 'root';
$password = '';

define('PREFIX', 'runalyze_'); // Prefix of your RUNALYZE installation
define('CLI', true); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql


// Uncomment these lines to unset time/memory limits
#@ini_set('memory_limit', '-1');
#if (!ini_get('safe_mode')) { @set_time_limit(0); }


/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

$starttime = microtime(true);
$maxtime = ini_get('max_execution_time');
/**
 * Protect script
 */
define('NL', CLI ? PHP_EOL : '<br>'.PHP_EOL);

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
if (!in_array('is_night', $columns)) {
      echo 'Update your installation from v2.3 to v2.4 via update.php'.NL;
      exit;
}

/**
 * Overview for data
 */
$Routes = $PDO->query('SELECT tr.id, tr.time, tr.s, r.startpoint from '.PREFIX.'training tr LEFT JOIN '.PREFIX.'route r ON tr.routeid = r.id  WHERE tr.routeid IS NOT NULL AND r.startpoint IS NOT NULL AND startpoint != "7zzzzzzzzz"');
$InsertIsNight = $PDO->prepare('UPDATE '.PREFIX.'training SET `is_night`=1 WHERE `id` = :id');
$geotools       = new \League\Geotools\Geotools();
while ($Route = $Routes->fetch()) {
	$coord = $geotools->geohash()->decode($Route['startpoint'])->getCoordinate();
	#$lat = $decoded->getCoordinate()->getLatitude();
#	$long = $decoded->getCoordinate()->getLongitude();
	$duration = $Route['s'];
	$timepoint = $Route['time'] + 0.5 * $duration;
	$isAfterSunset = $timepoint > date_sunset($timepoint, SUNFUNCS_RET_TIMESTAMP, $coord->getLatitude(), $coord->getLongitude(), 90 + 5/6, 0);
	$isBeforeSunrise = $timepoint < date_sunrise($timepoint, SUNFUNCS_RET_TIMESTAMP, $coord->getLatitude(), $coord->getLongitude(), 90 + 5/6, 0);
	$isNight = $isAfterSunset || $isBeforeSunrise;
	if($isNight) {
	$InsertIsNight->execute(array(
				 ':id' => $Route['id']
			    ));
	}
	echo ".";
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
    echo 'Remember to unset your credentials within this file.'.NL;
    echo '(Or simply delete this file if you are not working on our git repository)'.NL;
