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
	 * @var int
	 */
	protected $WIDTH = 600;

	/**
	 * @var int
	 */
	protected $HEIGHT = 190;

	/**
	 * Use standard x-axis?
	 * @var boolean
	 */
	protected $useStandardXaxis = false;

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
			'label' => __('Poincarè plot'),
			'color' => 'rgb(0,0,0)',
			'data' => $Collector->data()
		);

		// TODO: ensure symmetric x-/y-axis
		// TODO: provide better tooltips

		$this->Plot->addYUnit(0, 'ms');
		$this->Plot->setXUnit('ms');
		$this->Plot->smoothing(false);
		$this->Plot->showPoints();

		$this->Plot->PlotOptions['allowSelection'] = false;
	}
}