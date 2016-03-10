<?php
/**
 * This file contains class::ElevationAlgorithms
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;
use Runalyze\View\Plot\Series as PlotSeries;
use Runalyze\Parameter\Application\ElevationMethod;
use Runalyze\Configuration;
use Runalyze\Data;
use Runalyze\Model\Trackdata;
use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Plot for: Elevation algorithms
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class ElevationAlgorithms extends ActivityPlot {
	/**
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/** @var int|bool */
	protected $Min = false;

	/** @var int|bool */
	protected $Max = false;

	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key   = 'elevation-algorithms';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->Context = $context;

		$this->addSeries($this->seriesForCorrectedData(), 1, false);

		if ($context->route()->hasCorrectedElevations() && $context->route()->hasOriginalElevations()) {
			$this->addSeries($this->seriesForOriginalData(), 1, false);
		}

		$this->addSeries($this->seriesForThreshold(), 1, false);
		$this->addSeries($this->seriesForDouglasPeucker(), 1, false);

		foreach (array_keys($this->Plot->Data) as $key) {
			$this->Plot->Data[$key]['curvedLines'] = array('apply' => false);
		}

		$this->setAxisLimits();
	}

	/**
	 * @return \Runalyze\View\Plot\Series
	 */
	protected function seriesForCorrectedData() {
		$Series = new Series\Elevation($this->Context);

		$this->updateLimits($Series->limits());

		return $Series;
	}

	/**
	 * @return \Runalyze\View\Plot\Series
	 */
	protected function seriesForOriginalData() {
		$Series = new Series\Elevation($this->Context, true);
		$Series->setColor('#ccc');
		$Series->setLabel( __('Original data') );

		$this->updateLimits($Series->limits());

		return $Series;
	}

	/**
	 * @return \Runalyze\View\Plot\Series
	 */
	protected function seriesForThreshold() {
		$Series = new PlotSeries();
		$Series->setColor('#008');
		$Series->setLabel(__('Threshold'));
		$Series->setData($this->constructPlotDataFor(ElevationMethod::THRESHOLD));

		return $Series;
	}

	/**
	 * @return \Runalyze\View\Plot\Series
	 */
	protected function seriesForDouglasPeucker() {
		$Series = new PlotSeries();
		$Series->setColor('#800');
		$Series->setLabel(__('Douglas-Peucker'));
		$Series->setData($this->constructPlotDataFor(ElevationMethod::DOUGLAS_PEUCKER));

		return $Series;
	}

	/**
	 * Construct plot data
	 * @param mixed $algorithm
	 * @param int|bool $treshold
	 * @return array
	 */
	protected function constructPlotDataFor($algorithm, $treshold = false) {
		$Method = new ElevationMethod();
		$Method->set($algorithm);

		if ($treshold === false) {
			$treshold = Configuration::ActivityView()->elevationMinDiff();
		}

		$Calculator = new Data\Elevation\Calculation\Calculator($this->Context->route()->elevations());
		$Calculator->setMethod($Method);
		$Calculator->setThreshold($treshold);
		$Calculator->calculate();

		$i = 0;
		$Data = array();
		$Points = $Calculator->strategy()->smoothedData();
		$Indices = $Calculator->strategy()->smoothingIndices();
		$hasDistances = $this->Context->trackdata()->get(Trackdata\Entity::DISTANCE);
		$Distances = $this->Context->trackdata()->get(Trackdata\Entity::DISTANCE);
		$Times = $this->Context->trackdata()->get(Trackdata\Entity::TIME);
		$num = $this->Context->trackdata()->num();

		foreach ($Indices as $i => $index) {
			if ($index >= $num) {
				$index = $num - 1;
			}

			if ($hasDistances) {
				$Data[(string)$Distances[$index]] = $Points[$i];
			} else {
				$Data[(string)$Times[$index].'000'] = $Points[$i];
			}
		}

		$this->manipulateData($Data);

		return $Data;
	}

	/**
	 * Manipulate data for correct unit
	 * @param array $data
	 */
	protected function manipulateData(array &$data) {
		$UnitSystem = Configuration::General()->distanceUnitSystem();

		if ($UnitSystem->isImperial()) {
			$data = array_map(function($value) {
				return round($value * DistanceUnitSystem::FEET_MULTIPLIER / 1000);
			}, $data);
		}

		$this->updateLimits(array(min($data), max($data)));
	}

	/**
	 * @param array $minmax array($min, $max)
	 */
	protected function updateLimits(array $minmax) {
		$min = $minmax[0];
		$max = $minmax[1];

		if ($this->Min === false || $min < $this->Min) {
			$this->Min = $min;
		}

		if ($this->Max === false || $max > $this->Max) {
			$this->Max = $max;
		}
	}

	/**
	 * Set maximal limits for all series
	 */
	protected function setAxisLimits() {
		if ($this->Min !== false && $this->Max !== false) {
			if ($this->Max - $this->Min <= 50) {
				$minLimit = $this->Min - 20;
				$maxLimit = $this->Max + 20;
			} else {
				$minLimit = $this->Min;
				$maxLimit = $this->Max;
			}
	
			$this->Plot->setYLimits(1, $minLimit, $maxLimit, true);
		}
	}
}