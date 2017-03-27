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

	<?php echo System::getCodeForLocalJSFiles(); ?>
</head>

<body id="home" style="background-image:url(<?php echo \Runalyze\Configuration::Design()->backgroundImage(); ?>);">

<div id="flot-loader"></div>

<div id="headline">
	<span id="menu-link" onclick="$('#headline').toggleClass('menu-expanded');"><i class="fa fa-fw fa-bars"></i></span>
	<a class="tab logo" href="<?php echo System::getFullDomain(); ?>" title="Runalyze">Runalyze</a>

	<?php if ($this instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface && $this->get('security.authorization_checker')->isGranted('ROLE_USER')): ?>
        <?php $username = $this->get('security.token_storage')->getToken()->getUser()->getUsername(); ?>
        <div class="headline-menu right">
            <div class="submenu-label">
                <?php echo $username ?>&nbsp;<i class="fa fa-fw fa-lg fa-user"></i>
            </div>
            <ul class="submenu right-oriented">
                <li>
                    <?php if (\Runalyze\Configuration::Privacy()->listIsPublic()): ?>
                    <a href="<?php echo $this->get('router')->generate('shared-athlete', ['username' => $username]); ?>"><i class="fa fa-fw fa-id-card-o"></i>&nbsp;<?php _e('Public athlete page'); ?></a>
                    <?php else: ?>
                    <span class="no-link cursor-not-allowed unimportant" title="<?php _e('Your public athlete page is deactivated.') ?>"><i class="fa fa-fw fa-id-card-o"></i>&nbsp;<?php _e('Public athlete page'); ?></span>
                    <?php endif; ?>
                </li>
                <li class="separator"></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-account'); ?>"><i class="fa fa-fw fa-cogs"></i>&nbsp;<?php _e('Account settings'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('logout'); ?>"><i class="fa fa-fw fa-sign-out"></i>&nbsp;<?php _e('Logout'); ?></a></li>
            </ul>
        </div>

        <div class="headline-menu right">
            <div class="submenu-label only-icon">
                <i class="fa fa-fw fa-plus"></i><i class="fa fa-fw fa-caret-down"></i>
            </div>
            <ul class="submenu">
                <li><a class="window" href="<?php echo $this->get('router')->generate('activity-add'); ?>?upload" data-size="small"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Activity upload'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('activity-add'); ?>?date" data-size="small"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Manual activity'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings'); ?>?key=config_tab_equipment"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('New equipment'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('body-values-add'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('New body values'); ?></a></li>
            </ul>
        </div>

        <div class="headline-menu left">
            <div class="submenu-label b">
                <i class="fa fa-fw fa-lg fa-cog"></i>&nbsp;<?php _e('Configuration'); ?>
            </div>
            <ul class="submenu">
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings'); ?>?key=config_tab_general"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('General settings'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings'); ?>?key=config_tab_plugins"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Plugins'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings'); ?>?key=config_tab_dataset"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Dataset'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-sports'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Sport types (new)'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings'); ?>?key=config_tab_equipment"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Equipment'); ?></a></li>
            </ul>
        </div>

        <div class="headline-menu left">
            <div class="submenu-label b">
                <a class="window" href="<?php echo $this->get('router')->generate('tools'); ?>"><i class="fa fa-fw fa-lg fa-dashboard"></i>&nbsp;<?php _e('Tools'); ?></a>
            </div>
            <ul class="submenu">
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-anova'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('ANOVA'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-vo2max-analysis'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Analyze your VO2max'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-tables'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Running tables'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('poster'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Poster generator'); ?></a></li>
            </ul>
        </div>

        <a class="window tab left b" href="<?php echo $this->get('router')->generate('help') ?>"><i class="fa fa-fw fa-lg fa-question-circle"></i>&nbsp;<?php _e('Help'); ?></a>
	<?php endif; ?>
</div>
