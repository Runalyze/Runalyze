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
		$this->Data = $this->Training->GpsData()->getPlotDataForElevation();
		$this->Plot->Data[] = array('label' => 'H&ouml;he', 'color' => 'rgba(227,217,187,1)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$min = min($this->Data); $minXvalues = array_keys($this->Data, $min);
		$max = max($this->Data); $maxXvalues = array_keys($this->Data, $max);

		if ($max - $min <= 50) {
			$minLimit = $min - 20;
			$maxLimit = $max + 20;
		} else {
			$minLimit = $min;
			$maxLimit = $max;
		}

		$this->Plot->addYAxis(1, 'left');
		$this->Plot->addYUnit(1, 'm');
		$this->Plot->setYLimits(1, $minLimit, $maxLimit, true);

		$this->Plot->setLinesFilled();

		$this->Plot->addAnnotation($minXvalues[0], $min, $min.'m');
		$this->Plot->addAnnotation($maxXvalues[0], $max, $max.'m');
	}
}