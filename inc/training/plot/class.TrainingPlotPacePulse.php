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
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'pacepulse';
		$this->title = __('Pace/Heartrate');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$i = 1;
		$this->DataPace  = TrainingPlotPace::getData($this->Training);
		$this->DataPulse = TrainingPlotPulse::getData($this->Training);

		if (!empty($this->DataPace)) {
			$this->Plot->Data[] = array('label' => __('Pace'), 'color' => 'rgb(0,0,136)', 'data' => $this->DataPace, 'yaxis' => $i);
			$i++;
		}

		if (!empty($this->DataPulse)) {
			$this->Plot->Data[] = array('label' => __('Heartrate'), 'color' => 'rgb(136,0,0)', 'data' => $this->DataPulse, 'yaxis' => $i);
		}
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$i = 1;

		if (!empty($this->DataPace)) {
			TrainingPlotPace::setPropertiesTo($this->Plot, $i, $this->Training, $this->DataPace);
			$i++;
		}

		if (!empty($this->DataPulse)) {
			if ($i == 2) {
				$this->Plot->addYAxis($i, 'right', false);
			}

			TrainingPlotPulse::setPropertiesTo($this->Plot, $i, $this->Training, $this->DataPulse);
		}
	}
}