<?php
/**
 * Bootstrap for PHPUnit
 * @author Hannes Christiansen
 * @package Runalyze\PHPUnit
 */
//ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../php/PEAR');

if (!defined('FRONTEND_PATH'))
	define('FRONTEND_PATH', dirname(__FILE__).'/../inc/');

require_once dirname(__FILE__).'/../inc/system/class.Autoloader.php';
new Autoloader();

date_default_timezone_set('Europe/Berlin');

if (!defined('NL'))
	define('NL', "\n");

if (!defined('PREFIX'))
	define('PREFIX', 'runalyze_');

if (!defined('CONF_VDOT_CORRECTOR'))
	define('CONF_VDOT_CORRECTOR', 1);

if (!defined('RUNALYZE_TEST'))
	define('RUNALYZE_TEST', true);

if (!defined('DAY_IN_S'))
	define('DAY_IN_S', 86400);

if (!defined('CONF_MAINSPORT'))
	define('CONF_MAINSPORT', 1);

if (!defined('CONF_RUNNINGSPORT'))
	define('CONF_RUNNINGSPORT', 1);

if (!defined('CONF_WK_TYPID'))
	define('CONF_WK_TYPID', 5);

if (!defined('CONF_VDOT_DAYS'))
	define('CONF_VDOT_DAYS', 30);

if (!defined('CONF_CTL_DAYS'))
	define('CONF_CTL_DAYS', 42);

if (!defined('CONF_ATL_DAYS'))
	define('CONF_ATL_DAYS', 7);

if (!defined('CONF_MAX_ATL'))
	define('CONF_MAX_ATL', 0);

if (!defined('CONF_MAX_CTL'))
	define('CONF_MAX_CTL', 0);

if (!defined('CONF_MAX_TRIMP'))
	define('CONF_MAX_TRIMP', 0);

if (!defined('CONF_TRAINING_MAKE_PUBLIC'))
	define('CONF_TRAINING_MAKE_PUBLIC', 0);

if (!defined('CONF_TRAINING_LOAD_WEATHER'))
	define('CONF_TRAINING_LOAD_WEATHER', 0);

if (!defined('CONF_TRAINING_DECIMALS'))
	define('CONF_TRAINING_DECIMALS', 2);

if (!defined('CONF_TRAINING_SORT_SPORTS'))
	define('CONF_TRAINING_SORT_SPORTS', 'id-asc');
if (!defined('CONF_TRAINING_SORT_SHOES'))
	define('CONF_TRAINING_SORT_SHOES', 'id-asc');
if (!defined('CONF_TRAINING_SORT_TYPES'))
	define('CONF_TRAINING_SORT_TYPES', 'id-asc');

$_SERVER['REQUEST_URI'] = '/runalyze/index.php';
$_SERVER['SCRIPT_NAME'] = '/runalyze/index.php';

Mysql::connect('127.0.0.1', 'root', '', 'runalyze_unittest');

// Load helper class
Helper::Unknown('');
?>