<?php
/**
 * This file contains class::SectionSwimLane
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Swim Lane
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionSwimLane extends TrainingViewSectionTabbed {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Lanes');

		if (!$this->Context->activity()->splits()->isEmpty() && $this->Context->activity()->splits()->totalDistance() > 0) {
			$this->appendRowTabbed( new SectionLapsRowManual($this->Context), __('Manual Laps') );
		}
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return ($this->Context->swimdata()->has(\Runalyze\Model\Swimdata\Object::STROKE));
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'laps';
	}
}