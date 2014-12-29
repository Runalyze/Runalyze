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
class SectionRouteOnlyMap extends SectionRoute {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Course');

		$this->appendRow( new SectionRouteRowMap($this->Training, $this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return $this->Context->hasRoute() && $this->Context->route()->hasPositionData();
	}
}