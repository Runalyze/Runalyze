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
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->name = __('Cacheclean');
		$this->description = __('Empty the cache for activities.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if (isset($_GET['delete']))
			System::clearCache();

		$numData = DB::getInstance()->query('SELECT COUNT(*) as num FROM '.PREFIX.'training WHERE gps_cache_object!="" LIMIT 1')->fetch();
		$num     = $numData['num'];

		$Fieldset = new FormularFieldset( __('Empty the cache') );
		$Fieldset->addInfo(
				self::getActionLink('<strong>'.__('Empty the cache').'</strong>', 'delete=true').'<br>'.
				__('Due to performance reasons, some data (laps, zones, plots, map, ...) are cached.'.
					'If you have problems with your activity view, try to empty the cache.') );
		$Fieldset->addFileBlock( sprintf( __('The cache holds %s activities.'), $num ) );

		$Formular = new Formular();
		$Formular->setId('cacheclean-form');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}
}