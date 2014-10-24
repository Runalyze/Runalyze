<?php
/**
 * This file can be used to build Runalyze.
 */
/**
 * Scripts to call
 * @var array
 */
$SCRIPTS = array(
	'build.js.php',
	'build.classmap.php',
	'build.pluginmap.php'
);

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
findBuildScripts( $ROOT.'../plugin/');

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
				if ($file == 'build.php') {
					$SCRIPTS[] = substr( $dir.$file, strlen($ROOT) );
				}
			}
		}
	}

	closedir($handle);
}