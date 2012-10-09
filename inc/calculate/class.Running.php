<?php
/**
 * Class: Running
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Running {
	static public function possibleKm() {
		// TODO
	}

	/**
	 * Get the speed depending on the sport as pace or km/h
	 * @param float $km       Distance [km]
	 * @param int $time       Time [s]
	 * @param int $sport_id   ID of sport for choosing pace/kmh
	 * @return string
	 */
	public static function Speed($km, $time, $sport_id = 0) {
		if ($km == 0 || $time == 0)
			return '';

		$as_pace = self::Pace($km, $time).'/km';
		$as_kmh = self::Kmh($km, $time).'&nbsp;km/h';

		if (Sport::usesSpeedInKmh($sport_id))
			return Ajax::tooltip($as_kmh, $as_pace);
			
		return Ajax::tooltip($as_pace, $as_kmh);
	}

	/**
	 * Get the speed in min/km without unit
	 * @param float $km   Distance [km]
	 * @param int $time   Time [s]
	 * @return string
	 */
	public static function Pace($km, $time) {
		if ($km == 0)
			return '-:--';

		return Time::toString(round($time/$km));
	}

	/**
	 * Get the speed in km/h without unit
	 * @param float $km   Distance [km]
	 * @param int $time   Time [s]
	 * @return string
	 */
	public static function Kmh($km, $time) {
		return number_format($km*3600/$time, 1, ',', '.');
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

		if ($time != 0) {
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
		
		return round(100*$pulse / $hf_max).'&nbsp;&#37';
	}

	/**
	 * Get string for pulse [%HFres]
	 * @param int $pulse
	 * @param int $hf_max [optional]
	 * @param int $hf_rest [optional]
	 * @return string
	 */
	public static function PulseStringInPercentReserve($pulse, $hf_max = 0, $hf_rest = 0) {
		return self::PulseInPercentReserve($pulse, $hf_max, $hf_rest).'&nbsp;&#37';
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
	 */
	public static function BasicEndurance($as_int = false, $timestamp = 0) {
		// TODO: Unittests
		if ($timestamp == 0) {
			if (defined('BASIC_ENDURANCE'))
				return ($as_int) ? BASIC_ENDURANCE : BASIC_ENDURANCE.' &#37;';
			$timestamp = time();
		}

		if (VDOT_FORM == 0)
			return ($as_int) ? 0 : '0 &#37;';

		$diff = Time::diffInDays(START_TIME);
		if ($diff > 182)
			$DaysForWeekKm = 182; // 26 Wochen
		elseif ($diff < 70)
			$DaysForWeekKm = 70;
		else
			$DaysForWeekKm = $diff;

		$DaysForLongjogs        = 70;  // 10 Wochen
		$StartTimeForLongjogs   = $timestamp - $DaysForLongjogs * DAY_IN_S;
		$StartTimeForWeekKm     = $timestamp - $DaysForWeekKm * DAY_IN_S;
		$minKmForLongjog        = 13;
		$TargetWeekKm           = pow(VDOT_FORM, 1.135);
		$TargetLongjogKmPerWeek = log(VDOT_FORM/4) * 12 - $minKmForLongjog;

		$Query         = '
			SELECT
				SUM(
					IF (time >= '.$StartTimeForWeekKm.', `distance`, 0)
				) as `km`,
				SUM(
					IF (
						`distance` > '.$minKmForLongjog.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.$DaysForLongjogs.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.$minKmForLongjog.')/'.$TargetLongjogKmPerWeek.',2)
						),
						0
					)
				) as `sum`
			FROM '.PREFIX.'training
			WHERE sportid='.CONF_RUNNINGSPORT.' AND time<='.$timestamp.' AND time>='.min($StartTimeForLongjogs,$StartTimeForWeekKm).'
			GROUP BY accountid';
		$DataSum       = Mysql::getInstance()->fetchSingle($Query);
		$WeekKmResult  = isset($DataSum['km']) ? $DataSum['km'] : 0;
		$LongjogResult = isset($DataSum['sum']) ? $DataSum['sum'] : 0;

		$WeekPercentage    = $WeekKmResult * 7 / $DaysForWeekKm / $TargetWeekKm;
		$LongjogPercentage = $LongjogResult * 7 / $DaysForLongjogs;
		$Percentage        = round( 100 * ( $WeekPercentage*2/3 + $LongjogPercentage*1/3 ) );

		if ($Percentage < 0)
			$Percentage = 0;
		if ($Percentage > 100)
			$Percentage = 100;

		return ($as_int) ? $Percentage : $Percentage.' &#37;';
	}
}