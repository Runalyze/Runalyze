<?php
/**
 * This file contains class::SectionComposite
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Composite
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionComposite extends TrainingViewSectionTabbedPlot {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Composite data');

		$this->appendRowTabbedPlot( new SectionCompositeRow($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return true;
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'composite';
	}
}