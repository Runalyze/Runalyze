<?php
/**
 * This file contains class::SectionPaceRowZones
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Pace zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionPaceRowZones extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		//$this->Plot = new TrainingPlotPace($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->withShadow = true;

		$this->addTable();
	}

	/**
	 * Add: table
	 */
	protected function addTable() {
		if ($this->Training->hasArrayPace()) {
			$Table = new TableZonesPace($this->Training);
			$this->Code = $Table->getCode();
		}
	}
}