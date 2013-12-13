<?php
/**
 * This file contains class::TrainingPlotPulse
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for heartrate
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotPulse extends TrainingPlot {
	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_PULSE;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'pulse';
		$this->title = 'Herzfrequenz';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);

		$this->Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $this->Data);
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
		switch (CONF_PULS_MODE) {
			case 'hfmax':
				return $Training->GpsData()->getPlotDataForHeartrateInPercent();
			case 'hfres':
				return $Training->GpsData()->getPlotDataForHeartrateInPercentReserve();
			case 'bpm':
			default:
				return $Training->GpsData()->getPlotDataForHeartrate();
		}
	}

	/**
	 * Is data shown in percent?
	 * @return boolean
	 */
	static public function inPercent() {
		return (CONF_PULS_MODE == 'hfmax' || CONF_PULS_MODE == 'hfres');
	}

	/**
	 * Get HFmax for plot
	 * @return int
	 */
	static public function HFmax() {
		return (self::inPercent()) ? 100 : HF_MAX;
	}

	/**
	 * Get unit for current pulse mode
	 * @return string
	 */
	static public function getUnitAsString() {
		switch (CONF_PULS_MODE) {
			case 'hfmax':
				return '&#37; HFmax';
			case 'hfres':
				return '&#37; HFreserve';
			case 'bpm':
			default:
				return 'bpm';
		}
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

		if (self::inPercent()) {
			$Plot->addYUnit($YAxis, '%', 1);
			$Plot->setYTicks($YAxis, 5, 0);

			if ($average >= 60)
				$Plot->setYLimits($YAxis, 50, 100);
			else
				$Plot->setYLimits($YAxis, 10*floor(min($Data)/10), 100);
		} else {
			$Plot->addYUnit($YAxis, 'bpm', 0);
			$Plot->setYTicks($YAxis, 10, 0);
			$Plot->setYLimits($YAxis, 10*floor(0.5*self::HFmax()/10), 10*ceil(self::HFmax()/10));
		}

		if ($YAxis == 1) {
			$Plot->addThreshold('y'.$YAxis, $average, 'rgba(0,0,0,0.5)');
			$Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.self::getUnitAsString());
		}

		$Plot->addMarkingArea('y'.$YAxis, 10*ceil(self::HFmax()/10)*1,   10*ceil(self::HFmax()/10)*0.9, 'rgba(255,100,100,0.3)');
		$Plot->addMarkingArea('y'.$YAxis, 10*ceil(self::HFmax()/10)*0.9, 10*ceil(self::HFmax()/10)*0.8, 'rgba(255,100,100,0.2)');
		$Plot->addMarkingArea('y'.$YAxis, 10*ceil(self::HFmax()/10)*0.8, 10*ceil(self::HFmax()/10)*0.7, 'rgba(255,100,100,0.1)');
	}
}