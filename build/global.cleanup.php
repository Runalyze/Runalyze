<?php
/**
 * Script to run a global cleanup
 * 
 * You have to set your database connection within this file to enable the script.
 * Remember to delete your credentials afterwards to protect this script.
 * 
 * Hint: This script is currently only a dirty hack. Don't rely on this in future versions.
 * @since 2.0.0
 */
$hostname = '';
$database = '';
$username = '';
$password = '';

define('PREFIX', 'runalyze_');
define('CLI', false); // Set to true if running from command line
define('OUTPUT', false); // Set to true for outputting detailed info

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

	require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DatenbankCleanup/Job.php';
	require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DatenbankCleanup/JobGeneral.php';
	require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DatenbankCleanup/JobLoop.php';
}

/**
 * Set class to handle account id
 * @see SessionAccountHandler
 */
class GlobalCleanupAccount {
	public static $ID;
}

/**
 * Prepare global cleanup
 */
$HasColumn = $PDO->query('SHOW COLUMNS FROM `'.PREFIX.'account` LIKE "cleanup"')->fetch();

if (!$HasColumn) {
	$PDO->exec('ALTER TABLE `'.PREFIX.'account` ADD `cleanup` TINYINT NOT NULL AFTER `id`');
	echo 'Added column \'cleanup\' to database.'.EOL;
	echo EOL;
}

$count = $PDO->query('SELECT COUNT(*) FROM `'.PREFIX.'account` WHERE `cleanup`=1')->fetchColumn();

echo 'Table '.PREFIX.'account has column \'cleanup\'.'.EOL;
echo ' - already cleaned: '.$count.EOL;
echo EOL;
echo 'Start to clean accounts ';

$_POST = array(
	Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral::ENDURANCE => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral::EQUIPMENT => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral::MAX_TRIMP => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral::VDOT => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral::VDOT_CORRECTOR => true,

	Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop::ELEVATION => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop::JD_POINTS => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop::TRIMP => true,
	Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop::VDOT => true
);

/**
 * Run single cleanups
 */
$Accounts = $PDO->query('
	SELECT
		id
	FROM `'.PREFIX.'account`
	WHERE `cleanup`=0'
);
$AccountUpdate = $PDO->prepare('UPDATE `'.PREFIX.'account` SET `cleanup`=1 WHERE `id`=?');

while ($Account = $Accounts->fetch()) {
	GlobalCleanupAccount::$ID = $Account['id'];
	DB::getInstance()->setAccountID($Account['id']);

	Runalyze\Context::reset();
	Runalyze\Configuration::loadAll($Account['id']);

	$JobLoop = new Runalyze\Plugin\Tool\DatabaseCleanup\JobLoop();
	$JobLoop->run();

	$JobGeneral = new Runalyze\Plugin\Tool\DatabaseCleanup\JobGeneral();
	$JobGeneral->run();

	if (OUTPUT) {
		echo $Account['id'].':'.EOL;
		echo implode(EOL, $JobLoop->messages());
		echo implode(EOL, $JobGeneral->messages());
		echo EOL.EOL;
	}

	$AccountUpdate->execute(array($Account['id']));

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

echo 'You are done. All accounts are cleaned.'.EOL;

$PDO->exec('ALTER TABLE `'.PREFIX.'account` DROP `cleanup`');
echo 'The column \'cleanup\' has been dropped.'.EOL;

echo EOL;
echo 'Remember to unset your credentials within this file.'.EOL;
echo '(Or simply delete this file if you are not working on our git repository)'.EOL;