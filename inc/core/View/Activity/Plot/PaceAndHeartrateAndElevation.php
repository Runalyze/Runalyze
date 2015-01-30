<?php
/**
 * This file contains class::PaceAndHeartrateAndElevation
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Pace and heartrate and elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class PaceAndHeartrateAndElevation extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'pacehrelevation';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(new Series\Elevation($context), 1, false);
		$this->addSeries(new Series\Pace($context), 2, false);
		$this->addSeries(new Series\Heartrate($context), 3, false);
		$this->addSeries(new Series\TimeSeries($context), 4, false);
	}
}