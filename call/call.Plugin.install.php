<?php
/**
 * File for installing plugins.
 * Call:   call.Plugin.install.php?key=
 */
require '../inc/class.Frontend.php';

$Pluginkey = filter_input(INPUT_GET, 'key');

$Frontend = new Frontend();
$Installer = new PluginInstaller($Pluginkey);

echo '<h1>'.__('Install').' '.$Pluginkey.'</h1>';

if ($Installer->install()) {
	$Factory = new PluginFactory();
	$Plugin = $Factory->newInstance($Pluginkey);

	echo HTML::okay( __('The plugin has been successfully installed.') );

	echo '<ul class="blocklist">';
	echo '<li>';
	echo $Plugin->getConfigLink(Icon::$CONF.' '.__('Configuration'));
	echo '</li>';
	echo '</ul>';

	Ajax::setReloadFlag(Ajax::$RELOAD_ALL);
	echo Ajax::getReloadCommand();
} else {
	echo HTML::error( __('There was a problem, the plugin could not be installed.') );
}

echo '<ul class="blocklist">';
echo '<li>';
echo Ajax::window('<a href="'.ConfigTabPlugins::getExternalUrl().'">'.Icon::$TABLE.' '.__('back to list').'</a>');
echo '</li>';
echo '</ul>';