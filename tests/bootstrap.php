<?php
/**
 * Bootstrap for PHPUnit
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../php/PEAR');

if (!defined('FRONTEND_PATH'))
	define('FRONTEND_PATH', dirname(__FILE__).'/../inc/');

if (!defined('PREFIX'))
	define('PREFIX', 'runalyze_');

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

Mysql::connect('localhost', 'root', '', 'runalyze_unittest');
?>