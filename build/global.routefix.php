<?php
/**
 * Script to fix start/end/min/max for all routes
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 * 
 * Hint: This script is currently only a dirty hack. Don't rely on this in future versions.
 * @since 2.1
 */
$hostname = '';
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

if (empty($database) && empty($hostname)) {
	echo 'Database connection has to be set within the file.'.EOL;
	exit;
} else {
	date_default_timezone_set('Europe/Berlin');

	define('FRONTEND_PATH', __DIR__.'/../inc/');

	require_once FRONTEND_PATH.'/system/class.Autoloader.php';
	new Autoloader();

	try {
		DB::connect($hostname, $username, $password, $database);
		$PDO = DB::getInstance();
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $E) {
		echo 'Database connection failed: '.$E->getMessage().EOL;
		exit;
	}

	require_once FRONTEND_PATH.'../lib/phpfastcache/phpfastcache.php';
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
		`lats`,
		`lngs`
	FROM `'.PREFIX.'route`
	WHERE (`lats` != "" AND `lngs` != "") AND (`startpoint_lat`=0 OR `startpoint_lng`=0 OR `endpoint_lng`=0 OR `endpoint_lng`=0 OR `min_lng`=0 OR `max_lng`=0 OR `min_lat`=0 OR `max_lat`=0)'
);
$Updater = new Runalyze\Model\Route\Updater($PDO);

while ($Route = $Routes->fetch()) {
	$Updater->setAccountID($Route['accountid']);
	GlobalCleanupAccount::$ID = $Route['accountid'];
	$PDO->setAccountID($Route['accountid']);

	$Updater->update(new Runalyze\Model\Route\Entity($Route), array(
		'startpoint_lat',
		'startpoint_lng',
		'endpoint_lat',
		'endpoint_lng',
		'min_lng',
		'max_lng',
		'min_lat',
		'max_lat'
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