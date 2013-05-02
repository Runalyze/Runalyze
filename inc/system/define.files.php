<?php
/*
 * All needed JS/CSS-Files have to be in this array.
 * This file will be loaded by Minify to compress all files.
 */
$JS_FILES = array(
	'lib/jquery-1.7.1.min.js',
	'lib/jquery.form.js',
	'lib/jquery.metadata.js',
	'lib/jquery.tablesorter.js',
	'lib/jquery.tablesorter.pager.js',
	'lib/bootstrap-tooltip.js',

	'lib/fineuploader-3.5.0.min.js',

	'lib/jquery.datepicker.js',
	'lib/jquery.jbar.js',
	'lib/jquery.backgroundStretch.js',

	'lib/jquery.gmap3.min.js',

	'lib/runalyze.lib.log.js',
	'lib/runalyze.lib.plot.js',
	'lib/runalyze.lib.gmap.js',
	'lib/runalyze.lib.tablesorter.js',
	'lib/runalyze.lib.js',

	//'lib/flot/canvas2image.js',
	'lib/flot/base64.js',

	'lib/flot/jquery.plot.js',
	'lib/flot/jquery.qtip.min.js',
	'lib/flot/jquery.flot.min.js',
	'lib/flot/jquery.flot.resize.min.js',
	'lib/flot/jquery.flot.selection.min.js',
	'lib/flot/jquery.flot.crosshair.min.js',
	'lib/flot/jquery.flot.navigate.min.js',
	'lib/flot/jquery.flot.stack.min.js',
	'lib/flot/jquery.flot.text.js',
	'lib/flot/jquery.flot.orderBars.js',
);
	
$CSS_FILES = array(
	'style.css',
	'lib/sprites.css',
	'lib/flot.css',
	'lib/flot/qtip.css',
	'lib/bootstrap-tooltip.css',
	'lib/jquery.datepicker.css',
	'lib/jquery.tablesorter.css',
	'lib/jquery.jbar.css',
);

/**
 * Add plugin-files 
 */
$Files = glob('../../plugin/*/*.js');
if (is_array($Files))
	foreach ($Files as $file)
		$JS_FILES[] = substr($file,6);

$Files = glob('../../plugin/*/*.css');
if (is_array($Files))
	foreach ($Files as $file)
		$CSS_FILES[] = substr($file,6);

/**
 * Define correct filepaths 
 */
$root = substr($_SERVER['SCRIPT_FILENAME'], 0, strripos($_SERVER['SCRIPT_FILENAME'], "/"))."/";
$root = str_replace('runalyze/lib/min/', 'runalyze/', $root);

function Runalyze__FileTransformerForMinify($file) {
	global $root;

	if (substr($file,0,4) == 'http')
		return $file;

	return $root.$file;
}

$JS_FILES  = array_map('Runalyze__FileTransformerForMinify', $JS_FILES);
$CSS_FILES = array_map('Runalyze__FileTransformerForMinify', $CSS_FILES);