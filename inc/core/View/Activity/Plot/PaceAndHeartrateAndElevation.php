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
		/** @var \Runalyze\View\Plot\Series[] $allSeries */
		$allSeries = [
			new Series\Elevation($context),
			new Series\Gradient($context),
			new Series\Pace($context),
			new Series\Heartrate($context),
			new Series\TimeSeries($context),
			new Series\DistanceSeries($context)
		];

		$this->addMultipleSeries($allSeries);

		$allSeries[1]->hideIn($this);
		$allSeries[4]->hideIn($this);
		$allSeries[5]->hideIn($this);
	}
}
