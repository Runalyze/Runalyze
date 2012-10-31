<?php
/**
 * Class: TrainingPlotPulse
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingPlotPulse extends TrainingPlot {
	private $InPercent = false;
	private $HFmax = 200;

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
		$this->InPercent = (CONF_PULS_MODE == 'hfmax' || CONF_PULS_MODE == 'hfres');
		$this->HFmax     = ($this->InPercent) ? 100 : HF_MAX;
		$this->Data = $this->Training->GpsData()->getPlotDataForHeartrate($this->InPercent);

		$this->Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$average = round(array_sum($this->Data)/count($this->Data));

		if ($this->InPercent) {
			$this->Plot->addYUnit(1, '%');
			$this->Plot->setYTicks(1, 5, 0);
			$this->Plot->setYLimits(1, 50, 100);
		} else {
			$this->Plot->addYUnit(1, 'bpm');
			$this->Plot->setYTicks(1, 10, 0);
			$this->Plot->setYLimits(1, 10*floor(0.5*$this->HFmax/10), 10*ceil($this->HFmax/10));
		}


		$this->Plot->addThreshold("y1", $average, 'rgba(0,0,0,0.5)');
		$this->Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.($this->InPercent ? '&#37;' : 'bpm'));

		$this->Plot->addMarkingArea("y1", 10*ceil($this->HFmax/10)*1,   10*ceil($this->HFmax/10)*0.9, 'rgba(255,100,100,0.3)');
		$this->Plot->addMarkingArea("y1", 10*ceil($this->HFmax/10)*0.9, 10*ceil($this->HFmax/10)*0.8, 'rgba(255,100,100,0.2)');
		$this->Plot->addMarkingArea("y1", 10*ceil($this->HFmax/10)*0.8, 10*ceil($this->HFmax/10)*0.7, 'rgba(255,100,100,0.1)');
	}
}