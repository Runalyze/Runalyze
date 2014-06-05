<?php
/**
 * This file contains class::TraningViewIFrame
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display training data
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingViewIFrame extends TrainingView {
	/**
	 * Init sections
	 */
	protected function initSections() {
		$this->Sections[] = new SectionOverview($this->Training);
		$this->Sections[] = new SectionRouteOnlyMap($this->Training);
	}
}