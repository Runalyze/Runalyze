<?php
/**
 * This file contains class::RunningPrognosisDaniels
 * @package Runalyze\Calculations\Prognosis
 */

use Runalyze\Calculation\JD\VDOT;
use Runalyze\Calculation\Math\Bisection;
use Runalyze\Configuration;

/**
 * Class: RunningPrognosisDaniels
 * 
 * Competition prediction based on "Die Laufformel" by Jack Daniels.
 * See page 52/53 for a table.
 * 
 * An adjustment based on a value for the basic endurance can be used.
 * This adjustment is NOT based on Jack Daniels' formulas.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
class RunningPrognosisDaniels extends RunningPrognosisStrategy {
	/**
	 * VDOT
	 * @var float
	 */
	private $VDOT = 0;

	/**
	 * Adjust VDOT?
	 * @var bool
	 */
	private $ADJUST_VDOT = true;

	/**
	 * Basic endurance
	 * 
	 * Basic endurance is interpreted as a percentage of achieved (optimal)
	 * marathon training. A value of '100' represents a perfect training.
	 * The value can be greater than 100 for representing a good training for
	 * an ultramarathon.
	 * 
	 * @var int
	 */
	private $BASIC_ENDURANCE = 0;

	/**
	 * Running setup from database
	 */
	public function setupFromDatabase() {
		$this->setVDOT( Configuration::Data()->vdot() );
		$this->adjustVDOT( true );
		$this->setBasicEnduranceForAdjustment( BasicEndurance::getConst() );
	}

	/**
	 * Set VDOT
	 * @param float $VDOT
	 */
	public function setVDOT($VDOT) {
		$this->VDOT = $VDOT;
	}

	/**
	 * Adjust VDOT
	 * @param bool $booleanFlag
	 */
	public function adjustVDOT($booleanFlag = true) {
		$this->ADJUST_VDOT = $booleanFlag;
	}

	/**
	 * Set basic endurance
	 * @param float $basicEndurance
	 */
	public function setBasicEnduranceForAdjustment($basicEndurance) {
		$this->BASIC_ENDURANCE = $basicEndurance;
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance in kilometer
	 * @return float prognosis in seconds
	 */
	public function inSeconds($distance) {
		return self::prognosisFor($this->getAdjustedVDOTforDistanceIfWanted($distance), $distance);
	}

	/**
	 * Calculate prognosis for given VDOT
	 * @see \Runalyze\Calculation\JD\VDOT::formula
	 * @param $VDOTtoReach  VDOT
	 * @param $km           Distance [km]
	 * @return int          Time [s]
	 */
	public static function prognosisFor($VDOTtoReach, $km = 5) {
		if ($VDOTtoReach == 0)
			return 0;

		$Bisection = new Bisection(
			$VDOTtoReach,
			round(2*60*$km),
			round(10*60*$km),
			function($seconds) use ($km) {
				return VDOT::formula($km, $seconds);
			}
		);

		return $Bisection->findValue();
	}

	/**
	 * Get (adjusted) VDOT
	 * @param float $distance distance in km
	 * @return float (adjusted) vdot
	 */
	public function getAdjustedVDOTforDistanceIfWanted($distance) {
		if ($this->ADJUST_VDOT)
			return $this->getAdjustedVDOTforDistance($distance);

		return $this->VDOT;
	}

	/**
	 * Get adjusted VDOT
	 * 
	 * This method doesn't care if the strategy uses an adjusted VDOT or not.
	 * 
	 * @see RunningPrognosisDaniels::getAdjustmentFactor()
	 * @param float $distance distance in km
	 * @return float factor
	 */
	public function getAdjustedVDOTforDistance($distance) {
		return $this->VDOT * $this->getAdjustmentFactor($distance);
	}

	/**
	 * Get adjustment factor
	 * 
	 * Get a factor between 0 and 1 (in fact between 0.6 and 1) for adjusting
	 * the VDOT to the given distance based on used basic endurance value.
	 * 
	 * Uses <code>pow($distance, 1.23)</code> to predict the required basic endurance.
	 * 
	 * @param float $distance distance in km
	 * @return float factor
	 */
	public function getAdjustmentFactor($distance) {
		$RequiredBasicEndurance = pow($distance, 1.23);
		$BasicEnduranceFactor   = max(0, 1 - ($RequiredBasicEndurance - $this->BASIC_ENDURANCE) / 100);

		return min(1, 0.6 + 0.4*$BasicEnduranceFactor);
	}
}