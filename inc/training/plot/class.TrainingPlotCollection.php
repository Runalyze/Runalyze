<?php
/**
 * This file contains class::TrainingPlotCollection
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot with collection of pace, heartrate and elevation
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotCollection extends TrainingPlot {
	/**
	 * Data for pace
	 * @var array
	 */
	protected $DataPace = array();

	/**
	 * Data for heartrate
	 * @var array
	 */
	protected $DataPulse = array();

	/**
	 * Data for elevation
	 * @var array
	 */
	protected $DataElevation = array();

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'collection';
		$this->title = __('Pace/Heartrate/Elevation');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->DataPace      = TrainingPlotPace::getData($this->Training);
		$this->DataPulse     = TrainingPlotPulse::getData($this->Training);
		$this->DataElevation = TrainingPlotElevation::getData($this->Training);

		$i = 1;

		if (!empty($this->DataElevation)) {
			$this->Plot->Data[] = array('label' => __('Elevation'), 'color' => 'rgba(227,217,187,1)', 'data' => $this->DataElevation, 'yaxis' => $i);
			$i++;
		}

		if (!empty($this->DataPulse)) {
			$this->Plot->Data[] = array('label' => __('HR'), 'color' => 'rgb(136,0,0)', 'data' => $this->DataPulse, 'yaxis' => $i);
			$i++;
		}

		if (!empty($this->DataPace)) {
			$this->Plot->Data[] = array('label' => __('Pace'), 'color' => 'rgb(0,0,136)', 'data' => $this->DataPace, 'yaxis' => $i);
		}
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$i = 1;

		if (!empty($this->DataElevation)) {
			$this->Plot->addYAxis($i, 'left');
			TrainingPlotElevation::setPropertiesTo($this->Plot, $i, $this->Training, $this->DataElevation, false);
			$i++;
		}

		if (!empty($this->DataPulse)) {
			$this->Plot->addYAxis($i, 'right', false);
			TrainingPlotPulse::setPropertiesTo($this->Plot, $i, $this->Training, $this->DataPulse);
			$i++;
		}

		if (!empty($this->DataPace)) {
			$this->Plot->addYAxis($i, 'right', true, 0);
			TrainingPlotPace::setPropertiesTo($this->Plot, $i, $this->Training, $this->DataPace);
		}
	}
}