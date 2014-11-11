<?php
/**
 * This file contains class::SectionRouteRowMap
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Leaflet;
use Runalyze\Model;

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

		if ($this->Training->hasPositionData()) {
			$Factory = new Model\Factory(SessionAccountHandler::getId());

			$Map = new Leaflet\Map('map');
			$Map->addRoute(
				new Leaflet\Activity(
					'route-'.$this->Training->id(),
					$Factory->route($this->Training->get('routeid')),
					$Factory->trackdata($this->Training->id())
				)
			);

			$this->Content = $Map->code();
		}
	}
}