<?php
/**
 * This file contains class::SectionRunningDynamics
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity;
use Runalyze\Model\Trackdata;

/**
 * Section: Running dynamics
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRunningDynamics extends TrainingViewSectionTabbedPlot {
	/**
	 * Set header and rows
	 */
	protected function setHeaderAndRows() {
		$this->Header = __('Running Dynamics');

		$this->appendRowTabbedPlot( new SectionRunningDynamicsRow($this->Context) );
	}

	/**
	 * Has the training all required data?
	 * @return bool
	 */
	protected function hasRequiredData() {
		return (
			$this->Context->trackdata()->has(Trackdata\Entity::CADENCE) ||
			$this->Context->trackdata()->has(Trackdata\Entity::VERTICAL_OSCILLATION) ||
			$this->Context->trackdata()->has(Trackdata\Entity::GROUNDCONTACT)
		);
	}

	/**
	 * CSS-ID
	 * @return string
	 */
	protected function cssId() {
		return 'runningdynamics';
	}
}