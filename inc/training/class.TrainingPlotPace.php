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
		$this->Data = $this->Training->GpsData()->getPlotDataForPace();

		if ($this->Training->Sport()->usesKmh()) {
			$this->Data = Plot::correctValuesFromPaceToKmh($this->Data);
		} else {
			$this->Data = Plot::correctValuesForTime($this->Data);
		}

		$this->Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		if ($this->Training->Sport()->usesKmh())
			$this->Plot->addYUnit(1, 'km/h');
		else
			$this->Plot->setYAxisTimeFormat('%M:%S');

		if (!$this->Training->Sport()->usesKmh()) {
			$setLimits = false;
			$autoscale = true;
			$min       = min($this->Data);
			$max       = max($this->Data);

			if (CONF_PACE_HIDE_OUTLIERS && ($max - $min) > 2*60*1000) {
				$setLimits = true;
				$num       = count($this->Data);
				$sorted    = $this->Data;
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
				$this->Plot->setYLimits(1, $min, $max, $autoscale);
				$this->Plot->setYTicks(1, null);
			}
		}

		if (CONF_PACE_Y_AXIS_REVERSE)
			$this->Plot->setYAxisReverse(1);
	}
}