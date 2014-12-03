<?php
/**
 * This file contains the class of the RunalyzePluginTool "Cacheclean".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_Cacheclean';

/**
 * Class: RunalyzePluginTool_Cacheclean
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_Cacheclean extends PluginTool {
	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Cacheclean');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Empty the cache for activities.');
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if (isset($_GET['delete'])) {
			System::clearCache();
		}

		$Fieldset = new FormularFieldset( __('Empty the cache') );
		$Fieldset->addInfo( self::getActionLink('<strong>'.__('Empty the cache').'</strong>', 'delete=true') );

		$Formular = new Formular();
		$Formular->setId('cacheclean-form');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}
}