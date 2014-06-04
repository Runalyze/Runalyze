<?php
/**
 * This file contains class::SectionOverviewRow
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Overview
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionOverviewRow extends TrainingViewSectionRowFullwidth {
	/**
	 * Boxed values
	 * @var BoxedValue[]
	 */
	protected $BoxedValues = array();

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->id = 'training-overview';

		$this->setBoxedValues();
		$this->setBoxedValuesToContent();
	}

	/**
	 * Set boxed values to content
	 */
	protected function setBoxedValuesToContent() {
		$NumberOfBoxes = count($this->BoxedValues);
		$ValuesString = '';
		foreach ($this->BoxedValues as &$Value) {
			$Value->defineAsFloatingBlockWithFixedWidth($NumberOfBoxes);
			$ValuesString .= $Value->getCode();
		}

		$this->Content = BoxedValue::getWrappedValues($ValuesString);
	}

	/**
	 * Set boxed values
	 */
	protected function setBoxedValues() {	
		$this->BoxedValues = array(
			new BoxedValue(Helper::Unknown($this->Training->getDistance(), '-.--'), 'km', __('Distance')),
			new BoxedValue($this->Training->DataView()->getTimeString(), '', __('Time')),
			new BoxedValue($this->Training->DataView()->getElapsedTimeString(), '', __('Elapsed time')),
			new BoxedValue($this->Training->getPace(), '/km', __('Pace')),
			new BoxedValue(Helper::Unknown($this->Training->getPulseAvg(), '-'), 'bpm', __('&oslash; Heartrate')),
			new BoxedValue(Helper::Unknown($this->Training->getPulseMax(), '-'), 'bpm', __('max. Heartrate')),
			new BoxedValue($this->Training->getCalories(), 'kcal', __('Calories')),
			new BoxedValue(Helper::Unknown($this->Training->getCurrentlyUsedVdot(), '-'), '', __('VDOT'), $this->Training->DataView()->getVDOTicon()),
			new BoxedValue($this->Training->getTrimp(), '', __('TRIMP')),
			new BoxedValue(Helper::Unknown($this->Training->getElevation(), '-'), 'm', __('Elevation'))
		);
	}
}