<?php
/**
 * This file contains class::SectionLapsRowManual
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;
use Runalyze\Model\Swimdata;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Linker;

/**
 * Row: Laps (manual)
 *
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionSwimLaneRow extends TrainingViewSectionRow {
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
			$InfoLink = Ajax::window('<a href="'.$Linker->urlToRoundsInfo().'">'.__('More details about your laps').'</a>', 'normal');

			$this->Header = HTML::info( $InfoLink );
		}
	}
}
