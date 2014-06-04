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
class SectionHeartrate extends TrainingViewSectionTabbed {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Heartrate data');

		$this->appendRowTabbed( new SectionHeartrateRow($this->Training), __('Data with plot') );

		// TODO: Use tabbed view as soon as a plot for zones is available
		//if ($this->Training->hasArrayHeartrate())
		//	$this->appendRowTabbed( new SectionHeartrateRowZones($this->Training), __('Heartrate zones') );
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