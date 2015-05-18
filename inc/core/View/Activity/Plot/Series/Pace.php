<?php
/**
 * This file contains class::Pace
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Plot;
use Runalyze\Configuration;
use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\Parameter\Application\PaceAxisType;
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
	* @var type
	*/
	static private $CUT_OUTLIER_PERCENTAGE = 10;

	/**
	 * @var string
	 */
	const COLOR = 'rgb(0,0,136)';

	/**
	 * @var boolean
	 */
	protected $isRunning;

	/**
	 * @var enum
	 */
	protected $paceUnit;

	/**
	 * @var bool
	 */
	protected $paceInTime;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->paceUnit = $context->sport()->paceUnit();

		$this->paceInTime = ($this->paceUnit == \Runalyze\Activity\Pace::MIN_PER_KM || $this->paceUnit == \Runalyze\Activity\Pace::MIN_PER_100M || $this->paceUnit == \Runalyze\Activity\Pace::MIN_PER_500M);
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

		$pace = new \Runalyze\Activity\Pace(0, 1, $this->paceUnit);
		$this->UnitString = !$this->paceInTime ? str_replace('&nbsp;', '', $pace->appendix()) : '';
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
		switch ($this->paceUnit) {
			case \Runalyze\Activity\Pace::KM_PER_H:
				$this->Data = array_map(function($v){
					return ($v == 0) ? 0 : round(3600/$v, 1);
				}, $this->Data);
				break;

			case \Runalyze\Activity\Pace::M_PER_S:
				$this->Data = array_map(function($v){
					return ($v == 0) ? 0 : round(1000/$v, 1);
				}, $this->Data);
				break;

			case \Runalyze\Activity\Pace::MIN_PER_100M:
				$this->Data = array_map(function($v){
					return ($v == 0) ? 36000*100 :round($v*100);
				}, $this->Data);
				break;

			case \Runalyze\Activity\Pace::MIN_PER_500M:
				$this->Data = array_map(function($v){
					return ($v == 0) ? 36000*500 :round($v*500);
				}, $this->Data);
				break;

			case \Runalyze\Activity\Pace::MIN_PER_KM:
			default:
				$this->Data = array_map(function($v){
					return ($v == 0) ? 3600*1000 :round($v*1000);
				}, $this->Data);
				break;
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

		if (!$this->paceInTime) return;

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
		if ($this->paceUnit == \Runalyze\Activity\Pace::MIN_PER_KM) {
			if ($dataMin < 180000) {
				$min = 120000;
				$max = 3600000;
				$ticks = [120000, 180000, 240000, 300000, 360000, 480000, 600000];
			} else if ($dataMin < 240000) {
				$min = 180000;
				$max = 3600000;
				$ticks = [180000, 240000, 300000, 360000, 480000, 600000];
			} else {
				$min = 240000;
				$max = 3600000;
				$ticks = [240000, 300000, 360000, 480000, 600000];
			}
		} else if ($this->paceUnit == \Runalyze\Activity\Pace::MIN_PER_100M) {
			$min = 60000;
			$max = 7200000;
			$ticks = [60000, 120000, 180000, 240000];
		}

		$plot->setYLimits($yAxis, $min, $max, false);
		$plot->setYAxisLabels($yAxis, $ticks);

	}
}
