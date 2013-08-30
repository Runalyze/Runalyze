<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
	<meta name="viewport" content="width=320, initial-scale=1" />

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title><?php echo $title; ?></title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>

</head>

<body id="installer" class="toolbar-top">

<div id="copy" class="top">
	<a class="tab logo" href="http://www.runalyze.de/" title="Runalyze" target="_blank">Runalyze <?php if (defined('RUNALYZE_VERSION')) echo 'v'.RUNALYZE_VERSION; ?></a>
	<a class="tab right" href="login.php" title="Please login"><i class="toolbar-icon-user"></i> Please login</a>
</div>

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel<?php if (defined('ADMIN_WINDOW')) echo ' bigWin'; ?>" style="display:block;">
	<h1><?php echo $title; ?></h1>

	<div class="installer-window-container">