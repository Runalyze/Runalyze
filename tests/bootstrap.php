<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Hannes
 */
// TODO: check include path
ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../../../php/PEAR');

// put your code here
spl_autoload_register(function ($className) {

    $possibilities = array(
		__DIR__.'/../inc/class.'.$className.'.php',
		__DIR__.'/../inc/html/class.'.$className.'.php',
		__DIR__.'/../inc/training/class.'.$className.'.php',
		__DIR__.'/../inc/system/class.'.$className.'.php'
    );

    foreach ($possibilities as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});

// TODO: make all frontend files available without errors ;)
?>