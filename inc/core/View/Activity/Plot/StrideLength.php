<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class StrideLength extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'stridelength';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\StrideLength($context)
		);
	}
}