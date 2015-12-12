<?php
/**
 * Script to calculate stride length for all activities
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
echo 'Start to calculate ';

/**
 * Run single cleanups
 */
$Tracks = $PDO->query('
	SELECT
		a.`id`,
        a.`sportid`,
		t.`accountid`,
		t.`time`,
		t.`distance`,
		t.`cadence`
	FROM `'.PREFIX.'trackdata` as t
    INNER JOIN `'.PREFIX.'training` as a ON a.`id` = t.`activityid`
	WHERE (t.`time` != "" AND t.`distance` != "" AND t.`cadence` != "")'
);
$Updater = new Runalyze\Model\Activity\Updater($PDO);

while ($Track = $Tracks->fetch()) {
	$Updater->setAccountID($Track['accountid']);
	GlobalCleanupAccount::$ID = $Track['accountid'];
	$PDO->setAccountID($Track['accountid']);

	$Activity = new Runalyze\Model\Activity\Entity(array(
		'id' => $Track['id'],
		Runalyze\Model\Activity\Entity::SPORTID => $Track['sportid']
	));

	$Updater->setTrackdata(new Runalyze\Model\Trackdata\Entity($Track));
	$Updater->update($Activity, array(
		'stride_length'
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

echo 'You are done. All stride lenghts are calculated.'.EOL;

echo EOL;
echo 'Remember to unset your credentials within this file.'.EOL;
echo '(Or simply delete this file if you are not working on our git repository)'.EOL;