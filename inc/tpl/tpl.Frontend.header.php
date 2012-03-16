<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<?php foreach ($this->CSS_FILES as $file): ?>
		<link rel="stylesheet"  type="text/css"href="<?php echo $file; ?>" />
	<?php endforeach; ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php foreach ($this->JS_FILES as $file): ?>
		<script type="text/javascript" src="<?php echo $file; ?>"></script>
	<?php endforeach; ?>

	<?php if (class_exists('Plot')): ?><?php foreach (Plot::getNeededJSFilesAsArray() as $file): ?>
		<script type="text/javascript" src="<?php echo $file; ?>"></script>
	<?php endforeach; ?><?php endif; ?>

	<!--[if IE]><style type="text/css">table { border-collapse: collapse; }</style><![endif]-->
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="lib/flot/excanvas.min.js"></script><![endif]-->
</head>

<body id="home" class="toolbar-<?php echo CONF_DESIGN_TOOLBAR_POSITION; ?>" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flotLoader"></div>

<div id="copy" class="<?php echo CONF_DESIGN_TOOLBAR_POSITION; ?>">
	<a class="tab singleTab" href="http://www.runalyze.de/" title="Runalyze" target="_blank">&copy; Runalyze v<?php echo RUNALYZE_VERSION; ?></a>

	<span class="left b">
		<?php echo Ajax::window('<a class="tab" id="confEdit" href="'.Config::$CONFIG_URL.'">Konfiguration</a>'); ?>
		<?php echo Ajax::window('<a class="tab" id="confTool" href="'.PluginTool::$DISPLAY_URL.'">Tools</a>'); ?>
		<?php echo Ajax::window('<a class="tab" id="confHelp" href="'.Frontend::$HELP_URL.'">Hilfe</a>'); ?>
	</span>
</div>