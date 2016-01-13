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

if (!file_exists('data/config.php')) {
	die('We have changed some paths for RUNALYZE v2.4. Please move your `config.php` to `data/config.php`.');
}

define('FRONTEND_PATH', 'inc/');

include_once 'data/config.php';

require 'vendor/autoload.php';
require_once 'inc/core/Calculation/NightDetector.php';

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
	echo 'Update your installation from v2.3 to v2.4 via update.php.'.NL;
	exit;
}

/**
 * Overview for data
 */
$Routes = $PDO->query('SELECT tr.id, tr.time, tr.s, r.startpoint from '.PREFIX.'training tr LEFT JOIN '.PREFIX.'route r ON tr.routeid = r.id  WHERE tr.routeid IS NOT NULL AND r.startpoint IS NOT NULL AND startpoint != "7zzzzzzzzz"');
$InsertIsNight = $PDO->prepare('UPDATE '.PREFIX.'training SET `is_night` = :val WHERE `id` = :id');
$geotools = new \League\Geotools\Geotools();
$detector = new \Runalyze\Calculation\NightDetector();

while ($Route = $Routes->fetch()) {
	$coord = $geotools->geohash()->decode($Route['startpoint'])->getCoordinate();
	$timepoint = $Route['time'] + 0.5 * $Route['s'];
	$detector->setFrom(
		$Route['time'] + 0.5 * $Route['s'],
		$geotools->geohash()->decode($Route['startpoint'])->getCoordinate()
	);

	if ($detector->isKnown()) {
		$InsertIsNight->execute(array(
			':val' => $detector->value() ? 1 : 0,
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
echo 'Remember to delete this file if you are not working on our git repository.'.NL;
