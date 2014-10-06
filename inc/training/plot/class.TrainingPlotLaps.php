<?php
/**
 * This file contains class::TrainingPlotLaps
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for laps
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
abstract class TrainingPlotLaps extends TrainingPlot {
	/**
	 * Selection enabled?
	 * @var bool
	 */
	protected $selecting = false;

	/**
	 * Uses standard x-axis?
	 * @var bool
	 */
	protected $useStandardXaxis = false;

	/**
	 * Labels
	 * @var array
	 */
	protected $Labels = array();

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		if (!empty($this->Data)) {
			$min = Helper::floorFor(min($this->Data), 30000);
			$max = Helper::ceilFor(max($this->Data), 30000);
			$this->Plot->setYLimits(1, $min, $max, false);
		}

		$this->Plot->setYAxisTimeFormat('%M:%S');
		$this->Plot->setXLabels($this->Labels);
		$this->Plot->showBars(true);

		$this->Plot->setYTicks(1, null);
		$this->Plot->Options['xaxis']['show'] = true; // force to show xaxis-labels, even if no time or distance array is given

		$this->addAnnotations();
	}

	/**
	 * Add annotations
	 */
	protected function addAnnotations() {
		// Can be overwritten in subclass
	}

	/**
	 * Splits are not complete
	 * 
	 * "Complete" means: all laps active and complete distance
	 * @return boolean
	 */
	protected function splitsAreNotComplete() {
		if ($this->Training->Splits()->areEmpty() || $this->Training->Splits()->totalDistance() <= 0)
			return false;

		if ($this->Training->Splits()->hasActiveAndInactiveLaps())
			return true;

		if (!Validator::isClose($this->Training->Splits()->totalDistance(), $this->Training->getDistance(), 2))
			return true;

		return false;
	}
}