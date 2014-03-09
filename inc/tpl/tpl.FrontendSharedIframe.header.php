<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">

	<base href="<?php echo System::getFullDomain(); ?>" >

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" >

	<title><?php echo $this->getPageTitle(); ?> - Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="shared-iframe" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flot-loader"></div>

<div id="headline">
	<span class="tab singleTab">
		&Ouml;ffentliche Trainingsansicht
		<?php if (strlen($User['username']) > 1): ?>
			von <strong><?php echo $User['username']; ?></strong>
		<?php endif; ?>
	</span>
</div>