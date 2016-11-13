<?php
/**
 * This file contains class::SectionRouteOnlyMap
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Route
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRouteOnlyElevation extends SectionRoute {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Elevation data');

        $this->appendRow( new SectionRouteRowElevation($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
        return $this->Context->activity()->elevation() > 0;
	}
}
