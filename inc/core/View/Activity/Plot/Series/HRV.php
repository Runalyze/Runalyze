<?php
/**
 * This file contains class::HRV
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Calculation\HRV\Calculator;
use Runalyze\Model;
use Runalyze\View\Activity;

/**
 * Plot for: heart rate variability
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class HRV extends ActivityPointSeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,0,0)';

	/**
	 * @var int
	 */
	protected $PointSize = 1;

	/**
	 * @var array
	 */
	protected $XAxisData = array();

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->determineXAxis($context);
		$this->initHRVData($context->hrv());
	}

	/**
	 * Init data
	 * @param \Runalyze\Model\HRV\Entity $hrv
	 */
	protected function initHRVData(Model\HRV\Entity $hrv) {
		if (count($this->XAxisData) == $hrv->num()) {
			$this->XAxis = DataCollector::X_AXIS_TIME;
			$this->Data = array_combine($this->XAxisData, $hrv->data());
		} else {
			$this->XAxis = DataCollector::X_AXIS_INDEX;
			$this->Data = $hrv->data();
		}
	}

	/**
	 * Determine correct x axis
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function determineXAxis(Activity\Context $context) {
		if ($context->hasTrackdata()) {
			$totalTime = 1000 * $context->trackdata()->totalTime();

			if ($context->hrv()->num() == $context->trackdata()->num()) {
				$this->XAxisData = array_map(function($value) {
					return 1000 * $value;
				}, $context->trackdata()->time());
			} else {
				$correctTime = abs(array_sum($context->hrv()->data()) - $totalTime) > 0.005 * $totalTime;
				$time = 0;

				foreach ($context->hrv()->data() as $ms) {
					$time += $correctTime && $ms < 1000 ? 1000 : $ms;
					$this->XAxisData[] = $time;
				}

				if ($correctTime && abs($time - $totalTime) > 0.01 * $totalTime) {
					$this->XAxisData = array();
				}
			}
		}
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('R-R interval');
		$this->Color = self::COLOR;

		$this->UnitString = 'ms';
		$this->UnitDecimals = 0;

		$this->TickSize = 1;
		$this->TickDecimals = 1;

		$this->ShowAverage = false;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
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