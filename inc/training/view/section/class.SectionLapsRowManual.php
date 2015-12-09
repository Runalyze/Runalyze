<?php
/**
 * This file contains class::SectionLapsRowManual
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Linker;

/**
 * Row: Laps (manual)
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionLapsRowManual extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		$this->Plot = new Activity\Plot\LapsManual($this->Context);
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
		$Table = new TableLaps($this->Context);
		$this->Code = $Table->getCode();
	}

	/**
	 * Add info link
	 */
	protected function addInfoLink() {
		if ($this->Context->trackdata()->has(Trackdata\Entity::DISTANCE) && $this->Context->trackdata()->has(Trackdata\Entity::TIME)) {
			if (!Request::isOnSharedPage()) {
				$Linker = new Linker($this->Context->activity());
				$InfoLink = Ajax::window('<a href="'.$Linker->urlToRoundsInfo().'">'.__('More details about your laps').'</a>', 'big');

				$this->Header = HTML::info( $InfoLink );
			}
		}
	}
}
