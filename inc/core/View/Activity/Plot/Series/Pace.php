<?php
/**
 * This file contains class::Pace
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Plot;
use Runalyze\Configuration;
use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\Parameter\Application\PaceAxisType;
use Runalyze\Parameter\Application\PaceUnit;
use Runalyze\View\Activity;


/**
 * Plot for: Pace
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Pace extends ActivitySeries {
	/**
	* How many outliers should be cutted away?
	* @var int
	*/
	private static $CUT_OUTLIER_PERCENTAGE = 10;

	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,0,136)';

	/**
	 * @var boolean
	 */
	protected $isRunning;

	/**
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $paceUnit;

	/**
	 * @var string
	 */
	protected $paceUnitEnum;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->paceUnit = $context->sport()->paceUnit();
		$this->paceUnitEnum = $context->sport()->paceUnitEnum();

		$this->isRunning = ($context->sport()->id() == Configuration::General()->runningSport());

		$this->initOptions();
		$this->initData($context->trackdata(), Trackdata::PACE);
		$this->manipulateData();
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Pace');
		$this->Color = self::COLOR;

		$pace = new \Runalyze\Activity\Pace(0, 1);
		$pace->setUnit($this->paceUnit);
		$this->UnitString = !$this->paceUnit->isTimeFormat() ? str_replace('&nbsp;', '', $pace->appendix()) : '';
		$this->UnitDecimals = 1;

		$this->TickSize = false;

		$this->ShowAverage = false;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Manipulate data
	 */
	protected function manipulateData() {
		if ($this->paceUnit->isTimeFormat()) {
			$factor = $this->paceUnit->factorForUnit();

			$this->Data = array_map(function($v) use ($factor){
				return ($v == 0) ? 3600*1000 : 1000*round($v*$factor);
			}, $this->Data);
		} else {
			$dividend = $this->paceUnit->dividendForUnit();

			$this->Data = array_map(function($v) use ($dividend){
				return ($v == 0) ? 0 : round($dividend/$v, 1);
			}, $this->Data);
		}
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(\Plot &$Plot, $yAxis, $addAnnotations = true)
	{
		if (empty($this->Data)) {
			return;
		}

		parent::addTo($Plot, $yAxis, $addAnnotations);

		if (!$this->paceUnit->isTimeFormat()) return;

		$Plot->setYAxisTimeFormat('%M:%S', $yAxis);

		$min = min($this->Data);
		$max = max($this->Data);


		$setLimits = false;
		$autoscale = true;

		if (Configuration::ActivityView()->ignorePaceOutliers() && ($max - $min) > 2 * 60 * 1000) {
			$setLimits = true;
			$num = count($this->Data);
			$sorted = $this->Data;
			sort($sorted);
			$min = 10 * 1000 * floor($sorted[round((self::$CUT_OUTLIER_PERCENTAGE / 2 / 100) * $num)] / 10 / 1000);
			$max = 10 * 1000 * ceil($sorted[round((1 - self::$CUT_OUTLIER_PERCENTAGE / 2 / 100) * $num) - 1] / 10 / 1000);
		}

		if ($max > 50 * 60 * 1000) {
			$setLimits = true;
			$max = 50 * 60 * 1000;
		}

		if (Configuration::ActivityView()->paceAxisType()->valueAsString() == PaceAxisType::AS_SPEED) {
			$LimitMin = Configuration::ActivityView()->paceYaxisMinimum();
			if (!$LimitMin->automatic()) $min = $LimitMin->value() * 1000;
			$this->setYAxisForReversePace($Plot, $yAxis, $min);
		} else {
			$LimitMin = Configuration::ActivityView()->paceYaxisMinimum();
			$LimitMax = Configuration::ActivityView()->paceYaxisMaximum();

			if (!$LimitMin->automatic() || !$LimitMax->automatic()) {
				$setLimits = true;
				$autoscale = false;

				if (!$LimitMin->automatic() && $min < 1000 * $LimitMin->value()) {
					$min = 1000 * $LimitMin->value();
				} else {
					$min = 60 * 1000 * floor($min / 60 / 1000);
				}

				if (!$LimitMax->automatic() && $max > 1000 * $LimitMax->value()) {
					$max = 1000 * $LimitMax->value();
				} else {
					$max = 60 * 1000 * floor($max / 60 / 1000);
				}
			}
			if ($setLimits) {
				$Plot->setYLimits($yAxis, $min, $max, $autoscale);
				$Plot->setYAxisLabels($yAxis, null);
			}
		}

		switch (Configuration::ActivityView()->paceAxisType()->valueAsString()) {
			case PaceAxisType::AS_SPEED:
				$Plot->setYAxisPaceReverse($yAxis);
				break;
			case PaceAxisType::REVERSE:
				$Plot->setYAxisReverse($yAxis);
				break;
		}
	}

	/**
	 * @param Plot $plot
	 * @param $yAxis
	 * @param $dataMin
	 */
	private function setYAxisForReversePace(Plot $plot, $yAxis, $dataMin)
	{
		if ($this->paceUnitEnum == PaceUnit::MIN_PER_MILE) {
			$min = 240;
			$max = 3600;
			$ticks = [300, 360, 450, 600, 900];
		} else if ($this->paceUnitEnum == PaceUnit::MIN_PER_100M || $this->paceUnitEnum == PaceUnit::MIN_PER_100Y) {
			$min = $dataMin < 60*1000*10 ? 10 : 60;
			$max = 720;
			$ticks = [60, 120, 180, 240];
		} else if ($this->paceUnitEnum == PaceUnit::MIN_PER_500M || $this->paceUnitEnum == PaceUnit::MIN_PER_500Y) {
			$min = 60;
			$max = 720;
			$ticks = [60, 120, 180, 240];
		} else { // defaults to min/km
			if ($dataMin < 180*1000) {
				$min = 120;
				$max = 3600;
				$ticks = [120, 180, 240, 300, 360, 480, 600];
			} else if ($dataMin < 240*1000) {
				$min = 180;
				$max = 3600;
				$ticks = [180, 240, 300, 360, 480, 600];
			} else {
				$min = 240;
				$max = 3600;
				$ticks = [240, 300, 360, 480, 600];
			}
		}

		$ticks = array_map(function($v){
			return $v*1000;
		}, $ticks);

		$plot->setYLimits($yAxis, $min*1000, $max*1000, false);
		$plot->setYAxisLabels($yAxis, $ticks);

	}
}
