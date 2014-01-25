<?php
/**
 * RUNALYZE - Updater
 *
 * @author Hannes Christiansen
 * @copyright http://runalyze.laufhannes.de/
 * @package Runylze
 *
 * With this file you are able to update RUNALYZE.
 * Don't change anything in this file!
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Update von Runalyze</title>

	<script type="text/javascript" src="lib/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="lib/jquery.backgroundStretch.js"></script>
</head>

<body id="installer">

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<div class="panel-heading"><h1>Update von Runalyze</h1></div>

	<div class="panel-content" style="padding:5px 70px;">
<?php
require_once 'inc/system/class.Mysql.php';
require_once 'inc/class.Installer.php';
require_once 'inc/class.InstallerUpdate.php';

$Updater = new InstallerUpdate();
$Updater->display();

include 'inc/tpl/tpl.installerFooter.php';
?>