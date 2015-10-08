<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Calculation\StrideLength\Calculator;
use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;

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
	}

	/**
	 * Init data
	 * @param \Runalyze\Model\Trackdata $trackdata
	 * @param string $key
	 * @param boolean $fillGaps try to fill gaps (zero values)
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

		$this->UnitString = Configuration::General()->distanceUnitAsFeet();
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
		$this->Data = array_map(array($this, 'correctUnit'), $this->Data);
	}

	/**
	 * Change value by internal factor
	 * @param int $value
	 * @return float
	 */
	protected function correctUnit($value) {
	    $strideLength = new Distance(0.01*$value);
		return $strideLength->stringForDistanceFeet(false, false);
	}

	/**
	 * Average
	 * @param int $decimals [optional]
	 * @return int
	 */
	protected function avg($decimals = 2) {
		return parent::avg($decimals);
	}
}