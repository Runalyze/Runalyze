<?php
/**
 * This file contains class::SectionSwimLane
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Section: Swim Lane
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Swimdata;
class SectionSwimLane extends TrainingViewSectionTabbedPlot {
        
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		
                $this->appendRowTabbedPlot(new SectionSwimLaneRow($this->Context, __('Lanes')));
                $this->Header = __('Lanes');

	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return ($this->Context->swimdata()->has(Swimdata\Entity::STROKE));
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'swim-laps';
	}
}