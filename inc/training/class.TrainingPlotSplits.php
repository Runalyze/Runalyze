<?php
/**
 * Class: TrainingPlotSplits
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingPlotSplits extends TrainingPlot {
	protected $selecting = false;
	protected $useStandardXaxis = false;

	private $demandedPace = 0;
	private $achievedPace = 0;
	private $Labels = array();

	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_SPLITS;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'splits';
		$this->title = 'Zwischenzeiten';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		if ($this->Training->hasSplits()) {
			$this->Labels = $this->Training->Splits()->distancesAsArray();
			$this->Data   = $this->Training->Splits()->pacesAsArray();
			$num          = count($this->Data);
			$unit         = ($num >= 20) ? '' : ' km';

			$this->demandedPace = Running::DescriptionToDemandedPace($this->Training->get('comment'));
			$this->achievedPace = array_sum($this->Data) / $num;

			foreach ($this->Data as $key => $val) {
				if ($num > 35)
					$this->Labels[$key] = round($this->Labels[$key], 1);
				elseif ($num > 25)
					$this->Labels[$key] = number_format($this->Labels[$key], 1, ',', '.');

				$this->Labels[$key] = array($key, $this->Labels[$key].$unit);
				$this->Data[$key]   = $val*1000;
			}
		} else {
			$RawData = $this->Training->GpsData()->getRoundsAsFilledArray();
			$num     = count($RawData);

			foreach ($RawData as $key => $val) {
				$km = $key + 1;
				if ($num < 20) {
					$label = ($km%2 == 0 && $km > 0) ? $km.'&nbsp;km' : '';
				} else {
					$label = ($km%5 == 0 && $km > 0) ? $km.'&nbsp;km' : '';
				}

				$this->Labels[$key] = array($key, $label);
				$this->Data[$key]   = $val['s']*1000/$val['km'];
			}
		}

		$this->Plot->Data[] = array('label' => 'Zwischenzeiten', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$min = min($this->Data); $min = floor($min/30000)*30000;
		$max = max($this->Data); $max = ceil($max/30000)*30000;

		$this->Plot->setYAxisTimeFormat('%M:%S');
		$this->Plot->setXLabels($this->Labels);
		$this->Plot->showBars(true);

		$this->Plot->setYLimits(1, $min, $max, false);
		$this->Plot->setYTicks(1, null);

		if ($this->demandedPace > 0) {
			$this->Plot->addThreshold("y", $this->demandedPace*1000, 'rgb(180,0,0)');
			//$this->Plot->addAnnotation(count($Data)-1, $this->demandedPace*1000, 'Soll: '.Time::toString($this->demandedPace), -10, -7);
		}
		if ($this->achievedPace > 0) {
			$this->Plot->addThreshold("y", $this->achievedPace*1000, 'rgb(0,180,0)');
			$this->Plot->addAnnotation(0, $this->achievedPace*1000, '&oslash; '.Time::toString(round($this->achievedPace)), -20, -7);
		}
	}
}