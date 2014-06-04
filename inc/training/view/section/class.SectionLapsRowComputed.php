<?php
/**
 * This file contains class::SectionLapsRowComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Laps (computed)
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionLapsRowComputed extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		$this->Plot = new TrainingPlotLapsComputed($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->withShadow = true;

		$this->addTable();

		$this->addInfoLink();
	}

	/**
	 * Add: table
	 */
	protected function addTable() {
		if ($this->Training->hasArrayPace()) {
			$Table = new TableLapsComputed($this->Training);
			$this->Code = $Table->getCode();
		}
	}

	/**
	 * Add info link
	 */
	protected function addInfoLink() {
		if ($this->Training->hasArrayPace()) {
			$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToRoundsInfo().'">'.__('More details about your laps').'</a>', 'normal');

			$this->Content = HTML::info( $InfoLink );
		}
	}
}