<?php
/**
 * This file contains the class::JD
 */

define('VDOT_CORRECTOR', JD::calculateVDOTcorrector());
define('VDOT_FORM', JD::calculateVDOTform());

/**
 * Class for calculating based on "Jack Daniels' Running Formula"
 * @defines   VDOT_CORRECTOR   float   Factor to correct the user-specific VDOT
 * @defines   VDOT_FORM        float   Actual VDOT-value
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 * @uses class::Helper
 * @uses HF_MAX
 * @uses WK_TYPID
 *
 * Last modified 2011/03/05 13:00 by Hannes Christiansen
 */
class JD {
	/**
	 * This class contains only static methods
	 */
	private function __construct() {}
	private function __destruct() {}

	/**
	 * Calculates VDOT from an official run
	 * @param $km          Distance [km]
	 * @param $time_in_s   Time [s]
	 * @return float       VDOT 
	 */
	public static function Competition2VDOT($km, $time_in_s) {
		if ($km == 0 || $time_in_s == 0)
			return false;

		$time_in_min = $time_in_s/60;
		$m = $km*1000;
		return ( -4.6+0.182258*$m / $time_in_min + 0.000104*pow($m/$time_in_min,2) )
			/ ( 0.8 + 0.1894393*exp(-0.012778*$time_in_min) + 0.2989558*exp(-0.1932605*$time_in_min) );
	}

	/**
	 * Calculates speed in m/min for 100% of VDOT
	 * @param $VDOT    VDOT
	 * @return float   Speed [m/min]
	 */
	public static function VDOT2v($VDOT) {
		return 173.154 + 4.116*($VDOT-29);
	}

	/**
	 * Calculates heart-frequence for a given percentage of VDOT
	 * @param $pVDOT   VDOT [%]
	 * @return float   HFmax [%]
	 */
	public static function pVDOT2pHF($pVDOT) {
		return ($pVDOT+0.2812)/1.2812;
	}

	/**
	 * Calculates percentage of VDOT for a given percentage of HFmax
	 * @param $pHF     HFmax [%]
	 * @return float   VDOT [%]
	 */
	public static function pHF2pVDOT($pHF) {
		return 1.2812*$pHF-0.2812;
	}

	/**
	 * Calculates pace from speed
	 * @uses Helper::Time
	 * @param $v        Speed [m/min]
	 * @return string   1:23 (Pace [min/km])
	 */
	public static function v2Pace($v) {
		return Helper::Time(60*1000/$v);
	}

	/**
	 * Calculates pace from speed
	 * @param $pace_in_s   Pace [s/km]
	 * @return float       Speed [m/min
	 */
	public static function Pace2v($pace_in_s) {
		return $pace_in_s/1000/60;
	}

	/**
	 * Calculates VDOT for a training using self::VDOTcorrector
	 * @uses HF_MAX
	 * @param $training_id
	 */
	public static function Training2VDOT($training_id) {
		$training = Mysql::getInstance()->fetch('SELECT `sportid`, `distanz`, `dauer`, `puls` FROM `ltb_training` WHERE `id`='.$training_id.' LIMIT 1');

		return ($training['puls'] != 0 && $training['sportid'] == RUNNINGSPORT)
			? round( VDOT_CORRECTOR * self::Competition2VDOT($training['distanz'], $training['dauer']) / (self::pHF2pVDOT($training['puls']/HF_MAX) ), 2)
			: 0;
	}

	/**
	 * Calculates a prognosis for a given distance based an an actual VDOT
	 * @param $VDOTactual   VDOT
	 * @param $distance     Distance [km]
	 * @return int          Time [s]
	 */
	public static function CompetitionPrognosis($VDOTactual, $distance = 5) {
		$dauer = 60*$distance;
		$VDOT_low = 150;
		while (true) {
			$dauer++;
			$VDOT_high = $VDOT_low;
			$VDOT_low = self::Competition2VDOT($distance, $dauer);
			if ($VDOT_high > $VDOTactual && $VDOTactual > $VDOT_low)
				break;
		}
		return $dauer;
	}

	/**
	 * Calculates an actual VDOT value based on the trainings in the last 30 days
	 * @return float   VDOT
	 */
	public static function calculateVDOTform() {
		// TODO Speed up this procedure:
		// + Don't call each training for its own in Training2VDOT
		$VDOT_form = 0;
		$trainings = Mysql::getInstance()->fetch('SELECT `id` FROM `ltb_training` WHERE `sportid`='.RUNNINGSPORT.' && `puls`!=0 && `time`>'.(time()-30*DAY_IN_S));
		foreach ($trainings as $training)
			$VDOT_form += self::Training2VDOT($training['id']);

		return round($VDOT_form/count($trainings), 5);
	}

	/**
	 * Calculates a factor for correcting the user-specific VDOT-value
	 * @uses Helper::Bestzeit
	 * @uses HF_MAX
	 * @uses WK_TYPID
	 * @return float   VDOTcorrectionfactor
	 */
	public static function calculateVDOTcorrector() {
		// Find best VDOT-value in competition
		$VDOT_top = 0;
		$VDOT_top_dist = 0;
		$distances = array(3, 5, 10, 21.1, 42.2);
		foreach ($distances as $dist) {
			$dist_PB = Helper::PersonalBest($dist, true);
			if ($dist_PB != 0) {
				$dist_VDOT = self::Competition2VDOT($dist, $dist_PB);
				if ($dist_VDOT > $VDOT_top
					&& Mysql::getInstance()->num('SELECT 1 FROM `ltb_training` WHERE `typid`='.WK_TYPID.' AND `puls`!=0 AND `distanz`="'.$dist.'" LIMIT 1') > 0) {
					$VDOT_top = $dist_VDOT;
					$VDOT_top_dist = $dist;
				}
			}
		}
		// Find best VDOT-value in training
		$VDOT_top_dat = Mysql::getInstance()->fetch('SELECT `puls`, `dauer` FROM `ltb_training` WHERE `distanz`='.$VDOT_top_dist.' AND `puls`!=0 AND `typid`='.WK_TYPID.' ORDER BY `dauer` ASC LIMIT 1');
		$VDOT_max = self::Competition2VDOT($VDOT_top_dist, $VDOT_top_dat['dauer'])
			/ self::pHF2pVDOT($VDOT_top_dat['puls'] / HF_MAX);
	
		return $VDOT_top / $VDOT_max;
	}
}
?>