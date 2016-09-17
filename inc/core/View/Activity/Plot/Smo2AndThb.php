<?php
/**
 * This file contains class::Power
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;
use Runalyze\View\Activity\Plot\Series;

/**
 * Plot for: Power
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class Smo2AndThb extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'smo2andthb';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
        $this->addSeries(new Series\Smo2($context), 1);
        $this->addSeries(new Series\Smo2($context, 1), 1, false);
        $this->addSeries(new Series\Thb($context), 2);
        $this->addSeries(new Series\Thb($context, 1), 2, false);
    }
}
