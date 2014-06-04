<?php
/**
 * This file contains class::SectionRouteRowMap
 * @package Runalyze\DataObjects\Training\View\Section
 */
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
			$Map = new LeafletMap('map');
			$Map->addRoute( new LeafletTrainingRoute('route-'.$this->Training->id(), $this->Training->GpsData()) );

			$this->Content = $Map->getCode();
		}
	}
}