<?php
/**
 * This file contains class::Power
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Power
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class Power extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'power';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addMultipleSeries(array(
			new Series\Elevation($context),
			new Series\Power($context),
			new Series\TimeSeries($context),
			new Series\DistanceSeries($context)
		));
	}
}
