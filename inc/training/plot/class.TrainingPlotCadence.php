<?php
/**
 * This file contains class::TrainingPlotCadence
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for cadence
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotCadence extends TrainingPlot {
	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'cadence';
		$this->title = $this->Training->Cadence()->label();
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);

		$this->Plot->Data[] = array('label' => $this->title, 'color' => 'rgb(200,100,0)', 'data' => $this->Data);
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
		$array = $Training->GpsData()->getPlotDataForCadence();
		$Training->Cadence()->manipulateArray($array);

		return $array;
	}

	/**
	 * Get unit for current power mode
	 * @return string
	 */
	static public function getUnitAsString() {
		return '/min';
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param TrainingObject $Training
	 * @param array $Data 
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, TrainingObject &$Training, array $Data) {
		$average = TrainingPlot::averageWithoutLowValues($Data);

		$Plot->addYUnit($YAxis, $Training->Cadence()->unitAsString(), 0);
		$Plot->setYTicks($YAxis, 10, 0);
		//$Plot->setYLimits($YAxis, 0, Helper::ceilFor(max($Data), 100));

		if ($YAxis == 1) {
			$Plot->addThreshold('y'.$YAxis, $average, 'rgba(0,0,0,0.5)');
			$Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.self::getUnitAsString());
		}
	}
}