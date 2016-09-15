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
class Smo2AndThb extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'smo2andthb';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addMultipleSeries(array(
			new Series\Smo2_0($context),
            new Series\Smo2_1($context),
            new Series\Thb_0($context),
            new Series\Thb_1($context),
            new Series\TimeSeries($context),
			new Series\DistanceSeries($context),
		));
	}
}
