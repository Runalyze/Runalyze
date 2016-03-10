<?php
/**
 * CLI-Script to generate translations
 */
if (!defined('RUNALYZE_BUILD'))
	die('You\'re not allowed to do that.');

echo 'Building translations...'.PHP_EOL;

$supportedLanguages=array();

require(__DIR__.'/../data/config_lang.php');

$LOCALE_DIR=__DIR__.'/../inc/locale';

foreach ($supportedLanguages as $lang => $larr){
    $lang_dir=$LOCALE_DIR.'/'.$lang.'/LC_MESSAGES/';
    if (is_file($lang_dir.'runalyze.po')){
        echo $lang.' ';
        system ('msgfmt -v '.$lang_dir.'runalyze.po -o '.$lang_dir.'runalyze.mo');
    }
}

?>
