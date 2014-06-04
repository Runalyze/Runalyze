<?php
/**
 * This file contains class::SectionHeartrateRowZones
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Heartrate zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionHeartrateRowZones extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		//$this->Plot = new TrainingPlotPulse($this->Training);
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
		if ($this->Training->hasArrayHeartrate()) {
			$Table = new TableZonesHeartrate($this->Training);
			$this->Code = $Table->getCode();
		}
	}
}