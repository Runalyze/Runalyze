<?php
/**
 * This file contains class::Power
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

/**
 * Plot for: Power
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Power extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,136,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::POWER);
		$this->setManualAverage($context->activity()->power());
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Power');
		$this->Color = self::COLOR;

		$this->UnitString = 'W';
		$this->UnitDecimals = 0;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}
}