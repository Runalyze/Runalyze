<?php
/**
 * This file contains class::HRVPoincare
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Calculation\HRV\PoincareCollector;
use Runalyze\View\Activity;

/**
 * Poincare plot for hrv data
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class HRVPoincare extends ActivityPlot {
	/**
	 * Use standard x-axis?
	 * @var boolean
	 */
	protected $useStandardXaxis = false;

	/**
	 * @var int
	 */
	protected $PointSize = 1;

	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'hrvpoincare';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$Collector = new PoincareCollector($context->hrv());
		$this->Data = $Collector->data();

		if (empty($this->Data)) {
			return;
		}

		$this->Plot->Data[] = array(
			'label' => __('R-R interval').' n+1',
			'color' => 'rgb(0,0,0)',
			'data' => $Collector->data()
		);

		// TODO: ensure symmetric x-/y-axis

		$this->Plot->addYUnit(1, 'ms');
		$this->Plot->setXUnit('ms');
		$this->Plot->setXLabel(__('R-R interval').' n');
		$this->Plot->smoothing(false);
		$this->Plot->showPoints();

		$this->Plot->PlotOptions['allowSelection'] = false;
	}
}
