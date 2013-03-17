<?php
/**
 * Bootstrap for PHPUnit
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
//ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../php/PEAR');

if (!defined('FRONTEND_PATH'))
	define('FRONTEND_PATH', dirname(__FILE__).'/../inc/');

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

if (!defined('CONF_VDOT_DAYS'))
	define('CONF_VDOT_DAYS', 30);

$_SERVER['REQUEST_URI'] = '/runalyze/index.php';
$_SERVER['SCRIPT_NAME'] = '/runalyze/index.php';

date_default_timezone_set('Europe/Berlin');

spl_autoload_register(function ($className) {
    $possibilities = array(
		__DIR__.'/../inc/class.'.$className.'.php',
		__DIR__.'/../inc/calculate/class.'.$className.'.php',
		__DIR__.'/../inc/draw/class.'.$className.'.php',
		__DIR__.'/../inc/export/class.'.$className.'.php',
		__DIR__.'/../inc/html/class.'.$className.'.php',
		__DIR__.'/../inc/html/formular/class.'.$className.'.php',
		__DIR__.'/../inc/import/class.'.$className.'.php',
		__DIR__.'/../inc/plugin/class.'.$className.'.php',
		__DIR__.'/../inc/system/class.'.$className.'.php',
		__DIR__.'/../inc/training/class.'.$className.'.php',
		__DIR__.'/../inc/training/formular/class.'.$className.'.php'
    );

    foreach ($possibilities as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});

Mysql::connect('127.0.0.1', 'root', '', 'runalyze_unittest');
?>