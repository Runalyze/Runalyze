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

//@ini_set('memory_limit', '-1');
//if (!ini_get('safe_mode')) { @set_time_limit(0); }

$starttime = microtime(true);

/**
 * Protect script
 */
if (empty($database) && empty($hostname)) {
	echo 'Database connection has to be set within the file.';
	exit;
} else {
	define('NL', "<br>\n\r");
	date_default_timezone_set('Europe/Berlin');

	try {
		$PDO = new PDO('mysql:dbname='.$database.';host='.$hostname, $username, $password);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//$PDO->exec('SET GLOBAL max_allowed_packet=1073741824;');
		//$PDO->exec('SET GLOBAL key_buffer_size=1073741824;');
	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage();
		exit;
	}
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
echo NL;

/**
 * Navigation
 */
$HasColumn = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'training` LIKE "refactored"')->fetch();

if (!$HasColumn) {
	if (isset($_GET['done'])) {
		echo 'You are done. Unset your credentials or delete this file.';
		exit;
	}

	$PDO->exec('ALTER TABLE `'.PREFIX.'training` ADD `refactored` TINYINT NOT NULL AFTER `id`');
	echo 'Added column \'refactored\' to database.'.NL;
	echo NL;
	echo '... <a href="javascript:location.reload()">reload to continue</a>';
	exit;
} else {
	$count = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `refactored`=1')->fetchColumn();

	echo 'Table '.PREFIX.'training has column \'refactored\'.'.NL;
	echo ' - refactored: '.$count.'/'.$tables[PREFIX.'training'].NL;
	echo NL;

	if ($count == $tables[PREFIX.'training']) {
		echo 'You are done. All rows are refactored.'.NL;

		if (isset($_GET['done'])) {
			$PDO->exec('ALTER TABLE `'.PREFIX.'training` DROP `refactored`');
			echo 'The column \'refactored\' has been dropped.'.NL;
		} else {
			echo '<a href="?done">Click to delete column \'refactored\'</a>'.NL;
		}

		echo NL;
		echo '=&gt; Remember to unset your credentials within this file.'.NL;
		echo '(Or simply delete this file if you are working on our git repository)'.NL;
	} else {
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

			echo '. ';
		}

		echo 'done;'.NL;
		echo NL;
		echo 'Time: '.(microtime(true) - $starttime).'s'.NL;
		echo 'Memory peak: '.memory_get_peak_usage().'B'.NL;
		echo NL;
		echo '... <a href="javascript:location.reload()">reload to continue</a>';
	}
}

function notNull($value) {
	if (is_null($value))
		return '';

	return $value;
}