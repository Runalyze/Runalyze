<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=yes">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
        <?php foreach (Language::availableLanguages() as $key => $lang_arr) { ?>
        <link rel="alternate" href="<?php echo System::getFullDomain(true)."index.php?lang=".$key; ?>" hreflang="<?php echo $key; ?>" />
        <?php } ?>
	<title><?php echo $title; ?></title>

	<?php echo System::getCodeForLocalJSFiles(); ?>

</head>

<body id="installer">

<div id="headline">
	<a class="tab logo" href="http://www.runalyze.de/" target="_blank">Runalyze</a>
	<a class="tab right" href="login.php"><i class="fa fa-fw fa-sign-in"></i> <?php _e('Please login'); ?></a>
</div>

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>

<div id="ajax-outer">
<div id="ajax" class="panel<?php if (defined('ADMIN_WINDOW')) echo ' big-window'; ?>" style="display:block;">
	<div class="panel-heading">
		<div class="panel-menu">
                    <ul>
                    <li class="with-submenu">
                        <span class="link">Language</span>
			<ul class="submenu">
			<?php
				foreach (Language::availableLanguages() as $key => $lang_arr) {
					$liClass = (Language::getCurrentLanguage() == $key) ? ' class="triggered"' : '';
					echo '<li'.$liClass.'><a href="?lang='.$key.'">'.$lang_arr[0].'</a></li>';
				}
			?>
			</ul>
                    </li>
                    </ul>
		</div>
		<h1><?php echo $title; ?></h1>
	</div>
	<div class="panel-content">
		<div class="installer-window-container">
