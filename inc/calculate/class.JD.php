<?php
/**
 * This file contains the class::JD
 */
/**
 * Number of days to be used for calculating VDOT-form
 * @var int
 */
define('VDOT_DAYS', CONF_VDOT_DAYS);

/**
 * VDOT-corrector is used to correct the raw VDOT-value to user-specific values
 * @var double
 */
define('VDOT_CORRECTOR', JD::getVDOTcorrector());

/**
 * The actual (corrected) VDOT-value based on last trainings
 * @var double
 */
define('VDOT_FORM', JD::getConstVDOTform());

/**
 * Basic endurance as percentage
 * @const BASIC_ENDURANCE
 */
define('BASIC_ENDURANCE', Running::BasicEndurance(true));

/**
 * Class for calculating based on "Jack Daniels' Running Formula"
 * @author Hannes Christiansen <mail@laufhannes.de>
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

		if ($m / $time_in_min < 50 || $m / $time_in_min > 1000)
			return false;
	
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
	 * @uses Time::toString
	 * @param $v        Speed [m/min]
	 * @return string   1:23 (Pace [min/km])
	 */
	public static function v2Pace($v) {
		return Time::toString(round(60*1000/$v));
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
	 * Corrects VDOT if VDOT-corrector is enabled
	 * @param double $VDOT
	 * @return double
	 */
	public static function correctVDOT($VDOT) {
		if (CONF_JD_USE_VDOT_CORRECTOR)
			return VDOT_CORRECTOR*$VDOT;

		return $VDOT;
	}

	/**
	 * Calculates VDOT for a training (without correction!)
	 * @uses HF_MAX
	 * @param int $training_id
	 * @param array $training [optional]
	 * @return double
	 */
	public static function Training2VDOT($training_id, $training = array()) {
		if (!isset($training['sportid']) || !isset($training['distance']) || !isset($training['s']) || !isset($training['pulse_avg']))
			$training = Mysql::getInstance()->fetchSingle('SELECT `sportid`, `distance`, `s`, `pulse_avg` FROM `'.PREFIX.'training` WHERE `id`='.$training_id);

		if ($training['pulse_avg'] != 0 && $training['sportid'] == CONF_RUNNINGSPORT) {
			$VDOT = self::Competition2VDOT($training['distance'],  $training['s']);
			if ($VDOT !== false)
				return round( $VDOT / (self::pHF2pVDOT($training['pulse_avg']/HF_MAX) ), 2);
		}

		return 0;
	}

	/**
	 * Calculates a prognosis for a given distance based an an actual VDOT
	 * @param $VDOTtoReach  VDOT
	 * @param $km           Distance [km]
	 * @return int          Time [s]
	 */
	public static function CompetitionPrognosis($VDOTtoReach, $km = 5) {
		if ($VDOTtoReach == 0)
			return 0;

		$s        = round(2*60*$km);
		$VDOT_low = 150;
		while (true) {
			//$s++;
			$s += $km;

			$VDOT_high = $VDOT_low;
			$VDOT_low  = self::Competition2VDOT($km, $s);

			if ($VDOT_high > $VDOTtoReach && $VDOTtoReach > $VDOT_low)
				break;

			if ($s >= 60 * 60 * $km / 4)
				break;
		}

		return $s;
	}

	/**
	 * Get const for VDOT_FORM
	 * @return float
	 */
	public static function getConstVDOTform() {
		if (defined('CONF_VDOT_MANUAL_VALUE')) {
			$ManualValue = (float)Helper::CommaToPoint(CONF_VDOT_MANUAL_VALUE);
			if ($ManualValue > 0)
				return $ManualValue;
		}

		return self::calculateVDOTform();
	}

	/**
	 * Calculates an (corrected) actual VDOT value based on the trainings in the last VDOT_DAYS days
	 * @param int $time optional
	 * @return float   VDOT
	 */
	public static function calculateVDOTform($time = 0) {
		if ($time == 0)
			$time = time();

		$Data = Mysql::getInstance()->fetchSingle('
			SELECT
				SUM(`s`) as `ssum`,
				SUM(`vdot`*`s`) as `value`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`="'.CONF_RUNNINGSPORT.'"
				&& `pulse_avg`!=0
				&& `use_vdot`=1
				&& `time`<"'.$time.'"
				&& `time`>"'.($time - VDOT_DAYS*DAY_IN_S).'"
			GROUP BY `sportid`');

		if ($Data !== false)
			return round(self::correctVDOT($Data['value']/$Data['ssum']), 5);

		return 0;
	}

	/**
	 * Get VDOT corrector 
	 */
	public static function getVDOTcorrector() {
		if (defined('CONF_VDOT_MANUAL_CORRECTOR')) {
			$ManualCorrector = (float)Helper::CommaToPoint(CONF_VDOT_MANUAL_CORRECTOR);
			if ($ManualCorrector > 0)
				return $ManualCorrector;
		}

		if (!defined('CONF_VDOT_CORRECTOR')) {
			Error::getInstance()->addError('Constant CONF_VDOT_CORRECTOR has to be set!');
			define('CONF_VDOT_CORRECTOR', 1);
		}

		if (defined('VDOT_CORRECTOR'))
			return VDOT_CORRECTOR;

		if (CONF_VDOT_CORRECTOR >= 1 || CONF_VDOT_CORRECTOR == 0) {
			if (0 < Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` WHERE `typeid`="'.CONF_WK_TYPID.'" AND `pulse_avg`!=0 LIMIT 1'))
				return self::recalculateVDOTcorrector();
		}

		return CONF_VDOT_CORRECTOR;
	}

	/**
	 * Calculates a factor for correcting the user-specific VDOT-value
	 * This function should be only called if a new competition has been submitted (or changed)
	 * @uses Helper::Bestzeit
	 * @uses HF_MAX
	 * @uses CONF_WK_TYPID
	 * @return float   VDOTcorrectionfactor
	 */
	public static function recalculateVDOTcorrector() {
		// Find best VDOT-value from personal best in competition
		$VDOT_CORRECTOR = 1;

		$VDOT_top = 0;
		$VDOT_top_dist = 0;
		$distances = array(5, 10, 21.1, 42.2);
		foreach ($distances as $dist) {
			$dist_PB = Running::PersonalBest($dist, true);
			if ($dist_PB != 0) {
				$dist_VDOT = self::Competition2VDOT($dist, $dist_PB);
				if ($dist_VDOT > $VDOT_top && Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` WHERE `typeid`="'.CONF_WK_TYPID.'" AND `pulse_avg`!=0 AND `distance`="'.$dist.'" LIMIT 1') > 0) {
					$VDOT_top = $dist_VDOT;
					$VDOT_top_dist = $dist;
				}
			}
		}

		// Calculate VDOT-value for personal best from heartfrequence
		$VDOT_top_dat = Mysql::getInstance()->fetchSingle('SELECT `pulse_avg`, `s` FROM `'.PREFIX.'training` WHERE `distance`="'.$VDOT_top_dist.'" AND `pulse_avg`!=0 AND `typeid`="'.CONF_WK_TYPID.'" ORDER BY `s` ASC');
		if ($VDOT_top_dat !== false) {
			$VDOT_max = self::Competition2VDOT($VDOT_top_dist, $VDOT_top_dat['s'])
				/ self::pHF2pVDOT($VDOT_top_dat['pulse_avg'] / HF_MAX);

			if ($VDOT_top != 0 && $VDOT_max != 0)
				$VDOT_CORRECTOR = $VDOT_top / $VDOT_max;
		}

		ConfigValue::update('VDOT_CORRECTOR', $VDOT_CORRECTOR);

		return $VDOT_CORRECTOR;
	}

	/**
	 * Get VDOT corrector for a given training
	 * @param int $ID
	 * @param array $Training [optional]
	 * @return float 
	 */
	public static function VDOTcorrectorFor($ID, $Training = array()) {
		if (empty($Training))
			$Training = Mysql::getInstance()->fetchSingle('
				SELECT
					`pulse_avg`,
					`s`,
					`distance`,
					`vdot`
				FROM `'.PREFIX.'training`
				WHERE `id`='.$ID);

		$VDOTtimeDistance = JD::Competition2VDOT($Training['distance'], $Training['s']);
		$VDOTpulsePace    = $Training['vdot'];

		return $VDOTtimeDistance/$VDOTpulsePace;
	}
}