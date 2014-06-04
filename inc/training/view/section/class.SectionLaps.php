<?php
/**
 * This file contains class::SectionLaps
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionLaps extends TrainingViewSectionTabbed {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Laps');

		if (!$this->Training->Splits()->areEmpty() && $this->Training->Splits()->totalDistance() > 0)
			$this->appendRowTabbed( new SectionLapsRowManual($this->Training), __('Manual Laps') );

		if ($this->Training->hasArrayPace())
			$this->appendRowTabbed( new SectionLapsRowComputed($this->Training), __('Computed Laps') );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return (!$this->Training->Splits()->areEmpty() && $this->Training->Splits()->totalDistance() > 0) || $this->Training->hasArrayPace();
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'laps';
	}
}