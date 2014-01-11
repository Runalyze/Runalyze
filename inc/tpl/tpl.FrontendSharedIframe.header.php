<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type" />

	<base href="<?php echo System::getFullDomain(); ?>" />

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title><?php echo $this->getPageTitle(); ?> - Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="sharedIframe" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flotLoader"></div>

<div id="headline">
	<span class="tab singleTab">
		&Ouml;ffentliche Trainingsansicht
		<?php if (strlen($User['username']) > 1): ?>
			von <strong><?php echo $User['username']; ?></strong>
		<?php endif; ?>
	</span>
</div>