<?php
/**
 * This file contains class::SectionHRV
 * @package Runalyze\DataObjects\Training\View\Section
 */

/**
 * Section: HRV
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionHRV extends TrainingViewSectionTabbedPlot {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Heart rate variability');

		$this->appendRowTabbedPlot( new SectionHRVRow($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return (
			$this->Context->hasHRV()
		);
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'hrv';
	}
}