<?php
/**
 * This file contains class::TrainingPlotTemperature
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for temperature
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotTemperature extends TrainingPlot {
	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'temperature';
		$this->title = __('Temperature');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);

		$this->Plot->Data[] = array('label' => $this->title, 'color' => 'rgb(100,0,200)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		self::setPropertiesTo($this->Plot, 1, $this->Training, $this->Data);
	}

	/**
	 * Get data
	 * @param TrainingObject $Training
	 * @return array
	 */
	static public function getData(TrainingObject &$Training) {
		return $Training->GpsData()->getPlotDataForTemperature();
	}

	/**
	 * Get unit for current power mode
	 * @return string
	 */
	static public function getUnitAsString() {
		return '°C';
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param TrainingObject $Training
	 * @param array $Data 
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, TrainingObject &$Training, array $Data) {
		$average = round(array_sum($Data)/count($Data),1);

		$Plot->addYUnit($YAxis, '°C', 1);
		$Plot->setYTicks($YAxis, 1, 0);

		if ($YAxis == 1) {
			$Plot->addThreshold('y'.$YAxis, $average, 'rgba(0,0,0,0.5)');
			$Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.self::getUnitAsString());
		}
	}
}
