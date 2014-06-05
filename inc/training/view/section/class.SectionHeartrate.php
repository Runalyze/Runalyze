<?php
/**
 * This file contains class::SectionHeartrate
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Heartrate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionHeartrate extends TrainingViewSectionTabbedPlot {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Heartrate data');

		$this->appendRowTabbedPlot( new SectionHeartrateRow($this->Training) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Training->hasArrayHeartrate() || ($this->Training->getPulseAvg() > 0);
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'heartrate';
	}
}