<?php
/**
 * This file contains class::Pace
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Object as Trackdata;
use Runalyze\View\Activity;
use Runalyze\Configuration;


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
	protected $asKMH;

	/**
	 * @var boolean
	 */
	protected $isRunning;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->asKMH = ($context->sport()->paceUnit() == \Runalyze\Activity\Pace::KM_PER_H);
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

		$this->UnitString = $this->asKMH ? 'km/h' : '';
		$this->UnitDecimals = 0;

		$this->TickSize = false;

		$this->ShowAverage = false;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}

	/**
	 * Manipulate data
	 */
	protected function manipulateData() {
		if ($this->asKMH) {
			$this->Data = \Plot::correctValuesFromPaceToKmh($this->Data);
		} else {
			$this->Data = \Plot::correctValuesForTime($this->Data);
		}
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(\Plot &$Plot, $yAxis, $addAnnotations = true) {
		if (empty($this->Data)) {
			return;
		}

		parent::addTo($Plot, $yAxis, $addAnnotations);

		if (!$this->asKMH) {
			$Plot->setYAxisTimeFormat('%M:%S', $yAxis);

			$setLimits = false;
			$autoscale = true;
			$min       = min($this->Data);
			$max       = max($this->Data);

			if ($max > 50*60*1000) {
				$setLimits = true;
				$max = 50*60*1000;
			}

			if (Configuration::ActivityView()->ignorePaceOutliers() && ($max - $min) > 2*60*1000) {
				$setLimits = true;
				$num       = count($this->Data);
				$sorted    = $this->Data;
				sort($sorted);

				$min = 10*1000*floor( $sorted[round((self::$CUT_OUTLIER_PERCENTAGE/2/100)*$num)] /10/1000 );
				$max = 10*1000*ceil( $sorted[round((1-self::$CUT_OUTLIER_PERCENTAGE/2/100)*$num)-1] /10/1000 );
			}

			if ($this->isRunning) {
				$LimitMin = Configuration::ActivityView()->paceYaxisMinimum();
				$LimitMax = Configuration::ActivityView()->paceYaxisMaximum();

				if (!$LimitMin->automatic() || !$LimitMax->automatic()) {
					$setLimits = true;
					$autoscale = false;

					if (!$LimitMin->automatic() && $min < 1000*$LimitMin->value()) {
						$min = 1000*$LimitMin->value();
					} else {
						$min = 60*1000*floor($min/60/1000);
					}

					if (!$LimitMax->automatic() && $max > 1000*$LimitMax->value()) {
						$max = 1000*$LimitMax->value();
					} else {
						$max = 60*1000*floor($max/60/1000);
					}	
				}
			}

			if ($setLimits) {
				$Plot->setYLimits($yAxis, $min, $max, $autoscale);
				$Plot->setYTicks($yAxis, null);
			}
		}

		if (Configuration::ActivityView()->reversePaceAxis()) {
			$Plot->setYAxisReverse($yAxis);
		}
	}
}
