<?php
/**
 * This file contains class::SectionHeartrateRow
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Heartrate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionHeartrateRow extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		$this->Plot = new TrainingPlotPulse($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addAverageHeartrate();
		$this->addMaximalHeartrate();
		$this->addCaloriesAndTrimp();

		foreach ($this->BoxedValues as &$Value)
			$Value->defineAsFloatingBlock('w50');

		// TODO: Remove this and use tabbed view as soon as zones have a plot
		$this->withShadow = true;
		if ($this->Training->hasArrayHeartrate()) {
			$this->Code .= '<p>&nbsp;</p>';

			$Table = new TableZonesHeartrate($this->Training);
			$this->Code .= $Table->getCode();
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addAverageHeartrate() {
		if ($this->Training->getPulseAvg() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getPulseAvg(), 'bpm', __('&oslash; Heartrate'));
			$this->BoxedValues[] = new BoxedValue(Running::PulseInPercent($this->Training->getPulseAvg()), '&#37;', __('&oslash; Heartrate'));
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addMaximalHeartrate() {
		if ($this->Training->getPulseMax() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getPulseMax(), 'bpm', __('max. Heartrate'));
			$this->BoxedValues[] = new BoxedValue(Running::PulseInPercent($this->Training->getPulseMax()), '&#37;', __('max. Heartrate'));
		}
	}

	/**
	 * Add: calories/trimp
	 */
	protected function addCaloriesAndTrimp() {
		if ($this->Training->getCalories() > 0 || $this->Training->getTrimp() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getCalories(), 'kcal', __('Calories'));
			$this->BoxedValues[] = new BoxedValue($this->Training->getTrimp(), '', __('TRIMP'));
		}
	}
}