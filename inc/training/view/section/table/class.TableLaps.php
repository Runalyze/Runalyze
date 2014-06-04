<?php
/**
 * This file contains class::TableLaps
 * @package Runalyze\DataObjects\Training\View\Section
 */
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
		$Splits = $this->Training->Splits();
		$SplitsView = new SplitsView($Splits);
		$SplitsView->setDemandedPace( Running::DescriptionToDemandedPace($this->Training->getComment()) );

		if ($this->Training->Type()->isCompetition() && $this->Training->hasPositionData())
			$SplitsView->setHalfsOfCompetition( $this->Training->GpsData()->getRoundsAsFilledArray($this->Training->GpsData()->getTotalDistance()/2) );

		$this->Code = $SplitsView->getCode();
	}
}