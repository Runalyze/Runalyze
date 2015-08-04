<?php
/**
 * This file contains class::swolf
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Swolf
 * 
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\View\Activity\Plot
 */
class Swolfcycles extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'swolfcycles';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\Swolfcycles($context)
		);
	}
}