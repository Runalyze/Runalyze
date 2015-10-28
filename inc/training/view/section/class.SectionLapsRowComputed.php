<?php
/**
 * This file contains class::SectionLapsRowComputed
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity;

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
		$this->Plot = new Activity\Plot\LapsComputed($this->Context);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->withShadow = true;
		$this->addInfoLink();
		$this->addTable();
	}

	/**
	 * Add: table
	 */
	protected function addTable() {
		$Table = new TableLapsComputed($this->Context);
		$this->Code = $Table->getCode();
	}

	/**
	 * Add info link
	 */
	protected function addInfoLink() {
		if (!Request::isOnSharedPage()) {
			$Linker = new Linker($this->Context->activity());
			$InfoLink = Ajax::window('<a href="'.$Linker->urlToRoundsInfo().'">'.__('More details about your laps').'</a>', 'big');

			$this->Header = HTML::info( $InfoLink );
		}
	}
}
