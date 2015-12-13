<?php
/**
 * This file contains class::SectionLaps
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;

/**
 * Section: Laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionLaps extends TrainingViewSectionTabbed {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Laps');

		if (!$this->Context->activity()->splits()->isEmpty() && $this->Context->activity()->splits()->totalDistance() > 0) {
			$this->appendRowTabbed( new SectionLapsRowManual($this->Context), __('Manual Laps') );
		}

		if ($this->Context->trackdata()->has(Trackdata\Entity::DISTANCE) && $this->Context->trackdata()->has(Trackdata\Entity::TIME)) {
			$this->appendRowTabbed( new SectionLapsRowComputed($this->Context), __('Computed Laps') );
		}
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return (!$this->Context->activity()->splits()->isEmpty() && $this->Context->activity()->splits()->totalDistance() > 0)
			|| ($this->Context->trackdata()->has(Trackdata\Entity::DISTANCE) && $this->Context->trackdata()->has(Trackdata\Entity::TIME));
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'laps';
	}
}