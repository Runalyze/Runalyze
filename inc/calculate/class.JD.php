<?php
/**
 * This file contains the class::JD
 * @package Runalyze\Calculations
 */
/**
 * The actual (corrected) VDOT-value based on last trainings
 * @var double
 */
define('VDOT_FORM', JD::getConstVDOTform());

/**
 * Class for calculating based on "Jack Daniels' Running Formula"
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class JD {
	/**
	 * Value for basic endurance
	 * 
	 * This value refers to the constant configuration value.
	 * @var int
	 */
	private static $CONST_CORRECTOR = false;

	/**
	 * Get sum selector for VDOT for mysql
	 * 
	 * Depends on configuration: `vdot`*`s`*`use_vdot` or `vdot_with_elevation`*`s`*`use_vdot`
	 * 
	 * @return string
	 */
	public static function mysqlVDOTsum() {
		return Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`)*`s`*`use_vdot`' : '`vdot`*`s`*`use_vdot`';
	}

	/**
	 * Get sum selector for time for mysql
	 * 
	 * `s`*`use_vdot`
	 * 
	 * @return string
	 */
	public static function mysqlVDOTsumTime() {
		return '`s`*`use_vdot`*('.(Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`)' : '`vdot`').' > 0)';
	}

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
	 * Calculates percentage of VDOT for given speed in m/min
	 * @param float $v Speed [m/min]
	 * @return float   VDOT
	 */
	public static function v2VDOT($v) {
		return 29 + ($v - 173.154)/4.116;
	}

	/**
	 * Calculates heart-frequence for a given percentage of VDOT
	 * @param $pVDOT   VDOT [%]
	 * @return float   HFmax [%]
	 */
	public static function pVDOT2pHF($pVDOT) {
		if (Configuration::Vdot()->method()->usesLogarithmic())
			return 0.68725*log($pVDOT)+1.00466;

		// Old version
		return ($pVDOT+0.2812)/1.2812;
	}

	/**
	 * Calculates percentage of VDOT for a given percentage of HFmax
	 * @param $pHF     HFmax [%]
	 * @return float   VDOT [%]
	 */
	public static function pHF2pVDOT($pHF) {
		if (Configuration::Vdot()->method()->usesLogarithmic())
			return exp( ($pHF - 1.00466) / 0.68725 );

		// Old version
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
		return 60*1000/$pace_in_s;
	}

	/**
	 * Corrects VDOT if VDOT-corrector is enabled
	 * @param double $VDOT
	 * @return double
	 */
	public static function correctVDOT($VDOT) {
		if (Configuration::Vdot()->useCorrectionFactor())
			return self::correctionFactor()*$VDOT;

		return $VDOT;
	}

	/**
	 * Calculates VDOT for a training
	 * 
	 * without correction!
	 * @uses HF_MAX
	 * @param int $training_id
	 * @param array $training [optional]
	 * @return double
	 */
	public static function Training2VDOT($training_id, $training = array()) {
		if (!isset($training['sportid']) || !isset($training['distance']) || !isset($training['s']) || !isset($training['pulse_avg']))
			$training = DB::getInstance()->query('SELECT `sportid`, `distance`, `s`, `pulse_avg` FROM `'.PREFIX.'training` WHERE `id`='.(int)$training_id.' LIMIT 1')->fetch();

		return self::values2VDOT($training['distance'], $training['s'], $training['pulse_avg'], $training['sportid']);
	}

	/**
	 * Calculates VDOT for a training
	 * 
	 * without correction, with elevation!
	 * @uses HF_MAX
	 * @param int $training_id
	 * @param array $training [optional]
	 * @param int $up [optional]
	 * @param int $down [optional]
	 * @return double
	 */
	public static function Training2VDOTwithElevation($training_id, $training = array(), $up = false, $down = false) {
		$elevationFromDatabaseNeeded = ($up === false && $down === false) && (!isset($training['elevation']) || !isset($training['arr_alt']));
		if (!isset($training['sportid']) || !isset($training['distance']) || !isset($training['s']) || !isset($training['pulse_avg']) || $elevationFromDatabaseNeeded)
			$training = DB::getInstance()->query('SELECT `sportid`, `distance`, `s`, `pulse_avg`, `elevation`, `arr_alt`, `arr_time` FROM `'.PREFIX.'training` WHERE `id`='.(int)$training_id.' LIMIT 1')->fetch();

		if (!$training)
			return 0;

		if ($up === false && $down === false) {
			if (isset($training['arr_alt']) && !empty($training['arr_alt'])) {
				$GPS    = new GpsData($training);
				$elevationArray = $GPS->calculateElevation(true);
				$up   = $elevationArray[1];
				$down = $elevationArray[2];
			} elseif (isset($training['elevation'])) {
				$up   = $training['elevation'];
				$down = $training['elevation'];
			} else {
				$up   = 0;
				$down = 0;
			}
		}
		$training['distance'] = self::transformDistanceFromElevation($training['distance'], $up, $down);

		return self::values2VDOT($training['distance'], $training['s'], $training['pulse_avg'], $training['sportid']);
	}

	/**
	 * Transform distance from elevatoin
	 * 
	 * @param double $distance
	 * @param int $up
	 * @param int $down
	 * @return double
	 */
	public static function transformDistanceFromElevation($distance, $up, $down) {
		return $distance + Configuration::Vdot()->correctionForPositiveElevation()*$up/1000 + Configuration::Vdot()->correctionForNegativeElevation()*$down/1000;
	}

	/**
	 * Calculate vdot for given values
	 * @param type $distance
	 * @param type $s
	 * @param type $pulse_avg
	 * @param int $sportid [optional]
	 * @return int
	 */
	private static function values2VDOT($distance, $s, $pulse_avg, $sportid = false) {
		if ($sportid === false)
			$sportid = Configuration::General()->runningSport();

		if ($pulse_avg != 0 && $sportid == Configuration::General()->runningSport()) {
			$VDOT = self::Competition2VDOT($distance, $s);
			if ($VDOT !== false)
				return round( $VDOT / (self::pHF2pVDOT($pulse_avg/HF_MAX) ), 2);
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

		$iterations = 0;
		$precision  = 0.01;
		$lowerBound = round(2*60*$km);
		$upperBound = round(10*60*$km);

		while (true) {
			$middle = ($lowerBound + $upperBound) / 2;
			$VDOT   = self::Competition2VDOT($km, $middle);

			if (abs($VDOT - $VDOTtoReach) < $precision) {
				break;
			} elseif ($VDOT < $VDOTtoReach) {
				$upperBound = $middle;
			} else {
				$lowerBound = $middle;
			}

			$iterations++;

			if ($iterations > 100)
				break;
		}

		return $middle;
	}
 
 	/**
	 * Calculates points for a training
	 * @param int $training_id
	 * @param array $training [optional] Needs values for 's', 'distance', 'pulse_avg', 'arr_heart', 'arr_time'
	 * @return double
	 */
	public static function Training2points($training_id, $training = array()) {
		if (!isset($training['sportid']))
			$training['sportid'] = false;

		if (!isset($training['s']) || !isset($training['pulse_avg']) || !isset($training['distance']))
			$training = DB::getInstance()->query('SELECT `sportid`, `s`, `distance`, `pulse_avg`, `arr_heart`,`arr_time` FROM `'.PREFIX.'training` WHERE `id`='.(int)$training_id.' LIMIT 1')->fetch();

		$GPS    = new GpsData($training);
		$pulseArray = $GPS->getPulseZonesAsFilledArrays();

		return self::values2points($training['s'], $training['distance'], $training['pulse_avg'], $training['sportid'], $pulseArray);
	}
 
 	/**
	 * Approximate points for given values
	 * cf. Table 2.2. in [JD]
	 * @uses HF_MAX
	 * @param int $s
	 * @param float $distance
	 * @param int $pulse_avg
	 * @param int $sportid [optional]
	 * @return int
	 */
	private static function values2points($s, $distance, $pulse_avg, $sportid = false, $pulseArray = array()) {
		if ($sportid === false || $sportid == Configuration::General()->runningSport()) {
			if ($pulseArray) {
				$points = 0;
				foreach ($pulseArray as $hf => $Info)
					if ($Info['time'] > 0)
						$points += self::valuesSingle2points($hf/10, $Info['time']);

				return $points;
			}

			if ($pulse_avg == 0) {
				$VDOTbyPace = self::v2VDOT( self::Pace2v($s/$distance) );
				$pulse_avg = self::pVDOT2pHF( $VDOTbyPace / self::getConstVDOTform() );
			} else {
				$pulse_avg = $pulse_avg / HF_MAX;
			}

			return self::valuesSingle2points($pulse_avg, $s);
		}

		return 0;
	}

	/**
	 * Trainingpoints for single values
	 * @param float $heartrateInPercent
	 * @param int $timeInSeconds
	 * @return float
	 */
	private static function valuesSingle2points($heartrateInPercent, $timeInSeconds) {
		$heartrateInPercent = max($heartrateInPercent, 0.5);

		return (4.742894532 * pow($heartrateInPercent, 2) - 5.298465448 * $heartrateInPercent + 1.550709462) * $timeInSeconds / 60;
	}

	/**
	 * Get const for VDOT_FORM
	 * @return float
	 */
	public static function getConstVDOTform() {
		if (Configuration::Vdot()->useManualValue()) {
			return Configuration::Vdot()->manualValue();
		}

		if (!defined('CONF_VDOT_FORM')) {
			Error::getInstance()->addError('Constant CONF_VDOT_FORM has to be set!');
			define('CONF_VDOT_FORM', 0);
		}

		if (defined('VDOT_FORM'))
			return VDOT_FORM;

		if (CONF_VDOT_FORM == 0)
			return self::recalculateVDOTform();

		return CONF_VDOT_FORM;
	}

	/**
	 * Recalculate actual VDOT
	 */
	public static function recalculateVDOTform() {
		$VDOT_FORM = self::calculateVDOTform();

		ConfigValue::update('VDOT_FORM', $VDOT_FORM);

		return $VDOT_FORM;
	}

	/**
	 * Calculate actual VDOT
	 * 
	 * Gives an (corrected) actual VDOT value based on the trainings in the last VDOT_DAYS days
	 * @param int $time optional
	 * @return float   VDOT
	 */
	public static function calculateVDOTform($time = 0) {
		if ($time == 0) {
			$time = time();
		}

		$time = mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));
		$Data = DB::getInstance()->query('
			SELECT
				SUM('.self::mysqlVDOTsumTime().') as `ssum`,
				SUM('.self::mysqlVDOTsum().') as `value`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`="'.Configuration::General()->runningSport().'"
				&& `time` BETWEEN '.($time - Configuration::Vdot()->days()*DAY_IN_S).' AND '.$time.'
			GROUP BY `sportid`
			LIMIT 1
		')->fetch();

		if ($Data !== false && $Data['ssum'] > 0) {
			return round(self::correctVDOT($Data['value']/$Data['ssum']), 5);
		}

		return 0;
	}

	/**
	 * Get const for VDOT_CORRECTOR
	 * @return int
	 */
	public static function correctionFactor() {
		if (Configuration::Vdot()->useManualFactor()) {
			return Configuration::Vdot()->manualFactor();
		}

		if (self::$CONST_CORRECTOR === false) {
			if (!defined('CONF_VDOT_CORRECTOR')) {
				Error::getInstance()->addError('Constant CONF_VDOT_CORRECTOR has to be set!');
				define('CONF_VDOT_CORRECTOR', 1);
			}

			if (CONF_VDOT_CORRECTOR != 1 && CONF_VDOT_CORRECTOR != 0)
				self::$CONST_CORRECTOR = CONF_VDOT_CORRECTOR;
			else
				self::recalculateVDOTcorrector();
		}

		return self::$CONST_CORRECTOR;
	}

	/**
	 * Recalculate VDOT corrector
	 * 
	 * The correction factor is the ratio of the VDOT values by time and by heartrate
	 * for the "best" competition. Since there may be some big deviations, the maximal ratio
	 * of the three best competitions is taken as factor.
	 * 
	 * This function should be only called if a new competition has been submitted (or changed).
	 * 
	 * @return float new correction factor
	 */
	public static function recalculateVDOTcorrector() {
		$Statement = DB::getInstance()->prepare('
			SELECT MAX(`factor`) as `factor`
			FROM (
				SELECT `vdot_by_time`/`vdot` AS `factor` 
				FROM `'.PREFIX.'training` 
				WHERE `typeid` = :typeid
				AND `pulse_avg` > 0
				ORDER BY  `vdot_by_time` DESC 
				LIMIT 3
			) AS T
			LIMIT 1
		');
		$Statement->execute(array(':typeid' => Configuration::General()->competitionType()));
		$Result = $Statement->fetch();

		$VDOT_CORRECTOR = (isset($Result['factor'])) ? $Result['factor'] : 1;

		ConfigValue::update('VDOT_CORRECTOR', $VDOT_CORRECTOR);
		self::$CONST_CORRECTOR = $VDOT_CORRECTOR;

		return $VDOT_CORRECTOR;
	}

	/**
	 * Get VDOT corrector for a given training
	 * @param int $ID
	 * @param array $Training [optional]
	 * @return float 
	 */
	public static function VDOTcorrectorFor($ID, $Training = array()) {
		if (!isset($Training['vdot']) || !isset($Training['vdot_by_time']))
			$Training = DB::getInstance()->query('
				SELECT
					`vdot`,
					`vdot_by_time`
				FROM `'.PREFIX.'training`
				WHERE `id`='.(int)$ID.'
				LIMIT 1
			')->fetch();

		return $Training['vdot_by_time'] / $Training['vdot'];
	}
}