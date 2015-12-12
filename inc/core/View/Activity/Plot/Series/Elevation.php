<?php
/**
 * This file contains class::Elevation
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Configuration;
use Runalyze\Model\Route\Entity as Route;
use Runalyze\Parameter\Application\DistanceUnitSystem;
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

	/** @var bool */
	protected $ShowAsFilledLine = true;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 * @var boolean $forceOriginal [optional]
	 */
	public function __construct(Activity\Context $context, $forceOriginal = false) {
		$this->initOptions();
		$this->initDataWithRoute($context, $forceOriginal);
	}

	/**
	 * Init data
	 * @var \Runalyze\View\Activity\Context $context
	 * @var boolean $forceOriginal
	 */
	protected function initDataWithRoute(Activity\Context $context, $forceOriginal) {
		if (!$context->hasRoute()) {
			$this->Data = array();
			return;
		}

		$key = $context->route()->hasCorrectedElevations() && !$forceOriginal ? Route::ELEVATIONS_CORRECTED : Route::ELEVATIONS_ORIGINAL;

		if (!$context->route()->has($key)) {
			$this->Data = array();
			return;
		}
		
		$Collector = new DataCollectorWithRoute($context->trackdata(), $key, $context->route());
		$this->Data = $Collector->data();
		$this->XAxis = $Collector->xAxis();

		$this->manipulateData();
	}

	/**
	 * Manipulate data for correct unit
	 */
	protected function manipulateData() {
		$UnitSystem = Configuration::General()->distanceUnitSystem();

		if ($UnitSystem->isImperial()) {
			$this->Data = array_map(function($value) {
				return round($value * DistanceUnitSystem::FEET_MULTIPLIER / 1000);
			}, $this->Data);
		}
	}

	/**
	 * Init options
	 */
	protected function initOptions() {
		$this->Label = __('Elevation');
		$this->Color = self::COLOR;

		$this->UnitString = Configuration::General()->distanceUnitSystem()->elevationUnit();
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
		if (empty($this->Data)) {
			return;
		}

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

		if ($this->ShowAsFilledLine) {
			$Plot->setLinesFilled(array($yAxis - 1));
		}
	}

	/**
	 * @return array array($min, $max)
	 */
	public function limits() {
		return array(
			min($this->Data),
			max($this->Data)
		);
	}

	/**
	 * @param bool $flag
	 */
	public function showAsFilledLine($flag = true) {
		$this->ShowAsFilledLine = $flag;
	}
}