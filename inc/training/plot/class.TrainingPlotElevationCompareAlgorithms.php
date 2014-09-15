<?php
/**
 * This file contains class::TrainingPlotElevationCompareAlgorithms
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for comparing elevation algorithms
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotElevationCompareAlgorithms extends TrainingPlotElevation {
	/**
	 * Is selection-mode enabled?
	 * @var boolean
	 */
	protected $selecting = false;

	/**
	 * Elevation calculator
	 * @var ElevationCalculator
	 */
	protected $Calculator = null;

	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return true;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'elevation_algorithms';
		$this->title = __('Elevation algorithms');
	}

	/**
	 * Display without class 'training-chart'
	 */
	public function displayAsSinglePlot() {
		$this->Plot->clearAnnotations();

		echo Plot::getInnerDivFor($this->getCSSid(), $this->width, $this->height, false, '');
		$this->Plot->outputJavaScript();
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->Calculator = new ElevationCalculator($this->Training->getArrayAltitude());

		if ($this->Training->elevationWasCorrected() || !$this->Training->GpsData()->hasElevationDataOriginal()) {
			$this->Data = $this->constructPlotDataFor(ElevationMethod::NONE, 0);
			$this->Plot->Data[] = array('label' => __('corrected'), 'color' => 'rgba(227,217,187,0.5)', 'data' => $this->Data);
		}

		if ($this->Training->GpsData()->hasElevationDataOriginal()) {
			$this->Calculator = new ElevationCalculator($this->Training->getArrayAltitudeOriginal());

			$this->Plot->Data[] = array(
				'label'	=> __('Original data'),
				'color'	=> '#CCC',
				'data'	=> $this->constructPlotDataFor(ElevationMethod::NONE, 0)
			);

			if (count($this->Plot->Data) == 1) {
				$this->Plot->Data[0]['color'] = 'rgba(227,217,187,0.5)';
				$this->Data = $this->Plot->Data[0]['data'];
			}

			$this->Calculator = new ElevationCalculator($this->Training->getArrayAltitude());
		}

		$this->Plot->Data[] = array(
			'label'	=> __('Treshold'),
			'color'	=> '#008',
			'data'	=> $this->constructPlotDataFor(ElevationMethod::TRESHOLD),
			'curvedLines' => array('apply' => false)
		);

		$this->Plot->Data[] = array(
			'label'	=> __('Douglas-Peucker'),
			'color'	=> '#800',
			'data'	=> $this->constructPlotDataFor(ElevationMethod::DOUGLAS_PEUCKER),
			'curvedLines' => array('apply' => false)
		);
	}

	/**
	 * Construct plot data
	 * @param enum $algorithm
	 * @param int $treshold
	 * @return array
	 */
	protected function constructPlotDataFor($algorithm, $treshold = false) {
		$Method = new ElevationMethod();
		$Method->set($algorithm);

		if ($treshold === false) {
			$treshold = Configuration::ActivityView()->elevationMinDiff();
		}

		$this->Calculator->setMethod($Method);
		$this->Calculator->setTreshold($treshold);
		$this->Calculator->calculateElevation();

		$i = 0;
		$Points    = $this->Calculator->getElevationPointsWeeded();
		$Indices   = $this->Calculator->getIndicesOfElevationPointsWeeded();
		$Distances = $this->Training->getArrayDistance();

		foreach ($Indices as $i => $index) {
			if ($index >= count($Distances))
				$index = count($Distances)-1;

			$Data[(string)$Distances[$index]] = $Points[$i];
		}

		return $Data;
	}
}