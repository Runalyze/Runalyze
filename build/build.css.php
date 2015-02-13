<?php
/**
 * CLI-Script to generate translations
 */
if (!defined('RUNALYZE_BUILD'))
	die('You\'re not allowed to do that.');

echo 'Building css...'.PHP_EOL;

$CSS_DIR=__DIR__.'/../lib/less/';
system ('lessc --relative-urls '.$CSS_DIR.'runalyze-style.less '.$CSS_DIR.'runalyze-style.css' );

?>
