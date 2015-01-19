<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

	<title><?php echo $title; ?></title>

	<?php echo System::getCodeForLocalJSFiles(); ?>

</head>

<body id="installer">

<div id="headline">
	<a class="tab logo" href="http://www.runalyze.de/" target="_blank">Runalyze <?php if (defined('RUNALYZE_VERSION')) echo 'v'.RUNALYZE_VERSION; ?></a>
	<a class="tab right" href="login.php"><i class="fa fa-fw fa-sign-in"></i> <?php _e('Please login'); ?></a>
</div>

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel<?php if (defined('ADMIN_WINDOW')) echo ' big-window'; ?>" style="display:block;">
	<div class="panel-heading">
		<div class="panel-menu">
			<ul>
			<?php
				foreach (Language::availableLanguages() as $key => $lang_arr) {
					$liClass = (Language::getCurrentLanguage() == $key) ? ' class="triggered"' : '';
					echo '<li'.$liClass.'><a href="?lang='.$key.'">'.$lang_arr[0].'</a></li>';
				}
			?>
			</ul>
		</div>
		<h1><?php echo $title; ?></h1>
	</div>
	<div class="panel-content">
		<div class="installer-window-container">