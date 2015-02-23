<?php
/**
 * File for uninstalling plugins.
 * Call:   call.Plugin.uninstall.php?key=
 */
require '../inc/class.Frontend.php';

$Pluginkey = filter_input(INPUT_GET, 'key');

$Frontend = new Frontend();
$Installer = new PluginInstaller($Pluginkey);

echo '<h1>'.__('Uninstall').' '.$Pluginkey.'</h1>';

if ($Installer->uninstall()) {
	echo HTML::okay( __('The plugin has been uninstalled.') );

	PluginFactory::clearCache();
	Ajax::setReloadFlag(Ajax::$RELOAD_ALL);
	echo Ajax::getReloadCommand();
} else {
	echo HTML::error( __('There was a problem, the plugin could not be uninstalled.') );
}

echo '<ul class="blocklist">';
echo '<li>';
echo Ajax::window('<a href="'.ConfigTabPlugins::getExternalUrl().'">'.Icon::$TABLE.' '.__('back to list').'</a>');
echo '</li>';
echo '</ul>';