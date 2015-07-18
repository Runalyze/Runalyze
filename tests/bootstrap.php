<?php
/**
 * Bootstrap for PHPUnit
 * @author Hannes Christiansen
 * @package Runalyze\PHPUnit
 */
//ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../php/PEAR');

if (!defined('RUNALYZE_TEST'))
	define('RUNALYZE_TEST', true);

if (!defined('FRONTEND_PATH'))
	define('FRONTEND_PATH', dirname(__FILE__).'/../inc/');

require_once FRONTEND_PATH.'system/class.Autoloader.php';
new Autoloader();

require_once FRONTEND_PATH.'../lib/phpfastcache/phpfastcache.php';
require_once FRONTEND_PATH.'system/class.Cache.php';
new Cache();
Cache::clean();

date_default_timezone_set('Europe/Berlin');

if (!defined('NL'))
	define('NL', "\n");

if (!defined('NBSP'))
	define('NBSP', '&nbsp;');

if (!defined('PREFIX'))
	define('PREFIX', 'runalyze_');


if (!defined('DAY_IN_S'))
	define('DAY_IN_S', 86400);

$_SERVER['REQUEST_URI'] = '/runalyze/index.php';
$_SERVER['SCRIPT_NAME'] = '/runalyze/index.php';

// Load and clean database
DB::connect('127.0.0.1', 'root', '', 'runalyze_unittest');
DB::getInstance()->exec('SET GLOBAL sql_mode="TRADITIONAL"');

// Language functions
if (!function_exists('__')) {
	function __($text, $domain = 'runalyze') {
		return $text;
	}
}

if (!function_exists('_e')) {
	function _e($text, $domain = 'runalyze') {
		echo $text;
	}
}

if (!function_exists('_n')) {
	function _n($msg1, $msg2, $n, $domain = 'runalyze') {
		if ($n == 1)
			return $msg1;

		return $msg2;
	}
}

if (!function_exists('_ne')) {
	function _ne($msg1, $msg2, $n, $domain = 'runalyze') {
		if ($n == 1)
			echo $msg1;

		echo $msg2;
	}
}

// Load helper class
Helper::Unknown('');