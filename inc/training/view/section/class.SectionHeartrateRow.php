<?php
/**
 * This file contains class::SectionHeartrateRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity;
use Runalyze\View\Activity\Box;
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
		$this->addRightContent('plot', __('Heartrate plot'), new Activity\Plot\Heartrate($this->Context));

		if (
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::HEARTRATE) &&
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::TIME)
		) {
			$Table = new TableZonesHeartrate($this->Context);
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
		$this->addFitTrainingEffect();

		foreach ($this->BoxedValues as &$Value)
			$Value->defineAsFloatingBlock('w50');
	}

	/**
	 * Add: average heartrate
	 */
	protected function addAverageHeartrate() {
		if ($this->Context->activity()->hrAvg() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrAvg()->inBPM(), 'bpm', __('avg.').' '.__('Heart rate'));

			if ($this->Context->dataview()->hrMax()->canShowInHRmax()) {
				$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrAvg()->inPercent(), '&#37;', __('avg.').' '.__('Heart rate'));
			}
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addMaximalHeartrate() {
		if ($this->Context->activity()->hrMax() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrMax()->inBPM(), 'bpm', __('max.').' '.__('Heart rate'));

			if ($this->Context->dataview()->hrMax()->canShowInHRmax()) {
				$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrMax()->inPercent(), '&#37;', __('max.').' '.__('Heart rate'));
			}
		}
	}

	/**
	 * Add: calories/trimp
	 */
	protected function addCaloriesAndTrimp() {
		if ($this->Context->activity()->calories() > 0 || $this->Context->activity()->trimp() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->calories(), 'kcal', __('Calories'));
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->trimp(), '', __('TRIMP'));
		}
	}
	
	/**
	 * Add: FitTrainingEffect
	 */
	protected function addFitTrainingEffect() {
	    if ($this->Context->activity()->fitTrainingEffect() > 0) {
		$this->BoxedValues[] = new Box\FitTrainingEffect($this->Context);
	    }
	}
}
