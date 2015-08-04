<?php
/**
 * This file contains class::Stroke
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: stroke
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\View\Activity\Plot
 */
class Stroke extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'stroke';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\Stroke($context)
		);
	}
}