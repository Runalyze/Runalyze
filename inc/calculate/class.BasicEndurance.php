<?php
/**
 * This file contains class::BasicEndurance
 * @package Runalyze\Calculations
 */
/**
 * Class: BasicEndurance
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class BasicEndurance {
	/**
	 * Minimum distance to be recognized as a longjog
	 * @var double
	 */
	static $MIN_KM_FOR_LONGJOG = 13;

	/**
	 * Number of days for counting weekkilometer
	 * @var int 
	 */
	static $DAYS_FOR_WEEK_KM = 182;

	/**
	 * Minimum number of days for counting weekkilometer
	 * @var int 
	 */
	static $DAYS_FOR_WEEK_KM_MIN = 70;

	/**
	 * Number of days for counting longjogs
	 * @var int
	 */
	static $DAYS_FOR_LONGJOGS = 70;

	/**
	 * Percentage for weekkilometer
	 * @var double
	 */
	static $PERCENTAGE_WEEK_KM = 0.67;

	/**
	 * Percentage for longjogs
	 * @var double
	 */
	static $PERCENTAGE_LONGJOGS = 0.33;

	/**
	 * Get const for BASIC_ENDURANCE
	 * @return int
	 */
	public static function getConst() {
		if (!defined('CONF_BASIC_ENDURANCE')) {
			Error::getInstance()->addError('Constant CONF_BASIC_ENDURANCE has to be set!');
			define('CONF_BASIC_ENDURANCE', 0);
		}

		if (defined('BASIC_ENDURANCE'))
			return BASIC_ENDURANCE;

		if (CONF_BASIC_ENDURANCE == 0)
			return self::recalculateValue();

		return CONF_BASIC_ENDURANCE;
	}

	/**
	 * Recalculate value
	 */
	public static function recalculateValue() {
		$BASIC_ENDURANCE = self::calculateValue();

		ConfigValue::update('BASIC_ENDURANCE', $BASIC_ENDURANCE);

		return $BASIC_ENDURANCE;
	}

	/**
	 * Calculate value
	 * @return int
	 */
	static private function calculateValue() {
		return self::value(true);
	}

	/**
	 * Calculating value
	 * @uses DAY_IN_S
	 * @param bool $as_int as normal integer, default: false
	 * @param int $timestamp [optional] timestamp
	 * @param boolean $returnArrayWithResults [optional]
	 */
	public static function value($as_int = false, $timestamp = 0, $returnArrayWithResults = false) {
		// TODO: If you change the algorithm, remember to change info in 'RunalyzePluginPanel_Rechenspiele'
		// TODO: Unittests
		if ($timestamp == 0)
			$timestamp = time();

		if (VDOT_FORM == 0)
			return ($as_int) ? 0 : '0 &#37;';

		$DataSum       = Mysql::getInstance()->fetchSingle( self::getQuery($timestamp) );
		$WeekKmResult  = isset($DataSum['km']) ? $DataSum['km'] : 0;
		$LongjogResult = isset($DataSum['sum']) ? $DataSum['sum'] : 0;

		$WeekPercentage    = $WeekKmResult * 7 / self::getDaysForWeekKm() / self::getTargetWeekKm();
		$LongjogPercentage = $LongjogResult * 7 / self::$DAYS_FOR_LONGJOGS;
		$Percentage        = round( 100 * ( $WeekPercentage*self::$PERCENTAGE_WEEK_KM + $LongjogPercentage*self::$PERCENTAGE_LONGJOGS ) );

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
	 * Get query
	 * @param int $timestamp [optional]
	 * @param boolean $onlyLongjogs [optional]
	 * @return string
	 */
	static public function getQuery($timestamp = 0, $onlyLongjogs = false) {
		if ($timestamp == 0)
			$timestamp = time();

		$StartTimeForLongjogs = $timestamp - self::$DAYS_FOR_LONGJOGS * DAY_IN_S;
		$StartTimeForWeekKm   = $timestamp - self::getDaysForWeekKm() * DAY_IN_S;

		if ($onlyLongjogs) {
			return '
				SELECT
					`id`,
					`time`,
					`distance`,
					IF (
						`distance` > '.self::$MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.self::$DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.self::$MIN_KM_FOR_LONGJOG.')/'.self::getTargetLongjogKmPerWeek().',2)
						),
						0
					) as `points`
				FROM '.PREFIX.'training
				WHERE sportid='.CONF_RUNNINGSPORT.' AND time<='.$timestamp.' AND distance>'.self::$MIN_KM_FOR_LONGJOG.' AND time>='.$StartTimeForLongjogs.'';
		}

		return '
			SELECT
				SUM(
					IF (time >= '.$StartTimeForWeekKm.', `distance`, 0)
				) as `km`,
				SUM(
					IF (
						`distance` > '.self::$MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.self::$DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.self::$MIN_KM_FOR_LONGJOG.')/'.self::getTargetLongjogKmPerWeek().',2)
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
	static public function getDaysForWeekKm() {
		$diff = Time::diffInDays(START_TIME);

		if ($diff > self::$DAYS_FOR_WEEK_KM)
			return self::$DAYS_FOR_WEEK_KM;
		elseif ($diff < self::$DAYS_FOR_WEEK_KM_MIN)
			return self::$DAYS_FOR_WEEK_KM_MIN;

		return $diff;
	}

	/**
	 * Get target week km
	 * @return double
	 */
	static public function getTargetWeekKm() {
		return pow(VDOT_FORM, 1.135);
	}

	/**
	 * Get target longjog km per week
	 * PAY ATTENTION: self::$MIN_KM_FOR_LONGJOG is already subtracted!
	 * @return double
	 */
	static public function getTargetLongjogKmPerWeek() {
		if (VDOT_FORM == 0)
			return 1;

		return log(VDOT_FORM/4) * 12 - self::$MIN_KM_FOR_LONGJOG;
	}

	/**
	 * Get (real) target longjog km per week
	 * @return double
	 */
	static public function getRealTargetLongjogKmPerWeek() {
		return self::getTargetLongjogKmPerWeek() + self::$MIN_KM_FOR_LONGJOG;
	}
}