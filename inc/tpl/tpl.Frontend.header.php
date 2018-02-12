<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=yes">

	<base href="<?php echo System::getFullDomain(); ?>">

    <script data-pace-options='{"ajax": {"ignoreURLs": ["_internal/notifications", "_wdt/"]}, "document": true }' src="vendor/pace/pace.min.js"></script>

    <link rel="stylesheet" href="assets/css/runalyze-style.css?v=<?php echo RUNALYZE_VERSION; ?>">

	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="manifest" href="assets/appmanifest.json">
        <?php foreach (\Runalyze\Language::availableLanguages() as $key => $lang_arr) { ?>
        <link rel="alternate" href="<?php echo System::getFullDomain(true)."?lang=".$key; ?>" hreflang="<?php echo $key; ?>" />
        <?php } ?>
	<title>RUNALYZE</title>

    <script>document.addEventListener("touchstart", function(){}, true);</script>
    <script src="assets/js/scripts.min.js?v=<?php echo RUNALYZE_VERSION; ?>"></script>
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
                    <a target="_blank" href="<?php echo $this->get('router')->generate('shared-athlete', ['username' => $username]); ?>"><i class="fa fa-fw fa-id-card-o"></i>&nbsp;<?php _e('Public athlete page'); ?></a>
                    <?php else: ?>
                    <span class="no-link cursor-not-allowed unimportant" title="<?php _e('Your public athlete page is deactivated.') ?>"><i class="fa fa-fw fa-id-card-o"></i>&nbsp;<?php _e('Public athlete page'); ?></span>
                    <?php endif; ?>
                </li>
                <li class="separator"></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-account'); ?>"><i class="fa fa-fw fa-cogs"></i>&nbsp;<?php _e('Account settings'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-privacy'); ?>"><i class="fa fa-fw fa-unlock-alt"></i>&nbsp;<?php _e('Privacy settings'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('logout'); ?>"><i class="fa fa-fw fa-sign-out"></i>&nbsp;<?php _e('Logout'); ?></a></li>
            </ul>
        </div>

        <div class="headline-menu right">
            <div class="submenu-label only-icon">
                <i class="fa fa-fw fa-lg fa-book"></i>
            </div>
            <ul class="submenu right-oriented">
                <li><a class="window" href="<?php echo $this->get('router')->generate('glossary-index'); ?>"><i class="fa fa-fw fa-book"></i>&nbsp;<?php _e('Glossary'); ?></a></li>
                <li><a href="https://help.runalyze.com"><i class="fa fa-fw fa-book"></i>&nbsp;<?php _e('Documentation'); ?></a></li>
                <li class="separator"></li>
                <?php if (!empty($this->getParameter('feedback_mail'))) { ?>
                <li><a class="window" href="<?php echo $this->get('router')->generate('feedback'); ?>"><i class="fa fa-fw fa-cogs"></i>&nbsp;<?php _e('Feedback'); ?></a></li>
                <?php } ?>
                <li><a href="https://forum.runalyze.com"><i class="fa fa-fw fa-comments"></i>&nbsp;<?php _e('Forum'); ?></a></li>
                <li><a href="https://blog.runalyze.com"><i class="fa fa-fw fa-rss"></i>&nbsp;<?php _e('Blog'); ?></a></li>
                <li class="separator"></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('help'); ?>"><i class="fa fa-fw fa-building"></i>&nbsp;<?php _e('About us'); ?></a></li>
            </ul>
        </div>

        <div id="new-notifications-menu" class="headline-menu right">
            <div class="submenu-label only-icon">
                <i class="fa fa-fw fa-envelope"><span class="hide new-notifications-indicator"></span></i><i class="fa fa-fw fa-caret-down"></i>
            </div>
            <ul class="submenu right-oriented">
                <li><a class="window" data-size="small" href="<?php echo $this->get('router')->generate('notifications-list'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Show all notifications'); ?></a></li>
                <li class="separator"></li>
                <li class="no-notifications-messages"><em class="no-link"><?php _e('No new notifications'); ?></em></li>
                <li id="tpl-notification-message-with-internal-link" class="hide notification-message is-new"><a class="internal" href=""><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<span></span></a></li>
                <li id="tpl-notification-message-with-external-link" class="hide notification-message is-new"><a href="" target="_blank"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<span></span></a></li>
                <li id="tpl-notification-message-without-link" class="hide notification-message is-new"><span class="no-link"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<span></span></span></li>
             </ul>

            <script>Runalyze.Notifications.setLastRequestTime(1);</script>
        </div>

        <div class="headline-menu right">
            <div class="submenu-label only-icon">
                <i class="fa fa-fw fa-plus"></i><i class="fa fa-fw fa-caret-down"></i>
            </div>
            <ul class="submenu right-oriented">
                <li><a class="window" href="<?php echo $this->get('router')->generate('activity-upload'); ?>" data-size="small"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Activity upload'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('activity-new'); ?>" data-size="small"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Manual activity'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('equipment-overview'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('New equipment'); ?></a></li>
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
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-dataset'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Dataset'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('settings-sports'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Sports'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('equipment-overview'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Equipment'); ?></a></li>
                <li><a class="window" data-size="small" href="<?php echo $this->get('router')->generate('settings-tags'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Activity tags'); ?></a></li>
            </ul>
        </div>

        <div class="headline-menu left">
            <div class="submenu-label b">
                <a class="window" href="<?php echo $this->get('router')->generate('tools'); ?>"><i class="fa fa-fw fa-lg fa-dashboard"></i>&nbsp;<?php _e('Tools'); ?></a>
            </div>
            <ul class="submenu">
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-trend-analysis'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Trend analysis'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-anova'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('ANOVA'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-vo2max-analysis'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Analyze your VO2max'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools-tables'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Running tables'); ?></a></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('poster'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('Poster generator'); ?></a></li>
                <li class="separator"></li>
                <li><a class="window" href="<?php echo $this->get('router')->generate('tools'); ?>"><i class="fa fa-fw fa-chevron-right small"></i>&nbsp;<?php _e('More'); ?></a></li>
            </ul>
        </div>

        <a class="window tab left b" data-size="big" href="<?php echo $this->get('router')->generate('my-search') ?>"><i class="fa fa-fw fa-lg fa-search"></i>&nbsp;<?php _e('Search'); ?></a>
	<?php endif; ?>
</div>
