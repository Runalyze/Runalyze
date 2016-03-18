<?php
/**
 * RUNALYZE
 * 
 * @author Runalyze
 * @copyright http://www.runalyze.com/
 */
if (!file_exists('../data/config.php')) {
	include '../install.php';
	exit();
}

$Frontend = new Frontend(true);
$Frontend->displayAdminView();
