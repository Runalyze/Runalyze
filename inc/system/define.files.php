<?php
/*
 * All needed JS/CSS-Files have to be in this array.
 * This file will be loaded by Minify to compress all files.
 */
$JS_FILES = array(
	'lib/jquery-2.1.0.min.js',
	'lib/jquery.form.js',
	'lib/jquery.metadata.js',
	'lib/jquery.tablesorter.js',
	'lib/jquery.tablesorter.pager.js',
	'lib/bootstrap-tooltip.js',

	'lib/fineuploader-3.5.0.min.js',

	'lib/jquery.datepicker.js',

	'lib/jquery.chosen.min.js',

	'lib/runalyze.lib.log.js',
	'lib/runalyze.lib.plot.js',
	'lib/runalyze.lib.plot.options.js',
	'lib/runalyze.lib.plot.saver.js',
	'lib/runalyze.lib.plot.events.js',
	'lib/runalyze.lib.tablesorter.js',
	'lib/runalyze.lib.js',

	'lib/flot-0.8.1/base64.js',

	'lib/flot-0.8.1/jquery.flot.min.js',
	'lib/flot-0.8.1/jquery.flot.resize.min.js',
	'lib/flot-0.8.1/jquery.flot.selection.js',
	'lib/flot-0.8.1/jquery.flot.crosshair.js',
	'lib/flot-0.8.1/jquery.flot.navigate.min.js',
	'lib/flot-0.8.1/jquery.flot.stack.min.js',
	//'lib/flot-0.8.1/jquery.flot.text.js',
	'lib/flot-0.8.1/jquery.flot.textLegend.js',
	'lib/flot-0.8.1/jquery.flot.orderBars.js',
	'lib/flot-0.8.1/jquery.flot.hiddengraphs.js',
	'lib/flot-0.8.1/jquery.flot.canvas.js',
	'lib/flot-0.8.1/jquery.flot.time.min.js',

	'lib/leaflet/leaflet.js',
	'lib/leaflet/runalyze.leaflet.js',
	'lib/leaflet/runalyze.leaflet.layers.js',
	'lib/leaflet/runalyze.leaflet.routes.js',
);

/**
 * Define correct filepaths 
 */
$root = substr($_SERVER['SCRIPT_FILENAME'], 0, strripos($_SERVER['SCRIPT_FILENAME'], "/") - 7);

function Runalyze__FileTransformerForMinify($file) {
	global $root;

	if (substr($file,0,4) == 'http')
		return $file;

	return $root.$file;
}

if (!isset($IGNORE_ROOT))
	$JS_FILES  = array_map('Runalyze__FileTransformerForMinify', $JS_FILES);