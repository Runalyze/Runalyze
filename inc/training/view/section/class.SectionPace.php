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
class SectionPace extends TrainingViewSectionTabbed {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Pace data');

		$this->appendRowTabbed( new SectionPaceRow($this->Training), __('Data with plot') );

		// TODO: Use tabbed view as soon as a plot for zones is available
		//if ($this->Training->hasArrayHeartrate())
		//	$this->appendRowTabbed( new SectionPaceRowZones($this->Training), __('Pace zones') );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Training->hasArrayPace() || ($this->Training->getDistance() > 0 && $this->Training->getTimeInSeconds() > 0);
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'pace';
	}
}