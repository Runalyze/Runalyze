<?php
/**
 * This file contains class::Temperature
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;

/**
 * Plot for: Temperature
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class DistanceSeries extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgba(255,255,255,0)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::DISTANCE);
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Distance');
		$this->Color = self::COLOR;

		$this->UnitString = Configuration::General()->distanceUnit()->value();
		$this->UnitDecimals = 2;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = false;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Average
	 * @param int $decimals [optional]
	 * @return int
	 */
	protected function avg($decimals = 1) {
		return parent::avg($decimals);
	}
}
