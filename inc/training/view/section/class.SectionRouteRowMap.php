<?php
/**
 * This file contains class::SectionRouteRowMap
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Leaflet;

/**
 * Row: Map
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRouteRowMap extends TrainingViewSectionRowFullwidth {
	/**
	 * Set content
	 */
	protected function setContent() {
		$this->id = 'training-map';

		if ($this->Context->hasRoute() && $this->Context->route()->hasPositionData()) {
			$Map = new Leaflet\Map('map-'.$this->Context->activity()->id());
			$Map->addRoute(
				new Leaflet\Activity(
					'route-'.$this->Context->activity()->id(),
					$this->Context->route(),
					$this->Context->trackdata()
				)
			);

			$this->Content = $Map->code();
		}
	}
}