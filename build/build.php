<?php
/*
 * This file can be used to build Runalyze.
 * Usage: php build.php [COMPONENT]...
*/



/**
 * Scripts to call
 * @var array
 */
$SCRIPTS = array();

/**
 * Build flag
 * @var bool
 */
define('RUNALYZE_BUILD', true);

/**
 * Root directory
 * @var string
 */
$ROOT = dirname(__FILE__).'/';

/**
 * Find build scripts and include them
 */
findBuildScripts( $ROOT);
findBuildScripts( $ROOT.'../plugin/');

if ($argc > 1) {
	$request_run = $argv;
	array_shift($request_run);
	$request_run = array_map(function ($v) {
		return 'build.' . $v . '.php';
	}, $request_run);

	$SCRIPTS = array_intersect($SCRIPTS, $request_run);
}

foreach ($SCRIPTS as $path) {
	include dirname(__FILE__).'/'.$path;
}

/**
 * Scan directory
 * @param string $dir
 * @param string $classmap
 */
function findBuildScripts($dir) {
	global $ROOT;
	global $SCRIPTS;

	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..') {
			if (is_dir($dir.$file)) {
				findBuildScripts($dir.$file.'/');
			} else {
				if (preg_match('/build\..*\.php/',$file)) {
					$SCRIPTS[] = substr( $dir.$file, strlen($ROOT) );
				}
			}
		}
	}

	closedir($handle);
}