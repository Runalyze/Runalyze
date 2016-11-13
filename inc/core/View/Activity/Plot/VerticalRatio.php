<?php
/**
 * This file contains class::VerticalRatio
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Vertical ratio
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\View\Activity\Plot
 */
class VerticalRatio extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'verticalRatio';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\VerticalRatio($context)
		);
	}
}
