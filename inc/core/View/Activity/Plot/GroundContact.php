<?php
/**
 * This file contains class::GroundContact
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Ground contact
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class GroundContact extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'groundcontact';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addSeries(
			new Series\GroundContact($context)
		);
	}
}
