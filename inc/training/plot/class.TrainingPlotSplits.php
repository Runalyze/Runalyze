<?php
/**
 * This file contains class::TrainingPlotSplits
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for splits
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotSplits extends TrainingPlot {
	/**
	 * Selection enabled?
	 * @var bool
	 */
	protected $selecting = false;

	/**
	 * Uses standard x-axis?
	 * @var bool
	 */
	protected $useStandardXaxis = false;

	/**
	 * Demanded pace in s/km
	 * @var int
	 */
	private $demandedPace = 0;

	/**
	 * Achieved pace in s/km
	 * @var int
	 */
	private $achievedPace = 0;

	/**
	 * Labels
	 * @var array
	 */
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
		if ($this->splitsAreNotComplete()) {
			$showInactive = !$this->Training->Splits()->hasActiveLaps();
			$this->Labels = $this->Training->Splits()->distancesAsArray($showInactive);
			$this->Data   = $this->Training->Splits()->pacesAsArray($showInactive);
			$num          = count($this->Data);

			$this->demandedPace = Running::DescriptionToDemandedPace($this->Training->getComment());
			$this->achievedPace = array_sum($this->Data) / $num;

			foreach ($this->Data as $key => $val) {
				if ($num > 35)
					$this->Labels[$key] = array($key, round($this->Labels[$key], 1));
				elseif ($num >= 20)
					$this->Labels[$key] = array($key, number_format($this->Labels[$key], 1, '.', ''));
				elseif ($num > 10)
					$this->Labels[$key] = array($key, $this->Labels[$key].'k');
				else
					$this->Labels[$key] = array($key, $this->Labels[$key].' km');

				$this->Data[$key]   = $val*1000;
			}
		} else {
			$RawData = $this->Training->GpsData()->getRoundsAsFilledArray();
			$num     = count($RawData);

			foreach ($RawData as $key => $val) {
				$km = $key + 1;
				if ($num < 20) {
					$label = ($km%2 == 0 && $km > 0) ? $km.' km' : '';
				} elseif ($num < 50) {
					$label = ($km%5 == 0 && $km > 0) ? $km.' km' : '';
				} elseif ($num < 100) {
					$label = ($km%10 == 0 && $km > 0) ? $km.' km' : '';
				} else {
					$label = ($km%50 == 0 && $km > 0) ? $km.' km' : '';
				}

				$this->Labels[$key] = array($key, $label);
				$this->Data[$key]   = $val['km'] > 0 ? $val['s']*1000/$val['km'] : 0;
			}
		}

		$this->Plot->Data[] = array('label' => 'Zwischenzeiten', 'data' => $this->Data);
	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		if (!empty($this->Data)) {
			$min = Helper::floorFor(min($this->Data), 30000);
			$max = Helper::ceilFor(max($this->Data), 30000);
			$this->Plot->setYLimits(1, $min, $max, false);
		}

		$this->Plot->setYAxisTimeFormat('%M:%S');
		$this->Plot->setXLabels($this->Labels);
		$this->Plot->showBars(true);

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

	/**
	 * Splits are not complete
	 * 
	 * "Complete" means: all laps active and complete distance
	 * @return boolean
	 */
	protected function splitsAreNotComplete() {
		if ($this->Training->Splits()->areEmpty() || $this->Training->Splits()->totalDistance() <= 0)
			return false;

		if ($this->Training->Splits()->hasActiveAndInactiveLaps())
			return true;

		if (!Validator::isClose($this->Training->Splits()->totalDistance(), $this->Training->getDistance(), 2))
			return true;

		return false;
	}
}