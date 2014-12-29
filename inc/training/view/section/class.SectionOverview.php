<?php
/**
 * This file contains class::SectionOverview
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Overview
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionOverview extends TrainingViewSection {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Overview');

		$this->appendRow( new SectionOverviewRow($this->Training, $this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return true;
	}
}