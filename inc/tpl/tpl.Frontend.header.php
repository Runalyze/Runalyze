<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<base href="<?php echo System::getFullDomain(); ?>">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

	<title>Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="home" style="background-image:url(<?php echo \Runalyze\Configuration::Design()->backgroundImage(); ?>);">

<div id="flot-loader"></div>

<div id="headline">
	<a class="tab logo" href="http://www.runalyze.de/" title="Runalyze" target="_blank">Runalyze v<?php echo RUNALYZE_VERSION; ?></a>
	<?php if (SessionAccountHandler::isLoggedIn()): ?><a class="tab right" href="login.php?out" title="<?php _e('Logout'); ?>"><i class="fa fa-fw fa-lg fa-sign-out"></i>&nbsp;<?php _e('Logout'); ?></a><?php endif; ?>

	<span class="left b">
		<?php echo Ajax::window('<a class="tab" href="'.ConfigTabs::$CONFIG_URL.'"><i class="fa fa-fw fa-lg fa-cog"></i>'.NBSP.__('Configuration').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.PluginTool::$DISPLAY_URL.'"><i class="fa fa-fw fa-lg fa-dashboard"></i>'.NBSP.__('Tools').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.Frontend::$HELP_URL.'"><i class="fa fa-fw fa-lg fa-question-circle"></i>'.NBSP.__('Help').'</a>'); ?>
	</span>
</div>