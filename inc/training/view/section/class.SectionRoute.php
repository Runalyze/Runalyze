<?php
/**
 * This file contains class::SectionRoute
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Route
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRoute extends TrainingViewSection {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Course and elevation data');

		$this->appendRow( new SectionRouteRowElevation($this->Context) );
		$this->appendRow( new SectionRouteRowMap($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Context->activity()->elevation() > 0 || $this->Context->hasRoute();
	}
}
