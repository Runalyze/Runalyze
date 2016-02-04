<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# fitness: http://ogp.me/ns/fitness#">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=yes">

	<?php if (null !== $this->ActivityContext) { $Meta = new HTMLMetaForFacebook($this->ActivityContext); $Meta->display(); } ?>

	<base href="<?php echo System::getFullDomain(); ?>">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

	<title><?php echo $this->getPageTitle(); ?> - RUNALYZE</title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="shared">

<div id="flot-loader"></div>

<div id="headline">
	<a class="tab logo b" href="http://www.runalyze.de/" title="Runalyze" target="_blank">RUNALYZE</a>

	<span class="tab right">
		<?php
		if (isset($User) && isset($User['username']) && strlen($User['username']) > 1) {
			printf( __('Public training view of <strong>%s</strong>'), $User['username']);
		} else {
			_e('Public training view');
		}
		?>
	</span>
</div>
