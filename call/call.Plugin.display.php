<?php
/**
 * File for displaying plugins.
 * Call:   call.Plugin.display.php?id= [&sport= &jahr= &dat= ]
 */
$Factory = new PluginFactory();

try {
	$Plugin = $Factory->newInstanceFor( filter_input(INPUT_GET, 'id') );
} catch (Exception $E) {
	$Plugin = null;

	echo HTML::error( __('The plugin could not be found.') );
}

if ($Plugin !== null) {
	if ($Plugin instanceof PluginPanel) {
		$Plugin->setSurroundingDivVisible(false);
	}

	$Plugin->display();
}