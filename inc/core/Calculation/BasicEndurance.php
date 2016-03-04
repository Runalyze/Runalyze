<?php
/**
 * This file contains class::BasicEndurance
 * @package Runalyze\Calculation
 */

namespace Runalyze\Calculation;

use Runalyze\Configuration;
use Runalyze\Util\Time;

use DB;

/**
 * BasicEndurance
 * 
 * This class can calculated a value for the basic endurance.
 * A value of 100 represents a fully sufficient training for an optimal marathon.
 * The requirements for a sufficient basic endurance are calculated from the given VDOT.
 * 
 * This class can use the settings from configuration or with own settings.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation
 */
class BasicEndurance {
	/**
	 * Value for basic endurance
	 * 
	 * This value refers to the constant configuration value.
	 * @var int
	 */
	private static $CONST_VALUE = false;

	/**
	 * VDOT to use
	 * @var int
	 */
	protected $VDOT = 0;

	/**
	 * Minimum distance to be recognized as a longjog
	 * @var double
	 */
	protected $MIN_KM_FOR_LONGJOG = 13;

	/**
	 * Number of days for counting weekkilometer
	 * @var int 
	 */
	protected $DAYS_FOR_WEEK_KM = 182;

	/**
	 * Minimum number of days for counting weekkilometer
	 * @var int 
	 */
	protected $DAYS_FOR_WEEK_KM_MIN = 70;

	/**
	 * Number of days for counting longjogs
	 * @var int
	 */
	protected $DAYS_FOR_LONGJOGS = 70;

	/**
	 * Percentage for weekkilometer
	 * @var double
	 */
	protected $PERCENTAGE_WEEK_KM = 0.67;

	/**
	 * Percentage for longjogs
	 * @var double
	 */
	protected $PERCENTAGE_LONGJOGS = 0.33;

	/**
	 * Read settings from configuration
	 */
	public function readSettingsFromConfiguration() {
		$this->VDOT = Configuration::Data()->vdot();

		$BasicEndurance = Configuration::BasicEndurance();
		$this->MIN_KM_FOR_LONGJOG   = $BasicEndurance->minKmForLongjog();
		$this->DAYS_FOR_WEEK_KM     = $BasicEndurance->daysForWeekKm();
		$this->DAYS_FOR_WEEK_KM_MIN = $BasicEndurance->daysForWeekKmMin();
		$this->DAYS_FOR_LONGJOGS    = $BasicEndurance->daysForLongjogs();
		$this->PERCENTAGE_WEEK_KM   = $BasicEndurance->percentageWeekKm();
		$this->PERCENTAGE_LONGJOGS  = $BasicEndurance->percentageLongjogs();
	}

	/**
	 * Set vdot
	 * 
	 * Setting VDOT is required to calculated requirements for a sufficient basic endurance
	 * @param float $VDOT vdot
	 */
	public function setVDOT($VDOT) {
		$this->VDOT = $VDOT;
	}

	/**
	 * Get used VDOT
	 * @return float
	 */
	public function getUsedVDOT() {
		return $this->VDOT;
	}

	/**
	 * Set minimal distance for longjogs
	 * 
	 * Only trainings above a given distance are treated as "longjogs".
	 * @param float $km minimal distance for longjog
	 */
	public function setMinimalDistanceForLongjogs($km) {
		$this->MIN_KM_FOR_LONGJOG = $km;
	}

	/**
	 * Get minimal distance for longjogs
	 * @return int
	 */
	public function getMinimalDistanceForLongjogs() {
		return $this->MIN_KM_FOR_LONGJOG;
	}

	/**
	 * Set days to recognize for week kilometer
	 * @see setMinimalDaysToRecognizeForWeekKilometer()
	 * @param int $days number of days
	 */
	public function setDaysToRecognizeForWeekKilometer($days) {
		$this->DAYS_FOR_WEEK_KM = $days;
	}

	/**
	 * Get days to recognize for week kilometer
	 * @return int
	 */
	public function getDaysToRecognizeForWeekKilometer() {
		return $this->DAYS_FOR_WEEK_KM;
	}

	/**
	 * Set minimal number of days to recognize for week kilometer
	 * 
	 * For new users it's senseless to look one year back, but it's senseless to look only one week back.
	 * Therefore a minimal number of days can be specified.
	 * @param int $days number of days
	 */
	public function setMinimalDaysToRecognizeForWeekKilometer($days) {
		$this->DAYS_FOR_WEEK_KM_MIN = $days;
	}

	/**
	 * Get minimal number of days to recognize for week kilometer
	 * @return int
	 */
	public function getMinimalDaysToRecognizeForWeekKilometer() {
		return $this->DAYS_FOR_WEEK_KM_MIN;
	}

	/**
	 * Set days to recognize for jongjogs
	 * @param int $days number of days
	 */
	public function setDaysToRecognizeForLongjogs($days) {
		$this->DAYS_FOR_LONGJOGS = $days;
	}

	/**
	 * Get days to recognize for jongjogs
	 * @return int
	 */
	public function getDaysToRecognizeForLongjogs() {
		return $this->DAYS_FOR_LONGJOGS;
	}

	/**
	 * Set percentage for week kilometer
	 * 
	 * Percentage has to be between 0 and 1. A value higher than 1 will be treated as 1.
	 * @param int $percentage percentage between 0 and 1
	 */
	public function setPercentageForWeekKilometer($percentage) {
		$this->PERCENTAGE_WEEK_KM  = min(1, abs($percentage));
		$this->PERCENTAGE_LONGJOGS = 1 - $this->PERCENTAGE_WEEK_KM;
	}

	/**
	 * Get percentage for week kilometer
	 * @return float
	 */
	public function getPercentageForWeekKilometer() {
		return $this->PERCENTAGE_WEEK_KM;
	}

	/**
	 * Set percentage for longjogs
	 * 
	 * Percentage has to be between 0 and 1. A value higher than 1 will be treated as 1.
	 * @param int $percentage percentage between 0 and 1
	 */
	public function setPercentageForLongjogs($percentage) {
		$this->PERCENTAGE_LONGJOGS = min(1, abs($percentage));
		$this->PERCENTAGE_WEEK_KM  = 1 - $this->PERCENTAGE_LONGJOGS;
	}

	/**
	 * Get percentage for longjogs
	 * @return float
	 */
	public function getPercentageForLongjogs() {
		return $this->PERCENTAGE_LONGJOGS;
	}

	/**
	 * Get values as array
	 * @param int $timestamp [optional] timestamp
	 * @return array array('weekkm-result', 'longjog-result', 'weekkm-percentage', 'longjog-percentage', 'percentage')
	 */
	public function asArray($timestamp = 0) {
		if ($timestamp == 0)
			$timestamp = time();

		// If you change the algorithm, remember to change *info* in 'RunalyzePluginPanel_Rechenspiele'.
		$DataSum = DB::getInstance()->query( $this->getQuery($timestamp) )->fetch();
		$Result  = array();
		$Result['weekkm-result']      = isset($DataSum['km']) ? $DataSum['km'] : 0;
		$Result['longjog-result']     = isset($DataSum['sum']) ? $DataSum['sum'] : 0;

		$Result['weekkm-percentage']  = $Result['weekkm-result'] * 7 / $this->getDaysForWeekKm() / $this->getTargetWeekKm();
		$Result['longjog-percentage'] = $Result['longjog-result'] * 7 / $this->DAYS_FOR_LONGJOGS;
		$Result['percentage']         = round( 100 * ( $Result['weekkm-percentage']*$this->PERCENTAGE_WEEK_KM + $Result['longjog-percentage']*$this->PERCENTAGE_LONGJOGS ) );

		return $Result;
	}

	/**
	 * Get value
	 * 
	 * The calculated value can be higher than 100.
	 * To get a limited percentage, @see valueInPercent()
	 * 
	 * @param int $timestamp [optional] timestamp
	 * @return int calculated value
	 */
	public function value($timestamp = 0) {
		$Result = $this->asArray($timestamp);

		return $Result['percentage'];
	}

	/**
	 * Value in percent
	 * @param int $timestamp [optional] timestamp
	 * @return string
	 */
	public function valueInPercent($timestamp = 0) {
		return min(100, max(0, $this->value($timestamp))).' &#37;';
	}

	/**
	 * Get query
	 * @param int $timestamp [optional]
	 * @param boolean $onlyLongjogs [optional]
	 * @return string
	 */
	public function getQuery($timestamp = 0, $onlyLongjogs = false) {
		if ($timestamp == 0)
			$timestamp = time();

		$StartTimeForLongjogs = $timestamp - $this->DAYS_FOR_LONGJOGS * DAY_IN_S;
		$StartTimeForWeekKm   = $timestamp - $this->getDaysForWeekKm() * DAY_IN_S;

		if ($onlyLongjogs) {
			return '
				SELECT
					`id`,
					`time`,
					`distance`,
					IF (
						`distance` > '.$this->MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.$this->DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.$this->MIN_KM_FOR_LONGJOG.')/'.$this->getTargetLongjogKmPerWeek().',2)
						),
						0
					) as `points`
				FROM '.PREFIX.'training
				WHERE `accountid`='.\SessionAccountHandler::getId().' AND `time` BETWEEN '.$StartTimeForLongjogs.' AND '.$timestamp.' AND `sportid`='.Configuration::General()->runningSport().' AND distance>'.$this->MIN_KM_FOR_LONGJOG;
		}

		return '
			SELECT
				SUM(
					IF (time >= '.$StartTimeForWeekKm.', `distance`, 0)
				) as `km`,
				SUM(
					IF (
						`distance` > '.$this->MIN_KM_FOR_LONGJOG.' AND time >= '.$StartTimeForLongjogs.',
						(
							(2 - (2/'.$this->DAYS_FOR_LONGJOGS.') * ( ('.$timestamp.' - `time`) / '.DAY_IN_S.' ) )
							* POW((`distance`-'.$this->MIN_KM_FOR_LONGJOG.')/'.$this->getTargetLongjogKmPerWeek().',2)
						),
						0
					)
				) as `sum`
			FROM '.PREFIX.'training
			WHERE `accountid`='.\SessionAccountHandler::getId().' AND `time` BETWEEN '.min($StartTimeForLongjogs,$StartTimeForWeekKm).' AND '.$timestamp.' AND `sportid`='.Configuration::General()->runningSport().'
			GROUP BY accountid
			LIMIT 1';
	}

	/**
	 * Get days used for week km for basic endurance
	 * @return double 
	 */
	public function getDaysForWeekKm() {
		$diff = Time::diffInDays(START_TIME);

		if ($diff > $this->DAYS_FOR_WEEK_KM)
			return $this->DAYS_FOR_WEEK_KM;
		elseif ($diff < $this->DAYS_FOR_WEEK_KM_MIN)
			return $this->DAYS_FOR_WEEK_KM_MIN;

		return $diff;
	}

	/**
	 * Get target week km
	 * @return double
	 */
	public function getTargetWeekKm() {
		return pow(max($this->VDOT, 10), 1.135);
	}

	/**
	 * Get target longjog km per week
	 * PAY ATTENTION: $MIN_KM_FOR_LONGJOG is already subtracted!
	 * @return double
	 */
	public function getTargetLongjogKmPerWeek() {
		if ($this->VDOT == 0)
			return 1;

		return log($this->VDOT/4) * 12 - $this->MIN_KM_FOR_LONGJOG;
	}

	/**
	 * Get (real) target longjog km per week
	 * @return double
	 */
	public function getRealTargetLongjogKmPerWeek() {
		return $this->getTargetLongjogKmPerWeek() + $this->MIN_KM_FOR_LONGJOG;
	}



	/**
	 * Get const for BASIC_ENDURANCE
	 * @return int
	 */
	public static function getConst() {
		if (self::$CONST_VALUE === false) {
			if (Configuration::Data()->basicEndurance() != 0)
				self::$CONST_VALUE = Configuration::Data()->basicEndurance();
			else
				self::recalculateValue();
		}

		return self::$CONST_VALUE;
	}

	/**
	 * Recalculate value
	 */
	public static function recalculateValue() {
		$Object = new self;
		$Object->readSettingsFromConfiguration();
		$BASIC_ENDURANCE = $Object->value();

		Configuration::Data()->updateBasicEndurance($BASIC_ENDURANCE);

		self::$CONST_VALUE = $BASIC_ENDURANCE;
	}
}