<?php
use League\Geotools\Geohash\Geohash;
use League\Geotools\Coordinate\Coordinate;
/**
 * Script to refactor equipment
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
require 'vendor/autoload.php';

$host = '';
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_');
define('LIMIT', 100); // Limit number of accounts to refactor per request
define('CLI', false); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql
define('CHECK_INNODB', true); // Set to false if you don't want or can't use InnoDB as your storage engine

// Uncomment these lines to unset time/memory limits
#@ini_set('memory_limit', '-1');
#if (!ini_get('safe_mode')) { @set_time_limit(0); }



/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

$starttime = microtime(true);

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
		$PDO = new PDO('mysql:dbname='.$database.';host='.$host, $username, $password);
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
// TODO: final version needs to check if `lats` etc. do still exist
$IsNotRefactored = true;

if (!$IsNotRefactored) {
	echo 'The database is already refactored.'.NL;
	exit;
}


/**
 * Overview for data
 */
// TODO: final version needs to check for empty `geohashes` columns and use LIMIT
$Routes = $PDO->query('SELECT id, lats, lngs, startpoint_lat, startpoint_lng, endpoint_lat, endpoint_lng, min_lat, min_lng, max_lat, max_lng FROM runalyze_route WHERE lats != "" && id > 2127');
$InsertGeohash = $PDO->prepare('UPDATE '.PREFIX.'route SET `geohashes`=:geohash, `startpoint`=:startpoint, `endpoint`=:endpoint, `min`=:min, `max`=:max WHERE `id` = :id');
while ($Route = $Routes->fetch()) {
    $lats = explode("|", $Route['lats']);
    $lngs = explode("|", $Route['lngs']);
    $quantity = count($lats);
    $geohashArray = array();
	$Coordinate = new Coordinate(array(0, 0));

    for($i=0; $i< $quantity; $i++) {
		// TODO: use a persisent Geohash object as soon as that's possible, see https://github.com/thephpleague/geotools/issues/73
		$Coordinate->setLatitude($lats[$i]);
		$Coordinate->setLongitude($lngs[$i]);
	$geohashArray[] = (new Geohash)->encode($Coordinate, 12)->getGeohash();
    }
	$InsertGeohash->execute(array(
				':geohash' => implode("|", $geohashArray),
				 ':id' => $Route['id'],
				 ':startpoint' => (new Geohash)->encode(new Coordinate(array($Route['startpoint_lat'], $Route['startpoint_lng'])), 10)->getGeohash(),
				 ':endpoint' => (new Geohash)->encode(new Coordinate(array($Route['endpoint_lat'], $Route['endpoint_lng'])), 10)->getGeohash(),
				 ':min' => (new Geohash)->encode(new Coordinate(array($Route['min_lat'], $Route['min_lng'])), 10)->getGeohash(),
				 ':max' => (new Geohash)->encode(new Coordinate(array($Route['max_lat'], $Route['max_lng'])), 10)->getGeohash()
			    ));
    echo "\033[7D";
    $diff = count($Route['id']);
    echo str_pad($Route['id'], 7-$diff, ' ', STR_PAD_LEFT);
}

        echo 'done;'.NL;
        echo NL;
        echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
        echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
        echo NL;

// TODO: final version needs to remove old columns (`lats`, ...) afterwards
