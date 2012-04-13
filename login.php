<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://www.runalyze.de/
 */
if (!file_exists('config.php')) {
	include 'install.php';
	exit();
}

require 'inc/class.Frontend.php';
$Frontend = new Frontend(true);

if (isset($_GET['out']))
	SessionHandler::logout();

if (SessionHandler::isLoggedIn())
	header('Location: index.php');

// TODO: Register?
//include 'inc/system/class.SessionHandler.php';
//include 'inc/system/define.consts.php';

$title = 'Runalyze v'.RUNALYZE_VERSION;
$tpl   = 'tpl.loginWindow.php';

if (isset($_GET['chpw']))
	$tpl = 'tpl.loginWindow.setNewPassword.php';

include 'inc/html/tpl/tpl.installerHeader.php';
include 'inc/html/tpl/'.$tpl;
include 'inc/html/tpl/tpl.installerFooter.php';