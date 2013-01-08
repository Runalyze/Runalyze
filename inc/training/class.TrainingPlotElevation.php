<?php
/**
 * Class: TrainingPlotElevation
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingPlotElevation extends TrainingPlot {
	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_ELEVATION;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'elevation';
		$this->title = 'H&ouml;henprofil';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);
		$this->Plot->Data[] = array('label' => 'H&ouml;he', 'color' => 'rgba(227,217,187,1)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		self::setPropertiesTo($this->Plot, 1, $this->Training, $this->Data);
	}

	/**
	 * Get data
	 * @return array
	 */
	static public function getData(Training &$Training) {
		return $Training->GpsData()->getPlotDataForElevation();
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param Training $Training
	 * @param array $Data 
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, Training &$Training, array $Data) {
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
		$Plot->addYUnit($YAxis, 'm');
		$Plot->setYLimits($YAxis, $minLimit, $maxLimit, true);

		$Plot->setLinesFilled(array($YAxis - 1));

		$Plot->addAnnotation($minXvalues[0], $min, $min.'m');
		$Plot->addAnnotation($maxXvalues[0], $max, $max.'m');
	}
}