<?php
/**
 * Script to refactor database
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 */
$hostname = '';
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_');
define('LIMIT', 100); // Limit number of activities to refactor per request
define('CLI', false); // Set to true if running from command line
define('SET_GLOBAL_PROPERTIES', false); // Set to true to set max_allowed_packet and key_buffer_size for mysql

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

if (empty($database) && empty($hostname)) {
	echo 'Database connection has to be set within the file.'.NL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	try {
		$PDO = new PDO('mysql:dbname='.$database.';host='.$hostname, $username, $password);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
$IsNotRefactored = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'training` LIKE "gps_cache_object"')->fetch();

if (!$IsNotRefactored) {
	echo 'The database is already refactored.'.NL;
	exit;
}


/**
 * Overview for data
 */
$HasTable = $PDO->query('SHOW TABLES LIKE "'.PREFIX.'trackdata"');

if (!$HasTable) {
	echo 'Cannot find table `'.PREFIX.'trackdata`. Please run latest sql updates.';
	exit;
} else {
	$tables = array(
		PREFIX.'training' => 0,
		PREFIX.'trackdata' => 0,
		PREFIX.'route' => 0
	);
	echo 'Counting rows ...'.NL;

	foreach ($tables as $table => &$count) {
		$count = $PDO->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn();
		echo ' - '.$table.': '.$count.' rows'.NL;
	}
}

echo NL;

/**
 * Navigation
 */
$HasColumn = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'training` LIKE "refactored"')->fetch();

if (!$HasColumn) {
	$PDO->exec('ALTER TABLE `'.PREFIX.'training` ADD `refactored` TINYINT NOT NULL AFTER `id`');
	echo 'Added column \'refactored\' to database.'.NL;
	echo NL;
}

$count = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `refactored`=1')->fetchColumn();

echo 'Table '.PREFIX.'training has column \'refactored\'.'.NL;
echo ' - refactored: '.$count.'/'.$tables[PREFIX.'training'].NL;
echo NL;

if ($count < $tables[PREFIX.'training']) {
	echo 'Refactor another '.min(LIMIT, $tables[PREFIX.'training']-$count).' activities ';

	$UpdateActivity = $PDO->prepare('UPDATE `'.PREFIX.'training` SET `refactored`=1, `routeid`=:routeid WHERE `id`=:id');
	$InsertTrackdata = $PDO->prepare('INSERT INTO `'.PREFIX.'trackdata` (
			 accountid,  activityid,  time,  distance,  pace,  heartrate,  cadence,  power,  temperature
		) VALUES (
			:accountid, :activityid, :time, :distance, :pace, :heartrate, :cadence, :power, :temperature
	)');
	$InsertRoute = $PDO->prepare('INSERT INTO `'.PREFIX.'route` (
			 accountid,  name,  cities,  distance,  elevation,  lats,  lngs,  elevations_original,  elevations_corrected,
			 startpoint_lat,  startpoint_lng,  endpoint_lat,  endpoint_lng,  min_lat,  min_lng,  max_lat,  max_lng
		) VALUES (
			:accountid, :name, :cities, :distance, :elevation, :lats, :lngs, :elevations_original, :elevations_corrected,
			:startpoint_lat, :startpoint_lng, :endpoint_lat, :endpoint_lng, :min_lat, :min_lng, :max_lat, :max_lng
	)');

	$RouteLookup = $PDO->prepare('SELECT `id` FROM `'.PREFIX.'route` WHERE `name`=:name AND `distance`=:distance AND `lats`="" LIMIT 1');

	$Fetch = $PDO->query('
		SELECT
			id,
			accountid,
			distance,
			elevation_calculated,
			arr_lat,
			arr_lon,
			arr_alt_original,
			arr_alt,
			arr_time,
			arr_dist,
			arr_pace,
			arr_heart,
			arr_cadence,
			arr_power,
			arr_temperature,
			route
		FROM `'.PREFIX.'training`
		WHERE `refactored`=0
		LIMIT '.LIMIT
	);

	while ($Row = $Fetch->fetch()) {
		if (!empty($Row['route']) || (!empty($Row['arr_lat']) && !empty($Row['arr_lon']))) {
			if (strpos($Row['route'], ' - ') !== false || strpos($Row['route'], ' ') === false) {
				$route_name = $Row['route'];
				$route_cities = $Row['route'];
			} else {
				$route_name = $Row['route'];
				$route_cities = '';
			}

			if (!empty($Row['arr_lat']) && !empty($Row['arr_lon'])) {
				$route_lat = array_filter(explode('|', $Row['arr_lat']));
				$route_lng = array_filter(explode('|', $Row['arr_lon']));

				$NewRouteId = false;
			} else {
				$route_lat = array(0);
				$route_lng = array(0);

				$RouteLookup->execute(array(
					':name'		=> $route_name,
					':distance' => $Row['distance']
				));

				$NewRouteId = $RouteLookup->fetchColumn();
			}

			if ($NewRouteId === false) {
				$return = $InsertRoute->execute(array(
					':accountid'			=> $Row['accountid'],
					':name'					=> notNull($route_name),
					':cities'				=> notNull($route_cities),
					':distance'				=> $Row['distance'],
					':elevation'			=> $Row['elevation_calculated'],
					':lats'					=> notNull($Row['arr_lat']),
					':lngs'					=> notNull($Row['arr_lon']),
					':elevations_original'	=> notNull($Row['arr_alt_original']),
					':elevations_corrected'	=> notNull($Row['arr_alt']),
					':startpoint_lat'		=> reset($route_lat),
					':startpoint_lng'		=> reset($route_lng),
					':endpoint_lat'			=> end($route_lat),
					':endpoint_lng'			=> end($route_lng),
					':min_lat'				=> empty($route_lat) ? 0 : min($route_lat),
					':max_lat'				=> empty($route_lat) ? 0 : max($route_lat),
					':min_lng'				=> empty($route_lng) ? 0 : min($route_lng),
					':max_lng'				=> empty($route_lng) ? 0 : max($route_lng)
				));

				$NewRouteId = $PDO->lastInsertId();
			}
		} else {
			$NewRouteId = 0;
		}

		if (!empty($Row['arr_time']) || !empty($Row['arr_dist'])) {
			$InsertTrackdata->execute(array(
				':accountid'		=> $Row['accountid'],
				':activityid'		=> $Row['id'],
				':time'				=> notNull($Row['arr_time']),
				':distance'			=> notNull($Row['arr_dist']),
				':pace'				=> notNull($Row['arr_pace']),
				':heartrate'		=> notNull($Row['arr_heart']),
				':cadence'			=> notNull($Row['arr_cadence']),
				':power'			=> notNull($Row['arr_power']),
				':temperature'		=> notNull($Row['arr_temperature'])
			));
		}

		$UpdateActivity->execute(array(
			':id' => $Row['id'],
			':routeid' => $NewRouteId
		));

		echo '.'.(CLI ? '' : ' ');
	}

	echo 'done;'.NL;
	echo NL;
	echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
	echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
	echo NL;

	if (CLI) {
		echo '... call the script again to continue'.NL;
	} else {
		echo '... <a href="javascript:location.reload()">reload to continue</a>';
	}
}


if ($count + LIMIT >= $tables[PREFIX.'training']) {
	echo 'You are done. All rows are refactored.'.NL;

	$PDO->exec('ALTER TABLE `'.PREFIX.'training` DROP `refactored`');
	echo 'The column \'refactored\' has been dropped.'.NL;

	$PDO->exec('ALTER TABLE `'.PREFIX.'training` DROP `pace`,
		DROP `elevation_calculated`,
		DROP `arr_time`,
		DROP `arr_lat`,
		DROP `arr_lon`,
		DROP `arr_alt`,
		DROP `arr_alt_original`,
		DROP `arr_dist`,
		DROP `arr_heart`,
		DROP `arr_pace`,
		DROP `arr_cadence`,
		DROP `arr_power`,
		DROP `arr_temperature`,
		DROP `elevation_corrected`,
		DROP `gps_cache_object`');
	echo 'All unused columns of \'runalyze_training\' have been dropped.'.NL;

	echo NL;
	echo 'Remember to unset your credentials within this file.'.NL;
	echo '(Or simply delete this file if you are not working on our git repository)'.NL;
}

function notNull($value) {
	if (is_null($value))
		return '';

	return $value;
}