<?php
/**
 * This file can be used to compress all JavaScript-files.
 * It takes the content from all needed *.js-files and compresses it with the YUI-compressor.
 * 
 * You have to download the YUI-compressor by yourself:
 *  - http://yui.github.io/yuicompressor/
 */

// Windows or Unix?
$WINDOWS		= false;
// *.jar-file of YUI-compressor
$COMPRESSOR		= 'yuicompressor-2.4.8.jar';
// Scripts-file for developing
$SCRIPT			= 'scripts.js';
// Scripts-file for usage (minifies)
$SCRIPT_MIN		= 'scripts.min.js';

/**
 * Don't change something below.
 */
$IGNORE_ROOT = true;
include '../inc/system/define.files.php';

foreach ($JS_FILES as &$File)
	$File = '../'.$File;

$Copy     = $WINDOWS ? 'copy /b '.implode('+', $JS_FILES).' '.$SCRIPT : 'cat '.implode(' ', $JS_FILES).' > '.$SCRIPT;
$Compress = 'java -jar '.$COMPRESSOR.' \\
    --type js \\
    --nomunge \\
    -o '.$SCRIPT_MIN.' \\
	'.$SCRIPT;

system($Copy);
system($Compress);