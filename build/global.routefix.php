<?php
/**
 * Script to fix start/end/min/max for all routes
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 * 
 * Hint: This script is currently only a dirty hack. Don't rely on this in future versions.
 * @since 2.1, updated for 2.4
 */
$host = '';
$port = 3306;
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_');
define('CLI', false); // Set to true if running from command line

// Uncomment these lines to unset time/memory limits
//@ini_set('memory_limit', '-1');
//if (!ini_get('safe_mode')) { @set_time_limit(0); }


/*******************************************************************************
 * SCRIPT STARTS - YOU DON'T NEED TO CHANGE ANYTHING BELOW
 ******************************************************************************/

$starttime = microtime(true);

/**
 * Protect script
 */
define('EOL', CLI ? PHP_EOL : '<br>'.PHP_EOL);
define('GLOBAL_CLEANUP', true);

if (empty($database) && empty($host)) {
	echo 'Database connection has to be set within the file.'.EOL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	define('FRONTEND_PATH', __DIR__.'/../inc/');

	require_once FRONTEND_PATH.'/system/class.Autoloader.php';
	new Autoloader();

	try {
		DB::connect($host, $port, $username, $password, $database);
		$PDO = DB::getInstance();
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage().EOL;
		exit;
	}

	require_once FRONTEND_PATH.'/system/class.Cache.php';
	new Cache();
	
	require_once FRONTEND_PATH.'/system/define.consts.php';

	if (!function_exists('__')) {
		function __($t) {
			return $t;
		}
	}
}

/**
 * Set class to handle account id
 * @see SessionAccountHandler
 */
class GlobalCleanupAccount {
	public static $ID;
}

/**
 * Prepare fix
 */
echo 'Start to fix routes ';

/**
 * Run single cleanups
 */
$Routes = $PDO->query('
	SELECT
		`id`,
		`accountid`,
		`geohashes`
	FROM `'.PREFIX.'route`
	WHERE `startpoint` = "7zzzzzzzzz" OR (`startpoint` IS NOT NULL AND `min` IS NULL)'
);
$Updater = new Runalyze\Model\Route\Updater($PDO);

while ($Route = $Routes->fetch()) {
	$Updater->setAccountID($Route['accountid']);
	GlobalCleanupAccount::$ID = $Route['accountid'];
	$PDO->setAccountID($Route['accountid']);
	$RouteEntity = new Runalyze\Model\Route\Entity($Route);
	$RouteEntity->forceToSetMinMaxFromGeohashes();

	$Updater->update($RouteEntity, array(
		'startpoint',
		'endpoint',
		'min',
		'max'
	));

	echo '.'.(CLI ? '' : ' ');
}

/**
 * Finish
 */
echo 'done;'.EOL;
echo EOL;
echo 'Time: '.(microtime(true) - $starttime).'s'.EOL;
echo 'Memory peak: '.memory_get_peak_usage().'B'.EOL;
echo EOL;

echo 'You are done. All routes are cleaned.'.EOL;

echo EOL;
echo 'Remember to unset your credentials within this file.'.EOL;
echo '(Or simply delete this file if you are not working on our git repository)'.EOL;