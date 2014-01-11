<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<base href="<?php echo System::getFullDomain(); ?>" />

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="home" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flotLoader"></div>

<div id="headline">
	<a class="tab logo" href="http://www.runalyze.de/" title="Runalyze" target="_blank">Runalyze v<?php echo RUNALYZE_VERSION; ?></a>
	<?php if (SessionAccountHandler::isLoggedIn()): ?><a class="tab right" href="login.php?out" title="<?php __('Logout'); ?>"><i class="toolbar-icon-user"></i>&nbsp;<?php _e('Logout'); ?></a><?php endif; ?>

	<span class="left b">
		<?php echo Ajax::window('<a class="tab" href="'.ConfigTabs::$CONFIG_URL.'"><i class="toolbar-icon-config"></i>'.NBSP.__('Konfiguration').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.PluginTool::$DISPLAY_URL.'"><i class="toolbar-icon-tools"></i>'.NBSP.__('Tools').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.Frontend::$HELP_URL.'"><i class="toolbar-icon-help"></i>'.NBSP.__('Help').'</a>'); ?>
	</span>
</div>