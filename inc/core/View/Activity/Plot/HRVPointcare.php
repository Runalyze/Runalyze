<?php
/**
 * This file contains class::HRVPointcare
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\Calculation\HRV\PointcareCollector;
use Runalyze\View\Activity;

/**
 * Pointcare plot for hrv data
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class HRVPointcare extends ActivityPlot {
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
		$this->key = 'hrvpointcare';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$Collector = new PointcareCollector($context->hrv());
		$this->Data = $Collector->data();

		if (empty($this->Data)) {
			return;
		}

		$this->Plot->Data[] = array(
			'label' => __('Pointcare plot'),
			'color' => 'rgb(0,0,0)',
			'data' => $Collector->data()
		);

		// TODO: ensure symmetric x-/y-axis
		// TODO: provide better tooltips

		$this->Plot->addYUnit(0, 'ms');
		$this->Plot->setXUnit('ms');
		$this->Plot->smoothing(false);
		$this->Plot->showPoints();
	}
}