<?php
use League\Geotools\Geohash\Geohash;
use League\Geotools\Coordinate\Coordinate;
/**
 * Script to refactor coordinates to geohashes
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
require 'vendor/autoload.php';

$host = '';
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_'); // Prefix of your RUNALYZE installation
define('LIMIT', 50); // Limit number of trainings to refactor per request
define('CLI', false); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql


// Uncomment these lines to unset time/memory limits
#@ini_set('memory_limit', '-1');
#if (!ini_get('safe_mode')) { @set_time_limit(0); }



/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

$starttime = microtime(true);
$maxtime = ini_get('max_execution_time');
if(!CLI) {
    echo '<meta http-equiv="refresh" content="'.$maxtime.'; URL=refactor-geohash.php">';
}
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

$columns = $PDO->query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS  WHERE TABLE_SCHEMA = "'.$database.'" AND TABLE_NAME ="'.PREFIX.'route"')->fetchAll(PDO::FETCH_COLUMN, 0);
if (!in_array('geohashes', $columns)) {
      echo 'Update your installation from v2.2 to v2.3 via update.php'.NL;
      exit;
}

if (!in_array('lats', $columns)) {
	echo 'The Route table has already been refactored.'.NL;
       	exit;
}
 $IsNotRefactored = true;

/**
 * Overview for data
 */
$Routes = $PDO->query('SELECT id, lats, lngs, startpoint_lat, startpoint_lng, endpoint_lat, endpoint_lng, min_lat, min_lng, max_lat, max_lng FROM runalyze_route WHERE lats IS NOT NULL AND `geohashes` IS NULL LIMIT '.LIMIT);
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
    if (CLI) {
    echo "\033[7D";
    $diff = count($Route['id']);
    echo str_pad($Route['id'], 7-$diff, ' ', STR_PAD_LEFT);
    } else { echo "."; }
}

        echo 'done;'.NL;
        echo NL;
        echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
        echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
        echo NL;


$countLats = $PDO->query('SELECT COUNT(*) as c FROM runalyze_route WHERE `lats` != \'\' ')->fetchColumn();
$countGeohashes = $PDO->query('SELECT COUNT(*) as c FROM runalyze_route WHERE geohashes IS NOT NULL AND `lats` != \'\'')->fetchColumn();
echo $countGeohashes;

if($countGeohashes == $countLats) {
    echo 'You are done. All routes are refactored. Dropping now all old coordinate columns'.NL;
    $PDO->exec('ALTER TABLE `'.PREFIX.'route` DROP `lats`, DROP `lngs`, DROP `startpoint_lat`, DROP `startpoint_lng`, DROP `endpoint_lat`, DROP `endpoint_lng`, DROP `min_lat`, DROP `min_lng`, DROP `max_lat`, DROP `max_lng`');        
    echo 'All old coordinate columns have been dropped'.NL;
    echo NL;
    echo 'Remember to unset your credentials within this file.'.NL;
    echo '(Or simply delete this file if you are not working on our git repository)'.NL;
} else {
	if (CLI) {
		echo '... call the script again to continue'.NL;
	} else {
		echo '... <a href="javascript:location.reload()">reload to continue</a>';
	}
}