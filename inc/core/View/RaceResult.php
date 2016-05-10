<?php
/**
 * This file contains class::RaceResult
 * @package Runalyze\View
 */

namespace Runalyze\View;
use Runalyze\Model\RaceResult as ModelRaceResult;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use SportFactory;
use Runalyze\Activity\Pace;


/**
 * RaceResult
 * @author Hannes Christiansen
 * @author Michael pohl
 * @package Runalyze\View
 */
class RaceResult {
    
	/**
	 * RaceResult
	 * @var \Runalyze\Model\RaceResult\Entity
	 */
	protected $RaceResult = null;
    
	/**
	 * Construct Dataviewer for RaceResults
	 * @param string $text
	 */
	public function __construct(RaceResult\Entity $Raceresult) {
		$this->RaceResult = $Raceresult;
		print_r($RaceResult);
	}
	
	/**
	 * Get official distance
	 * @return string
	 */
	public function officialDistance($decimals = null) {
		if (is_null($decimals)) {
			$decimals = Configuration::ActivityView()->decimals();
		}

		if ($this->RaceResult->officialDistance() > 0) {
			return Distance::format($this->RaceResult->officialDistance(), true, $decimals);
		}

		return '';
	}
	
	/**
	 * Official Duration
	 * @return \Runalyze\Activity\Duration
	 */
	public function officialTime() {
		return new Duration($this->RaceResult->officialTime());
	}
	
	/**
	 * Get a string for the speed depending on sportid
	 * @return \Runalyze\Activity\Pace
	 */
	public function pace($sportid) {
		return new Pace($this->RaceResult->officialTime(), $this->RaceResult->officialDistance(), SportFactory::getSpeedUnitFor($sportid));
	}
	
	/**
	 * Total place with tooltip "of x"
	 * @return string
	 */
	public function placementTotalWithTooltip() {
	    $placeTotal = $this->RaceResult->placeTotal();
	    $participantsTotal = $this->RaceResult->participantsTotal();
	    
	    if (is_null($placeTotal) || !$participantsTotal) {
	        
	        return \Helper::Unknown($placeTotal);
	    }
	    
		$TooltipTotal = new Tooltip(__('of').' '. $participantsTotal.'. '.__('overall'));
		$TooltipTotal->wrapAround($placeTotal);
		
		return $placeTotal.'.';
	}
	
	/**
	 * Ageclass place with tooltip "of x"
	 * @return string
	 */
	public function placementAgeClassWithTooltip() {
	    $placeAgeClass = $this->RaceResult->placeAgeclass();
	    $participantsAgeClass = $this->RaceResult->participantsAgeclass();
	    
	    if (is_null($placeAgeClass) || !$participantsAgeClass) {
	        
	        return \Helper::Unknown($placeAgeClass);
	    }
	    //($placeAgeClass/$participantsAgeClass*100)
		$TooltipAgeClass = new Tooltip(__('of').' '. $participantsAgeClass.'. '.__('age class participants'));
		$TooltipAgeClass->wrapAround($placeAgeClass);
		
		return $placeAgeClass.'.';
	}
	
	/**
	 * Gender place with tooltip "of x"
	 * @return string
	 */
	public function placementGenderWithTooltip() {
	    $placeGender = $this->RaceResult->placeGender();
	    $participantsGender = $this->RaceResult->participantsGender();

	    if (is_null($placeGender) || !$participantsGender) {
	        
	        return \Helper::Unknown($placeGender);
	    }
	    //($placeGender/$participantsGender*100)
		$TooltipGender = new Tooltip(__('of').' '. $participantsGender.'. '.__('age class participants'));
		$TooltipGender->wrapAround($placeGender);
		
		return $placeGender.'.';
	}
	
	/**
	 * Total place with "of" age class overall
	 * @return string
	 */
	public function placementTotalWithParticipants() {
	    $placeTotal = $this->RaceResult->placeTotal();
	    $participantsTotal = $this->RaceResult->participantsTotal();
	    
	    if (!$participantsTotal && is_numeric($placeTotal)) {
	        return $placeTotal.'.';
	    } elseif (!is_numeric($placeTotal)) {
	        return \Helper::Unknown($placeTotal);
	    }
	    
        $placeTotal =  $placeTotal.'. '.__('of').' '. $participantsTotal.'.';
		
		return $placeTotal;
	}
	
	/**
	 * Ageclass place with "of" age class participants
	 * @return string
	 */
	public function placementAgeClassWithParticipants() {
	    $placeAgeClass = $this->RaceResult->placeAgeclass();
	    $participantsAgeClass = $this->RaceResult->participantsAgeclass();
	    
	    if (!$participantsAgeClass && is_numeric($placeAgeClass)) {
	        return $placeAgeClass.'.';
	    } elseif (!is_numeric($placeAgeClass)) {
	        return \Helper::Unknown($placeAgeClass);
	    }

        $placeAgeClass =  $placeAgeClass.'. '.__('of').' '. $participantsAgeClass.'.';
		
		return $placeAgeClass;
	}
	
	/**
	 * Gender place with "of" age class gender
	 * @return string
	 */
	public function placementGenderWithParticipants() {
	    $placeGender = $this->RaceResult->placeGender();
	    $participantsGender = $this->RaceResult->participantsGender();

	    if (!$participantsGender && is_numeric($placeGender)) {
	        return $placeGender.'.';
	    } elseif (!is_numeric($placeGender)) {
	        return \Helper::Unknown($placeGender);
	    }
	    
	    
        $placeGender =  $placeGender.'. '.__('of').' '. $participantsGender.'.';
		
		return $placeGender;
	}
	
}