<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=yes">

	<base href="<?php echo System::getFullDomain(); ?>">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

	<title><?php echo $title; ?></title>

	<?php echo System::getCodeForLocalJSFiles(); ?>

</head>

<body id="installer">

<div id="headline">
	<a class="tab logo" href="http://www.runalyze.com/" target="_blank">Runalyze</a>
</div>

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>

<div id="ajax-outer">
<div id="ajax" class="panel<?php if (defined('ADMIN_WINDOW')) echo ' big-window'; ?>" style="display:block;">
	<div class="panel-heading">
		<h1><?php echo $title; ?></h1>
	</div>
	<div class="panel-content">
		<div class="installer-window-container">
