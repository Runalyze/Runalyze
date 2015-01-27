<?php
/**
 * This file contains class::Heartrate
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Heartrate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class Heartrate extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'heartrate';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\Heartrate($context)
		);
	}
}