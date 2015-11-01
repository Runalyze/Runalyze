<?php
use League\Geotools\Geotools;
use \League\Geotools\Coordinate\Coordinate;
/**
 * Script to refactor equipment
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
require 'vendor/autoload.php';
include_once 'config.php';
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
$IsNotRefactored = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'shoe"');

if (!$IsNotRefactored) {
	echo 'The database is already refactored.'.NL;
	exit;
}


/**
 * Overview for data
 */
$geotools   = new Geotools();
$Routes = $PDO->query('SELECT id, lats, lngs, startpoint_lat, startpoint_lng, endpoint_lat, endpoint_lng, min_lat, min_lng, max_lat, max_lng FROM runalyze_route WHERE lats != ""');
$InsertGeohash = $PDO->prepare('UPDATE '.PREFIX.'route SET `geohashes`=:geohash, `startpoint`=:startpoint, `endpoint`=:endpoint, `min`=:min, `max`=:max WHERE `id` = :id');
while ($Route = $Routes->fetch()) {
    
	$coordinate = new Coordinate($Route['min_lat'].','.$Route['min_lng']);
	$min = $geotools->geohash()->encode($coordinate, 10);

	$coordinate = new Coordinate($Route['max_lat'].','.$Route['max_lng']);
	$max = $geotools->geohash()->encode($coordinate, 10);
    
	$coordinate = new Coordinate($Route['startpoint_lat'].','.$Route['startpoint_lng']);
	$startpoint = $geotools->geohash()->encode($coordinate, 10);

	$coordinate = new Coordinate($Route['endpoint_lat'].','.$Route['endpoint_lng']);
	$endpoint = $geotools->geohash()->encode($coordinate, 10);
    
    $lats = explode("|", $Route['lats']);
    $lngs = explode("|", $Route['lngs']);
    $quantity = count($lats);
    $geohashArray = array();
    for($i=0; $i< $quantity; $i++) {
	$coordinate = new Coordinate($lats[$i].','. $lngs[$i]);
	$geohash = $geotools->geohash()->encode($coordinate, 12);
	$geohashArray[] = $geohash->getGeohash();
    }
    $InsertGeohash->execute(array(
				':geohash' => implode("|", $geohashArray),
				 ':id' => $Route['id'],
				 ':startpoint' => $startpoint->getGeohash(),
				 ':endpoint' => $endpoint->getGeohash(),
				 ':min' => $min->getGeohash(),
				 ':max' => $max->getGeohash()
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

