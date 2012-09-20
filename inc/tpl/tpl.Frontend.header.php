<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php echo System::getCodeForAllJSFiles(); ?>

	<!--[if IE]><style type="text/css">table { border-collapse: collapse; }</style><![endif]-->
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="lib/flot/excanvas.min.js"></script><![endif]-->
</head>

<body id="home" class="toolbar-<?php echo CONF_DESIGN_TOOLBAR_POSITION; ?>" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flotLoader"></div>

<div id="copy" class="<?php echo CONF_DESIGN_TOOLBAR_POSITION; ?>">
	<a class="tab logo" href="http://www.runalyze.de/" title="Runalyze" target="_blank">Runalyze v<?php echo RUNALYZE_VERSION; ?></a>
	<?php if (SessionAccountHandler::isLoggedIn()): ?><a class="tab right" href="login.php?out" title="Ausloggen"><i class="toolbar-icon-user"></i> Logout</a><?php endif; ?>

	<span class="left b">
		<?php echo Ajax::window('<a class="tab" href="'.Config::$CONFIG_URL.'"><i class="toolbar-icon-config"></i> Konfiguration</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.PluginTool::$DISPLAY_URL.'"><i class="toolbar-icon-tools"></i> Tools</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.Frontend::$HELP_URL.'"><i class="toolbar-icon-help"></i> Hilfe</a>'); ?>
	</span>
</div>