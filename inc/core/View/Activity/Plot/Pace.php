<?php
/**
 * This file contains class::Pace
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Pace
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class Pace extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'pace';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$CumulativeSeries = new Series\PaceCumulativeAverage($context);

		$this->addSeries(new Series\Pace($context));
		$this->addSeries($CumulativeSeries, 1, false);

		$this->Plot->Options['legend']['hidden'] = [$CumulativeSeries->label()];
	}
}