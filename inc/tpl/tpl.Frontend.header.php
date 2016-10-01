<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=yes">

	<base href="<?php echo System::getFullDomain(); ?>">

	<?php echo System::getCodeForAllCSSFiles(); ?>

	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="manifest" href="assets/appmanifest.json">
        <?php foreach (\Runalyze\Language::availableLanguages() as $key => $lang_arr) { ?>
        <link rel="alternate" href="<?php echo System::getFullDomain(true)."?lang=".$key; ?>" hreflang="<?php echo $key; ?>" />
        <?php } ?>
	<title>RUNALYZE</title>

	<?php echo System::getCodeForExternalJSFiles(); ?>
	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="home" style="background-image:url(<?php echo \Runalyze\Configuration::Design()->backgroundImage(); ?>);">

<div id="flot-loader"></div>

<div id="headline">
	<span id="menu-link" onclick="$('#headline').toggleClass('menu-expanded');"><i class="fa fa-fw fa-bars"></i></span>
	<a class="tab logo" href="<?php echo System::getFullDomain(); ?>" title="Runalyze">Runalyze</a>

	<?php if ($this instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface): ?>
	<?php if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')): ?>
		<a class="tab right" href="<?php echo $this->get('router')->generate('logout'); ?>" title="<?php _e('Logout'); ?>"><i class="fa fa-fw fa-lg fa-sign-out"></i>&nbsp;<?php _e('Logout'); ?></a>
		<?php echo Ajax::window('<a class="tab right b" href="'.$this->get('router')->generate('settings-account').'"><i class="fa fa-fw fa-lg fa-user"></i>'.NBSP.$this->get('security.token_storage')->getToken()->getUser()->getUsername().'</a>'); ?>
	<?php endif; ?>

	<span class="left b">
		<?php echo Ajax::window('<a class="tab" href="'.$this->get('router')->generate('settings').'"><i class="fa fa-fw fa-lg fa-cog"></i>'.NBSP.__('Configuration').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.$this->get('router')->generate('tools').'"><i class="fa fa-fw fa-lg fa-dashboard"></i>'.NBSP.__('Tools').'</a>'); ?>
		<?php echo Ajax::window('<a class="tab" href="'.$this->get('router')->generate('help').'"><i class="fa fa-fw fa-lg fa-question-circle"></i>'.NBSP.__('Help').'</a>'); ?>
	</span>
	<?php endif; ?>
</div>
