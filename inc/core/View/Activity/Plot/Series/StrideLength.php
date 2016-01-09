<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Plot for: stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class StrideLength extends ActivityPointSeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(41,128,185)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initData($context->trackdata());
		$this->manipulateData();
		$this->setManualAverage($context->activity()->strideLength());
	}

	/**
	 * Init data
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param string|bool $key
	 * @param bool $fillGaps try to fill gaps (zero values)
	 */
	protected function initData(Trackdata $trackdata, $key = false, $fillGaps = false) {
		$Collector = new DataCollectorForStrideLength($trackdata);

		$this->Data = $Collector->data();
		$this->XAxis = $Collector->xAxis();
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Stride length');
		$this->Color = self::COLOR;

		$this->UnitString = Configuration::General()->distanceUnitSystem()->strideLengthUnit();
		$this->UnitDecimals = 2;

		$this->TickSize = 1;
		$this->TickDecimals = 1;

		$this->ShowAverage = true;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Manipulate data
	 */
	protected function manipulateData() {
		$UnitSystem = Configuration::General()->distanceUnitSystem();

		if ($UnitSystem->isImperial()) {
			$factor = DistanceUnitSystem::FEET_MULTIPLIER / 1000 / 100;
		} else {
			$factor = 1 / 100;
		}

		$this->Data = array_map(function ($value) use ($factor) {
			return $value * $factor;
		}, $this->Data);
	}
}