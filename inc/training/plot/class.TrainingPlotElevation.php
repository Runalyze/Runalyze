<?php
/**
 * This file contains class::TrainingPlotElevation
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for elevation
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotElevation extends TrainingPlot {
	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'elevation';
		$this->title = __('Elevation profile');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);
		$this->Plot->Data[] = array('label' => __('Elevation'), 'color' => 'rgba(227,217,187,1)', 'data' => $this->Data);
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
		if (!$Training->hasArrayAltitude() && !$Training->hasArrayAltitudeOriginal()) {
			return array();
		}

		return $Training->GpsData()->getPlotDataForElevation();
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param TrainingObject $Training
	 * @param array $Data 
	 * @param bool $annotations
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, TrainingObject &$Training, array $Data, $annotations = true) {
		$min = min($Data); $minXvalues = array_keys($Data, $min);
		$max = max($Data); $maxXvalues = array_keys($Data, $max);

		if ($max - $min <= 50) {
			$minLimit = $min - 20;
			$maxLimit = $max + 20;
		} else {
			$minLimit = $min;
			$maxLimit = $max;
		}

		$Plot->addYAxis($YAxis, 'left');
		$Plot->addYUnit($YAxis, 'm', 0);
		$Plot->setYLimits($YAxis, $minLimit, $maxLimit, true);

		$Plot->setLinesFilled(array($YAxis - 1));

		if ($annotations) {
			$Plot->addAnnotation($minXvalues[0], $min, $min.'m');
			$Plot->addAnnotation($maxXvalues[0], $max, $max.'m');
		}
	}
}