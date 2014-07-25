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
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_COLLECTION;
	}

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

		$this->Plot->Data[] = array('label' => __('Elevation'), 'color' => 'rgba(227,217,187,1)', 'data' => $this->DataElevation);
		$this->Plot->Data[] = array('label' => __('HR'), 'color' => 'rgb(136,0,0)', 'data' => $this->DataPulse, 'yaxis' => 2);
		$this->Plot->Data[] = array('label' => __('Pace'), 'color' => 'rgb(0,0,136)', 'data' => $this->DataPace, 'yaxis' => 3);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$this->Plot->addYAxis(1, 'left');
		TrainingPlotElevation::setPropertiesTo($this->Plot, 1, $this->Training, $this->DataElevation);

		$this->Plot->addYAxis(2, 'right', false);
		TrainingPlotPulse::setPropertiesTo($this->Plot, 2, $this->Training, $this->DataPulse);

		$this->Plot->addYAxis(3, 'right', true, 0);
		TrainingPlotPace::setPropertiesTo($this->Plot, 3, $this->Training, $this->DataPace);
	}
}