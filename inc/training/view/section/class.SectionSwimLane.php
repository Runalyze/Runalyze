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
class SectionSwimLane extends TrainingViewSectionTabbed {
        
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Lanes');
                $this->appendRowTabbed(new SectionSwimLaneRow($this->Context, __('Lanes')));

	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return ($this->Context->swimdata()->has(Swimdata\Object::STROKE));
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'laps';
	}
}