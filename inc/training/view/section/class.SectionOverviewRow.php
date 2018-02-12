<?php
/**
 * This file contains class::SectionOverviewRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Box;

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
			new Box\Distance($this->Context),
			new BoxedValue($this->Context->dataview()->duration()->string(), '', __('Time')),
			new BoxedValue($this->Context->dataview()->elapsedTime(), '', __('Elapsed time')),
			new Box\Pace($this->Context),
			new BoxedValue(Helper::Unknown($this->Context->activity()->hrAvg(), '-'), 'bpm', __('avg.').' '.__('Heart rate')),
			new BoxedValue(Helper::Unknown($this->Context->activity()->hrMax(), '-'), 'bpm', __('max.').' '.__('Heart rate')),
			new Box\Energy($this->Context),
			new BoxedValue(Helper::Unknown($this->Context->dataview()->vo2max()->value(), '-'), '', 'VO<sub>2</sub>max', $this->Context->dataview()->effectiveVO2maxIcon(), 'vo2max'),
			new Box\Trimp($this->Context),
			new Box\Elevation($this->Context)
		);
	}
}
