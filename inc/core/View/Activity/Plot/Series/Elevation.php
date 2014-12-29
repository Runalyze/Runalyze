<?php
/**
 * This file contains class::Elevation
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Route\Object as Route;
use Runalyze\View\Activity;

/**
 * Plot for: Elevation
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class Elevation extends ActivitySeries {
	/**
	 * @var string
	 */
	const COLOR = 'rgb(227,217,187)';

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context) {
		$this->initOptions();
		$this->initDataWithRoute($context);
	}

	/**
	 * Init data
	 * @var \Runalyze\View\Activity\Context $context
	 */
	protected function initDataWithRoute(Activity\Context $context) {
		$key = $context->route()->hasCorrectedElevations() ? Route::ELEVATIONS_CORRECTED : Route::ELEVATIONS_ORIGINAL;

		$Collector = new DataCollectorWithRoute($context->trackdata(), $key, $context->route());

		$this->Data = $Collector->data();
		$this->XAxis = $Collector->xAxis();
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Elevation');
		$this->Color = self::COLOR;

		$this->UnitString = 'm';
		$this->UnitDecimals = 0;

		$this->TickSize = 10;
		$this->TickDecimals = 0;

		$this->ShowAverage = false;
		$this->ShowMaximum = true;
		$this->ShowMinimum = true;
	}

	/**
	 * Add to plot
	 * @param \Plot $Plot
	 * @param int $yAxis
	 * @param boolean $addAnnotations [optional]
	 */
	public function addTo(\Plot &$Plot, $yAxis, $addAnnotations = true) {
		parent::addTo($Plot, $yAxis, $addAnnotations);

		$min = min($this->Data);
		$max = max($this->Data);

		if ($max - $min <= 50) {
			$minLimit = $min - 20;
			$maxLimit = $max + 20;
		} else {
			$minLimit = $min;
			$maxLimit = $max;
		}

		$Plot->setYLimits($yAxis, $minLimit, $maxLimit, true);

		$Plot->setLinesFilled(array($yAxis - 1));
	}
}