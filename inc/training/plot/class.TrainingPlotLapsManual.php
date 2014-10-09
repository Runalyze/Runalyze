<?php
/**
 * This file contains class::TrainingPlotLapsManual
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot for manual laps
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotLapsManual extends TrainingPlotLaps {
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
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'laps_manual';
		$this->title = __('Manual Laps');
	}

	/**
	 * Init data
	 */
	protected function initData() {
		if ($this->Training->Splits()->areEmpty() || $this->Training->Splits()->totalDistance() <= 0) {
			$this->Plot->raiseError( __('There are no manual laps.') );
			return;
		}

		$showInactive = !$this->Training->Splits()->hasActiveLaps(2);
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

		$this->Plot->Data[] = array('label' => $this->title, 'data' => $this->Data);
	}

	/**
	 * Ad annotations
	 */
	protected function addAnnotations() {
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