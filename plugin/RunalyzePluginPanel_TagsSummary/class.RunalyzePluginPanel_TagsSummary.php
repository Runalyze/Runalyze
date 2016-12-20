<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Tags Summary".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_TagsSummary';

use Runalyze\Model;

/**
 * Class: RunalyzePluginPanel_TagsSummary
 *
 * @author Felix Gertz
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_TagsSummary extends PluginPanel {
	/**
	 * Internal array with all tags from database
	 * @var array
	 */
	private $Tags = null;

	/**
	 * @var array
	 */
	protected $AllTypes = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('TagsSummary');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Summarises your tags for the selected sport.');
	}

}
