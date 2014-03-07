<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/
 
 $JS_FILES = array();
 
 if (file_exists('../../inc/system/define.files.php')) {
	include '../../inc/system/define.files.php';
 }
 
return array(
    'js' => $JS_FILES
);