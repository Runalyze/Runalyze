<?php
/**
 * This file contains class::TrainingPlotPower
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for power
 * @author Nils Frohberg
 * @package Runalyze\Draw\Training
 */
class TrainingPlotPower extends TrainingPlot {
	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return TRAINING_SHOW_PLOT_POWER;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'power';
		$this->title = 'Power';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);

		$this->Plot->Data[] = array('label' => 'Power', 'color' => 'rgb(0,136,0)', 'data' => $this->Data);
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
		return $Training->GpsData()->getPlotDataForPower();
	}

	/**
	 * Is data shown in percent?
	 * @return boolean
	 */
	static public function inPercent() {
		return false; // TODO?
	}

	/**
	 * Get unit for current power mode
	 * @return string
	 */
	static public function getUnitAsString() {
		return 'W';
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param TrainingObject $Training
	 * @param array $Data 
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, TrainingObject &$Training, array $Data) {
		$average = round(array_sum($Data)/count($Data));

		/*if (self::inPercent()) {
			$Plot->addYUnit($YAxis, '%');
			$Plot->setYTicks($YAxis, 5, 0);
			$Plot->setYLimits($YAxis, 50, 100);
		} else {*/
			$Plot->addYUnit($YAxis, 'W');
			$Plot->setYTicks($YAxis, 10, 0);
			$Plot->setYLimits($YAxis, 0, Helper::ceilFor(max($Data), 100));
			//$Plot->setYLimits($YAxis, 0, 500); /* XXX */
		//}

		if ($YAxis == 1) {
			$Plot->addThreshold('y'.$YAxis, $average, 'rgba(0,0,0,0.5)');
			$Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.self::getUnitAsString());
		}
	}
}