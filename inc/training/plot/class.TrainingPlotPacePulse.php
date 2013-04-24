<?php
/**
 * This file contains class::TrainingPlotPacePulse
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for pace and heartrate
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotPacePulse extends TrainingPlot {
	/**
	 * Data for pace
	 * @var array
	 */
	protected $DataPace = array();

	/**
	 *Data for heartrate
	 * @var array
	 */
	protected $DataPulse = array();

	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_PACEPULSE;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'pacepulse';
		$this->title = 'Pace/Herzfrequenz';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->DataPace  = TrainingPlotPace::getData($this->Training);
		$this->DataPulse = TrainingPlotPulse::getData($this->Training);

		$this->Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $this->DataPace);
		$this->Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $this->DataPulse, 'yaxis' => 2);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		TrainingPlotPace::setPropertiesTo($this->Plot, 1, $this->Training, $this->DataPace);

		$this->Plot->addYAxis(2, 'right', false);
		TrainingPlotPulse::setPropertiesTo($this->Plot, 2, $this->Training, $this->DataPulse);
	}
}