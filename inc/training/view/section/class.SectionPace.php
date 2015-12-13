<?php
/**
 * This file contains class::SectionPace
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Pace
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionPace extends TrainingViewSectionTabbedPlot {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Pace data');

		$this->appendRowTabbedPlot( new SectionPaceRow($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return ($this->Context->activity()->distance() > 0 && $this->Context->activity()->duration() > 0) || $this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::PACE);
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'pace';
	}
}