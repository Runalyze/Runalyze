<?php
/**
 * This file contains class::TableLaps
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Splits;
use Runalyze\Model\Trackdata;
use Runalyze\Configuration;
use Runalyze\Util\StringReader;

/**
 * Table: laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class TableLaps extends TableLapsAbstract {
	/**
	 * Set code
	 */
	protected function setCode() {
		$Reader = new StringReader($this->Context->activity()->comment());

		$Splits = $this->Context->activity()->splits();
		$SplitsView = new Splits\Table($Splits, $this->Context->dataview()->pace()->unitEnum());
		$SplitsView->setDemandedPace($Reader->findDemandedPace());

		if ($this->Context->trackdata()->has(Trackdata\Entity::DISTANCE)
			&& $this->Context->activity()->typeid() == Configuration::General()->competitionType()
		) {
			$SplitsView->setHalfsOfCompetition($this->computeHalfs());
		}

		$this->Code = $SplitsView->code();
	}

	/**
	 * @return array
	 */
	protected function computeHalfs() {
		$Halfs = array();

		$Loop = new Trackdata\Loop($this->Context->trackdata());
		$Loop->moveToDistance( $this->Context->trackdata()->totalDistance()/2 );
		$Halfs[] = $this->halfFromLoop($Loop);

		$Loop->moveToDistance( $this->Context->trackdata()->totalDistance() );
		$Halfs[] = $this->halfFromLoop($Loop);

		return $Halfs;
	}

	/**
	 * @param \Runalyze\Model\Trackdata\Loop $Loop
	 * @return array
	 */
	protected function halfFromLoop(Trackdata\Loop $Loop) {
		return array(
			's' => $Loop->difference(Trackdata\Entity::TIME),
			'km' => $Loop->difference(Trackdata\Entity::DISTANCE)
		);
	}
}