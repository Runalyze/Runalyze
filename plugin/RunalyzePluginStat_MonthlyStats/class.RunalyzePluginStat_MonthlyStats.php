<?php
/**
 * This file contains the class of the RunalyzePluginStat "MonthlyStats".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_MonthlyStats';

use Runalyze\Configuration;

/**
 * Class: RunalyzePluginStat_MonthlyStats
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_MonthlyStats extends PluginStat {
	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Monthly Stats');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return sprintf(__('How many %s/hours did you do per month'), Configuration::General()->distanceUnitSystem()->distanceUnit());
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		// deprecated, see Runalyze\Bundle\CoreBundle\Controller\PluginController::getResponseForMonthlyStats
	}
}
