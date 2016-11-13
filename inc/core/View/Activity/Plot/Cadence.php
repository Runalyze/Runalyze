<?php
/**
 * This file contains class::Cadence
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Cadence
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class Cadence extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'cadence';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\Cadence($context)
		);
	}
}
