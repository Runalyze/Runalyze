<?php
/**
 * Script to refactor coordinates to geohashes
 *
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */

// This script must only be executed once with `$updateActivityTime = true;`,
// otherwise it will change all your activity timestamps.
// If you did not have a timezone database available and want to set timezone offset (only for visual effects) later on,
// You can run the script again with `$updateActivityTime = false;`.
$updateActivityTime = true;

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
require_once 'inc/core/Util/TimezoneLookupException.php';
require_once 'inc/core/Util/LocalTime.php';
require 'vendor/autoload.php';

use League\Geotools\Geotools;

$starttime = microtime(true);

/**
 * Protect script
 */
define('NL', CLI ? PHP_EOL : '<br>'.PHP_EOL);

if ($updateActivityTime && file_exists('.refactortimezone')) {
    echo 'This script has already been executed.';
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

if (!in_array('timezone_offset', $columns)) {
	echo 'Update your installation from v2.4 to v2.5 via update.php.'.NL;
	exit;
}

/**
 * Overview for data
 */
$Geotools = new Geotools();
$TZLookup = new \Runalyze\Util\TimezoneLookup();
$DateTime = new DateTime('', new DateTimeZone('Europe/Berlin'));

$activities = $PDO->query('SELECT tr.id, tr.time, tr.activity_id, tr.routeid, r.startpoint FROM '.PREFIX.'training tr LEFT JOIN '.PREFIX.'route r ON (tr.routeid = r.id) GROUP BY tr.id');
$UpdateTime = $PDO->prepare('UPDATE '.PREFIX.'training SET `time` = :time, `activity_id` = :activityid, `timezone_offset` = :offset WHERE `id` = :id');
$UpdateOnlyOffset = $PDO->prepare('UPDATE '.PREFIX.'training SET `timezone_offset` = :offset WHERE `id` = :id');

while ($activity = $activities->fetch()) {
	$offset = $DateTime->setTimestamp($activity['time'])->getOffset();
	$localTime = $activity['time'] + $offset;

	$timezoneOffset = $offset / 60;

	if ($TZLookup->isPossible() && !is_null($activity['startpoint'])) {
		$Coordinate = $Geotools->geohash()->decode($activity['startpoint'])->getCoordinate();
		$timezone = $TZLookup->getTimezoneForCoordinate($Coordinate->getLongitude(), $Coordinate->getLatitude());

		if ($timezone) {
			$timezoneOffset = (new DateTime(null, new DateTimeZone($timezone)))->setTimestamp($localTime)->getOffset() / 60;

			if (!$updateActivityTime) {
				$UpdateOnlyOffset->execute([
					'offset' => $timezoneOffset,
					'id' => $activity['id']
				]);
			}
		}
	}

	if ($updateActivityTime) {
		$UpdateTime->execute([
			'time' => $localTime,
			'activityid' => $activity['activity_id'] + $offset,
			'offset' => $timezoneOffset,
			'id' => $activity['id']
		]);
	}

	echo '.';
}

$file = @fopen(".refactortimezone","w");
@fclose($file);

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
