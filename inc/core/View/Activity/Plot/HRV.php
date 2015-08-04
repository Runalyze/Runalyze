<?php
/**
 * This file contains class::HRV
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for hrv data
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class HRV extends ActivityPlot {
	/**
	 * Use standard x-axis?
	 * @var boolean
	 */
	protected $useStandardXaxis = false;

	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'hrv';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\HRV($context)
		);
	}
}