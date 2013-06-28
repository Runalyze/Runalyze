<?php
/**
 * This file contains class::Running
 * @package Runalyze\Calculations
 */
/**
 * Class: Running
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class Running {
	/**
	 * Basic endurance: Minimum distance to be recognized as a longjog
	 * @var double
	 */
	static $BE_MIN_KM_FOR_LONGJOG = 13;

	/**
	 * Basic endurance: Number of days for counting weekkilometer
	 * @var int 
	 */
	static $BE_DAYS_FOR_WEEK_KM = 182;

	/**
	 * Basic endurance: Minimum number of days for counting weekkilometer
	 * @var int 
	 */
	static $BE_DAYS_FOR_WEEK_KM_MIN = 70;

	/**
	 * Basic endurance: Number of days for counting longjogs
	 * @var int
	 */
	static $BE_DAYS_FOR_LONGJOGS = 70;

	/**
	 * Basic endurance: Percentage for weekkilometer
	 * @var double
	 */
	static $BE_PERCENTAGE_WEEK_KM = 0.67;

	/**
	 * Basic endurance: Percentage for longjogs
	 * @var double
	 */
	static $BE_PERCENTAGE_LONGJOGS = 0.33;

	/**
	 * Average month pace (access only via self::getAverageMonthPace()
	 * @var double
	 */
	static $AverageMonthPace = false;

	/**
	 * Get average month pace
	 * @return double [min/km]
	 */
	static public function getAverageMonthPace() {
		if (self::$AverageMonthPace === false) {
			$AverageMonthPace       = Mysql::getInstance()->fetchSingle('SELECT AVG(`s`/60/`distance`) AS `avg` FROM `'.PREFIX.'training` WHERE `time` > '.(time()-30*DAY_IN_S).' AND `sportid`='.CONF_RUNNINGSPORT);
			self::$AverageMonthPace = $AverageMonthPace['avg'];
		}

		return self::$AverageMonthPace;
	}

	/**
	 * Get number of "possible" kilometer in a given time range
	 * @param int $Days
	 * @param int $roundForInt [optional]
	 * @return int
	 */
	static public function possibleKmInDays($Days, $roundForInt = 1) {
		$CTL = Trimp::CTL();

		if ($CTL <= 0 || self::getAverageMonthPace() <= 0)
			return false;

		return Helper::roundFor((Trimp::minutesForTrimp($Days * $CTL) / self::getAverageMonthPace()), $roundForInt);
	}

	/**
	 * Get number of "possible" kilometer in a week
	 * @param int $roundForInt [optional]
	 * @return int
	 */
	static public function possibleKmInOneWeek($roundForInt = 5) {
		return self::possibleKmInDays(7, $roundForInt);
	}

	/**
	 * Get number of "possible" kilometer in a month
	 * @param int $roundForInt [optional]
	 * @return int
	 */
	static public function possibleKmInOneMonth($roundForInt = 10) {
		return self::possibleKmInDays(365/12, $roundForInt);
		
	}

	/**
	 * Display a distance as km or m
	 * @param float $km       Distance [km]
	 * @param int $decimals   Decimals after the point, default: 1
	 * @param bool $track     Run on a tartan track?, default: false
	 */
	public static function Km($km, $decimals = -1, $track = false) {
		if ($km == 0)
			return '';

		if ($track)
			return number_format($km*1000, 0, ',', '.').'m';

		if ($decimals == -1)
			$decimals = CONF_TRAINING_DECIMALS;

		return number_format($km, $decimals, ',', '.').'&nbsp;km';
	}

	/**
	 * Find the personal best for a given distance
	 * @uses self::Time
	 * @param float $dist       Distance [km]
	 * @param bool $return_time Return as integer, default: false
	 * @return mixed
	 */
	public static function PersonalBest($dist, $return_time = false) {
		$pb = Mysql::getInstance()->fetchSingle('SELECT `s`, `distance` FROM `'.PREFIX.'training` WHERE `typeid`="'.CONF_WK_TYPID.'" AND `distance`="'.$dist.'" ORDER BY `s` ASC');
		if ($return_time)
			return ($pb != '') ? $pb['s'] : 0;
		if ($pb != '')
			return Time::toString($pb['s']);
		return '<em>keine</em>';
	}

	/**
	 * Get a string for displaying any pulse
	 * @param int $pulse
	 * @param int $time
	 * @return string
	 */
	public static function PulseString($pulse, $time = 0) {
		if ($pulse == 0)
			return '';

		$hf_max  = 0;
		$hf_rest = 0;

		if ($time != 0 && (time() - $time) > 365*DAY_IN_S) {
			$HFmax = Mysql::getInstance()->fetchSingle('SELECT `time`,`pulse_max`,`pulse_rest` FROM `'.PREFIX.'user` ORDER BY ABS(`time`-'.$time.') ASC');
			if ($HFmax !== false && $HFmax['pulse_max'] != 0)
				$hf_max  = $HFmax['pulse_max'];
			if ($HFmax !== false && $HFmax['pulse_rest'] != 0)
				$hf_rest = $HFmax['pulse_rest'];
		}

		$bpm = self::PulseStringInBpm($pulse);
		$hf  = self::PulseStringInPercent($pulse, $hf_max);
		$hfr = self::PulseStringInPercentReserve($pulse, $hf_max, $hf_rest);

		if (CONF_PULS_MODE == 'hfmax')
			return Ajax::tooltip($hf, $bpm);

		if (CONF_PULS_MODE == 'hfres')
			return Ajax::tooltip($hfr, $bpm);
			
		return Ajax::tooltip($bpm, $hf);
	}

	/**
	 * Get string for pulse [bpm]
	 * @param int $pulse
	 * @return string
	 */
	public static function PulseStringInBpm($pulse) {
		return round($pulse).'bpm';
	}

	/**
	 * Get string for pulse [%HFmax]
	 * @param int $pulse
	 * @param int $hf_max [optional]
	 * @return string
	 */
	public static function PulseStringInPercent($pulse, $hf_max = 0) {
		if ($hf_max == 0)
			$hf_max = HF_MAX;
		
		return round(100*$pulse / $hf_max).'&nbsp;&#37;';
	}

	/**
	 * Get string for pulse [%HRmax]
	 * @param int $pulse
	 * @param int $hf_max [optional]
	 * @return string
	 */
	public static function PulseStringInPercentHRmax($pulse, $hf_max = 0, $hf_rest = 0) {
		if ($hf_max == 0)
			$hf_max = HF_MAX;
		if ($hf_rest == 0)
			$hf_rest = HF_REST;
		
		return round(100*($pulse - $hf_rest) / ($hf_max - $hf_rest)).'&nbsp;&#37;';
	}

	/**
	 * Get string for pulse [%HFres]
	 * @param int $pulse
	 * @param int $hf_max [optional]
	 * @param int $hf_rest [optional]
	 * @return string
	 */
	public static function PulseStringInPercentReserve($pulse, $hf_max = 0, $hf_rest = 0) {
		return self::PulseInPercentReserve($pulse, $hf_max, $hf_rest).'&nbsp;&#37;';
	}

	/**
	 * Get pulse in percent of reserve
	 * @param int $pulse
	 * @param int $hf_max [optional]
	 * @param int $hf_rest [optional]
	 * @return string
	 */
	public static function PulseInPercentReserve($pulse, $hf_max = 0, $hf_rest = 0) {
		if ($hf_max == 0)
			$hf_max = HF_MAX;

		if ($hf_rest == 0)
			$hf_rest = HF_REST;

		return round(100*($pulse - $hf_rest) / ($hf_max - $hf_rest));
	}

	/**
	 * Creating a RGB-color for a given stress-value [0-100]
	 * @param int $stress   Stress-value [0-100]
	 */
	public static function Stresscolor($stress) {
		if ($stress > 100)
			$stress = 100;
		if ($stress < 0)
			$stress = 0;

		$gb = dechex(200 - 2*$stress);

		if ((200 - 2*$stress) < 16)
			$gb = '0'.$gb;

		return 'C8'.$gb.$gb;
	}

	/**
	 * Get the demanded pace if set in description (e.g. "... in 3:05 ...")
	 * @param string $description
	 * @return int
	 */
	public static function DescriptionToDemandedPace($description) {
		$array = explode("in ", $description);
		if (count($array) != 2)
			return 0;

		$array = explode(",", $array[1]);
		$array = explode(":", $array[0]);

		return sizeof($array) == 2 ? 60*$array[0]+$array[1] : 0;
	}

	/**
	 * Calculate factor concerning to basic endurance
	 * @param double $distance
	 * @return double
	 */
	static public function VDOTfactorOfBasicEndurance($distance) {
		$BasicEndurance         = Running::BasicEndurance(true);
		$RequiredBasicEndurance = pow($distance, 1.23);
		$BasicEnduranceFactor   = 1 - ($RequiredBasicEndurance - $BasicEndurance) / 100;

		if ($BasicEnduranceFactor > 1)
			return 1;
		if ($BasicEnduranceFactor < 0)
			return 0.01;

		return (0.6 + 0.4 * $BasicEnduranceFactor);
	}

	/**
	 * Get prognosis (vdot/seconds) as array
	 * @param double $distance
	 * @param double $VDOT [optional]
	 * @param boolean $useEnduranceFactor
	 * @return array
	 */
	static public function PrognosisAsArray($distance, $VDOT = 0, $useEnduranceFactor = true) {
		return self::Prognosis($distance, $VDOT, $useEnduranceFactor, true);
	}

	/**
	 * Get prognosis in seconds
	 * @param double $distance
	 * @param double $VDOT [optional]
	 * @param boolean $useEnduranceFactor [optional]
	 * @param boolean $asArray [optional]
	 * @return mixed
	 */
	static public function Prognosis($distance, $VDOT = 0, $useEnduranceFactor = true, $asArray = false) {
		$VDOT  = ($VDOT == 0) ? VDOT_FORM : $VDOT;

		if ($useEnduranceFactor)
			$VDOT *= self::VDOTfactorOfBasicEndurance($distance);

		$PrognosisInSeconds = JD::CompetitionPrognosis($VDOT, $distance);

		if ($asArray)
			return array('vdot' => $VDOT, 'seconds' => $PrognosisInSeconds);

		return $PrognosisInSeconds;
	}

	/**
	 * Calculating basic endurance
	 * @uses DAY_IN_S
	 * @param bool $as_int as normal integer, default: false
	 * @param int $timestamp [optional] timestamp
	 * @param boolean $returnArrayWithResults [optional]
	 */
	public static function BasicEndurance($as_int = false, $timestamp = 0, $returnArrayWithResults = false) {
		// TODO: If you change the algorithm, remember to change info in 'RunalyzePluginPanel_Rechenspiele'
		// TODO: Unittests
		if ($timestamp == 0 && !$returnArrayWithResults) {
			if (defined('BASIC_ENDURANCE'))
				return ($as_int) ? BASIC_ENDURANCE : BASIC_ENDURANCE.' &#37;';
			$timestamp = time();
		}

		if (VDOT_FORM == 0)
			return ($as_int) ? 0 : '0 &#37;';

		$DataSum       = Mysql::getInstance()->fetchSingle( self::getQueryForBE($timestamp) );
		$WeekKmResult  = isset($DataSum['km']) ? $DataSum['km'] : 0;
		$LongjogResult = isset($DataSum['sum']) ? $DataSum['sum'] : 0;

		$WeekPercentage    = $WeekKmResult * 7 / self::getBEDaysForWeekKm() / self::getBETargetWeekKm();
		$LongjogPercentage = $LongjogResult * 7 / self::$BE_DAYS_FOR_LONGJOGS;
		$Percentage        = round( 100 * ( $WeekPercentage*self::$BE_PERCENTAGE_WEEK_KM + $LongjogPercentage*self::$BE_PERCENTAGE_LONGJOGS ) );

		if ($returnArrayWithResults) {
			$Array = array(
				'weekkm-result'		=> $WeekKmResult,
				'weekkm-percentage'	=> $WeekPercentage,
				'longjog-result'	=> $LongjogResult,
				'longjog-percentage'=> $LongjogPercentage,
				'percentage'		=> $Percentage
			);

			return $Array;
		}

		if ($Percentage < 0)
			$Percentage = 0;
		if ($Percentage > 100)
			$Percentage = 100;

		return ($as_int) ? $Percentage : $Percentage.' &#37;';
	}

	/**
	 * Get query for BE
	 * @param int $timestamp [optional]
	 * @param boolean $onlyLongjogs [optional]
	 * @return string
	 */
	static public function getQueryForBE($timestamp = 0, $onlyLongjogs = false) {
		if ($timestamp == 0)
			$timestamp = time();

		$StartTimeForLongjogs = $timestamp - self::$BE_DAYS_FOR_LONGJOGS * DAY_IN_S;
		$StartTimeForWeekKm   = $timestamp - self::getBEDaysForWeekKm() * DAY_IN_S;

		if ($onlyLongjogs) {
			return '
				SELECT
					`id`,
					`time`,
					`distance`,
					IF (
						`distance` > '.self::$BE_MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.self::$BE_DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.self::$BE_MIN_KM_FOR_LONGJOG.')/'.self::getBETargetLongjogKmPerWeek().',2)
						),
						0
					) as `points`
				FROM '.PREFIX.'training
				WHERE sportid='.CONF_RUNNINGSPORT.' AND time<='.$timestamp.' AND distance>'.self::$BE_MIN_KM_FOR_LONGJOG.' AND time>='.$StartTimeForLongjogs.'';
		}

		return '
			SELECT
				SUM(
					IF (time >= '.$StartTimeForWeekKm.', `distance`, 0)
				) as `km`,
				SUM(
					IF (
						`distance` > '.self::$BE_MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.self::$BE_DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.self::$BE_MIN_KM_FOR_LONGJOG.')/'.self::getBETargetLongjogKmPerWeek().',2)
						),
						0
					)
				) as `sum`
			FROM '.PREFIX.'training
			WHERE sportid='.CONF_RUNNINGSPORT.' AND time<='.$timestamp.' AND time>='.min($StartTimeForLongjogs,$StartTimeForWeekKm).'
			GROUP BY accountid';
	}

	/**
	 * Get days used for week km for basic endurance
	 * @return double 
	 */
	static public function getBEDaysForWeekKm() {
		$diff = Time::diffInDays(START_TIME);

		if ($diff > self::$BE_DAYS_FOR_WEEK_KM)
			return self::$BE_DAYS_FOR_WEEK_KM;
		elseif ($diff < self::$BE_DAYS_FOR_WEEK_KM_MIN)
			return self::$BE_DAYS_FOR_WEEK_KM_MIN;

		return $diff;
	}

	/**
	 * Get target week km
	 * @return double
	 */
	static public function getBETargetWeekKm() {
		return pow(VDOT_FORM, 1.135);
	}

	/**
	 * Get target longjog km per week
	 * PAY ATTENTION: self::$BE_MIN_KM_FOR_LONGJOG is already subtracted!
	 * @return double
	 */
	static public function getBETargetLongjogKmPerWeek() {
		if (VDOT_FORM == 0)
			return 1;

		return log(VDOT_FORM/4) * 12 - self::$BE_MIN_KM_FOR_LONGJOG;
	}

	/**
	 * Get (real) target longjog km per week
	 * @return double
	 */
	static public function getBErealTargetLongjogKmPerWeek() {
		return self::getBETargetLongjogKmPerWeek() + self::$BE_MIN_KM_FOR_LONGJOG;
	}
}