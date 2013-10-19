<?php
/**
 * This file contains class::RoundsSplits
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display splits
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class RoundsSplits extends RoundsAbstract {
	/**
	 * Get key
	 * @return string
	 */
	public function key() {
		return 'rounds-splits';
	}

	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return 'Rundenzeiten';
	}

	/**
	 * Display
	 */
	public function display() {
		$Splits = $this->Training->Splits();
		$SplitsView = new SplitsView($Splits);
		$SplitsView->setDemandedPace( Running::DescriptionToDemandedPace($this->Training->getComment()) );

		if ($this->Training->Type()->isCompetition() && $this->Training->hasPositionData())
			$SplitsView->setHalfsOfCompetition( $this->Training->GpsData()->getRoundsAsFilledArray($this->Training->GpsData()->getTotalDistance()/2) );

		$SplitsView->display();
	}
}