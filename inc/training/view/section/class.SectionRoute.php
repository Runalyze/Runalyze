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

		$this->appendRow( new SectionRouteRowElevation($this->Training) );
		$this->appendRow( new SectionRouteRowMap($this->Training) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Training->hasArrayAltitude() || strlen($this->Training->getRoute()) > 0;
	}
}