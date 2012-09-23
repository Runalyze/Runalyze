<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title><?php echo $title; ?></title>

	<?php echo System::getCodeForAllJSFiles(); ?>

</head>

<body id="installer" class="toolbar-top">

<div id="copy" class="top">
	<a class="tab logo" href="http://www.runalyze.de/" title="Runalyze" target="_blank">Runalyze v<?php echo RUNALYZE_VERSION; ?></a>
	<a class="tab right" href="login.php" title="Please login"><i class="toolbar-icon-user"></i> Please login</a>

	<!--<span class="left b">
		<?php echo Ajax::window('<a class="tab" href="'.Config::$CONFIG_URL.'"><i class="toolbar-icon-config"></i> Konfiguration</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.PluginTool::$DISPLAY_URL.'"><i class="toolbar-icon-tools"></i> Tools</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.Frontend::$HELP_URL.'"><i class="toolbar-icon-help"></i> Hilfe</a>'); ?>
	</span>-->
</div>

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<h1><?php echo $title; ?></h1>

	<div style="padding:0 70px;">