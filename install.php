<?php
/**
 * RUNALYZE
 *
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://runalyze.laufhannes.de/
 *
 * With this file you are able to install RUNALYZE.
 * Don't change anything in this file!
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Installation: Runalyze</title>

	<script type="text/javascript" src="lib/jQuery.1.4.2.js"></script>
	<script type="text/javascript" src="lib/jQuery.backgroundStretch.js"></script>
</head>

<body id="installer">

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<h1>Installation von Runalyze</h1>

	<div style="padding:0 70px;">
<?php
require_once 'inc/class.Mysql.php';
require_once 'inc/class.Installer.php';

$Installer = new Installer();
?>
	</div>
</div>

<div id="copy">

	<span class="right">
		&copy; Programmierung und Design von
		<a id="copy" href="http://www.laufhannes.de/" title="Laufhannes" target="_blank">laufhannes.de</a>
	</span>

	<span class="left b">
		Runalyze
	</span>

</div>

</body>
</html>