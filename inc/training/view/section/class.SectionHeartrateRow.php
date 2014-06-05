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
class SectionHeartrateRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Set plot
	 */
	protected function setRightContent() {
		$this->addRightContent('plot', __('Heartrate plot'), new TrainingPlotPulse($this->Training));

		if ($this->Training->hasArrayHeartrate()) {
			$Table = new TableZonesHeartrate($this->Training);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You\'ll be soon able to configure your own zones.') );

			$this->addRightContent('zones', __('Heartrate zones'), $Code);
		}
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