<?php
/**
 * This file can be used to compress all JavaScript-files.
 * It takes the content from all needed *.js-files and compresses it with the YUI-compressor.
 * 
 * You have to download the YUI-compressor by yourself:
 *  - http://yui.github.io/yuicompressor/
 */
if (!defined('RUNALYZE_BUILD'))
	die('You\'re not allowed to do that.');

echo 'Building javascript files...'.PHP_EOL;

// Windows or Unix?
$WINDOWS		= false;
// *.jar-file of YUI-compressor
$COMPRESSOR		=  dirname(__FILE__).'/yuicompressor-2.4.8.jar';
// Scripts-file for developing
$SCRIPT			= dirname(__FILE__).'/scripts.js';
// Scripts-file for usage (minifies)
$SCRIPT_MIN		= dirname(__FILE__).'/scripts.min.js';

/**
 * Don't change something below.
 */
if (!file_exists($COMPRESSOR)) {
	echo "Build javascript files FAILED! YUI compressor is missing.\n";
} else {
	include dirname(__FILE__).'/../inc/system/define.files.php';

	foreach ($JS_FILES as &$File)
		$File = dirname(__FILE__).'/../'.$File;

	$Copy     = $WINDOWS ? 'copy /b '.implode('+', $JS_FILES).' '.$SCRIPT : 'cat '.implode(' ', $JS_FILES).' > '.$SCRIPT;
	$Compress = 'java -jar '.$COMPRESSOR.' \\
		-o '.$SCRIPT_MIN.' \\
		'.$SCRIPT;

	system($Copy);
	system($Compress);

	echo "Successfully built and compressed javascript files.\n";
}