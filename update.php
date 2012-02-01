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

	<title>Update von Runalyze</title>

	<script type="text/javascript" src="jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="lib/jQuery.backgroundStretch.js"></script>
</head>

<body id="installer">

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<h1>Update von Runalyze</h1>

	<div style="padding:0 70px;">
<?php
require_once 'inc/class.Mysql.php';
require_once 'inc/class.Installer.php';
require_once 'inc/class.InstallerUpdate.php';

$Updater = new InstallerUpdate();
$Updater->display();
?>

	</div>
</div>

<div id="copy">

	<a class="right" id="copy" href="http://www.runalyze.de/" title="Runalyze" target="_blank">
		<strong>&copy; Runalyze</strong>
	</a>

</div>

</body>
</html>