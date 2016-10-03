<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">

	<base href="<?php echo System::getFullDomain(); ?>" >

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" >
	<link rel="manifest" href="assets/appmanifest.json">

	<title><?php echo $this->getPageTitle(); ?> - Runalyze</title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="shared-iframe">

<div id="flot-loader"></div>

<div id="headline">
	<span class="tab singleTab">
		<?php
		if (isset($User) && isset($User['username']) && strlen($User['username']) > 1) {
			printf( __('Public training view of <strong>%s</strong>'), $User['username']);
		} else {
			_e('Public training view');
		}
		?>
	</span>
</div>
