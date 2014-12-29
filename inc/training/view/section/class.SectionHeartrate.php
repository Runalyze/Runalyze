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

		$this->appendRowTabbedPlot( new SectionHeartrateRow($this->Training, $this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Context->activity()->hrAvg() > 0;
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'heartrate';
	}
}