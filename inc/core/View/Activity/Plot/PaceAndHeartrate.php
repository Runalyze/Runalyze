<?php
/**
 * This file contains class::PaceAndHeartrate
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Pace and heartrate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class PaceAndHeartrate extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'pacehr';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addMultipleSeries(array(
			new Series\Pace($context),
			new Series\Heartrate($context)
		));
	}
}
