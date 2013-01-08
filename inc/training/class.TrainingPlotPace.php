<?php
/**
 * Class: TrainingPlotPace
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingPlotPace extends TrainingPlot {
	/**
	 * How many outliers should be cutted away?
	 * @var type 
	 */
	static private $CUT_OUTLIER_PERCENTAGE = 10;

	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_PACE;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'pace';
		$this->title = 'Pace';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Data = self::getData($this->Training);

		$this->Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		self::setPropertiesTo($this->Plot, 1, $this->Training, $this->Data);
	}

	/**
	 * Get data
	 * @param Training $Training
	 * @return array
	 */
	static public function getData(Training &$Training) {
		$Data = $Training->GpsData()->getPlotDataForPace();

		if ($Training->Sport()->usesKmh()) {
			$Data = Plot::correctValuesFromPaceToKmh($Data);
		} else {
			$Data = Plot::correctValuesForTime($Data);
		}

		return $Data;
	}

	/**
	 * Set properties
	 * @param Plot $Plot
	 * @param int $YAxis
	 * @param Training $Training
	 * @param array $Data 
	 */
	static public function setPropertiesTo(Plot &$Plot, $YAxis, Training &$Training, array $Data) {
		if ($Training->Sport()->usesKmh())
			$Plot->addYUnit($YAxis, 'km/h');
		else
			$Plot->setYAxisTimeFormat('%M:%S', $YAxis);

		if (!$Training->Sport()->usesKmh()) {
			$setLimits = false;
			$autoscale = true;
			$min       = min($Data);
			$max       = max($Data);

			if (CONF_PACE_HIDE_OUTLIERS && ($max - $min) > 2*60*1000) {
				$setLimits = true;
				$num       = count($Data);
				$sorted    = $Data;
				sort($sorted);

				$min = $sorted[round((self::$CUT_OUTLIER_PERCENTAGE/2/100)*$num)];
				$max = $sorted[round((1-self::$CUT_OUTLIER_PERCENTAGE/2/100)*$num)-1];

				$min = 10*1000*floor($min/10/1000);
				$max = 10*1000*ceil($max/10/1000);
			}

			if (CONF_PACE_Y_LIMIT_MIN != 0 || CONF_PACE_Y_LIMIT_MAX != 0) {
				$setLimits = true;
				$autoscale = false;

				if (CONF_PACE_Y_LIMIT_MIN != 0 && $min < 1000*CONF_PACE_Y_LIMIT_MIN)
					$min = 1000*CONF_PACE_Y_LIMIT_MIN;
				else
					$min = 60*1000*floor($min/60/1000);

				if (CONF_PACE_Y_LIMIT_MAX != 0 && $max > 1000*CONF_PACE_Y_LIMIT_MAX)
					$max = 1000*CONF_PACE_Y_LIMIT_MAX;
				else
					$max = 60*1000*ceil($max/60/1000);
			}

			if ($setLimits) {
				$Plot->setYLimits($YAxis, $min, $max, $autoscale);
				$Plot->setYTicks($YAxis, null);
			}
		}

		if (CONF_PACE_Y_AXIS_REVERSE)
			$Plot->setYAxisReverse($YAxis);
	}
}