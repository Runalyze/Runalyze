<?php
/**
 * This file contains class::Trimp
 * @package Runalyze\Calculations
 */
/**
 * Class for calculating Training Load (ATL, CTL, TRIMP)
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class Trimp {
	/**
	 * Factor A for male
	 * @var double
	 */
	static private $FACTOR_MALE_A = 0.64;

	/**
	 * Factor B for male
	 * @var double
	 */
	static private $FACTOR_MALE_B = 1.92;

	/**
	 * Factor A for female
	 * @var double
	 */
	static private $FACTOR_FEMALE_A = 0.86;

	/**
	 * Factor B for female
	 * @var double
	 */
	static private $FACTOR_FEMALE_B = 1.67;

	/**
	 * Maximum value for ATL
	 * @var int
	 */
	static private $MAX_ATL = CONF_MAX_ATL;

	/**
	 * Maximum value for CTL
	 * @var int
	 */
	static private $MAX_CTL = CONF_MAX_CTL;

	/**
	 * Maximum value for TRIMP
	 * @var int
	 */
	static private $MAX_TRIMP = CONF_MAX_TRIMP;

	/**
	 * Constructor is private
	 */
	private function __construct() {}

	/**
	 * Get maximum ATL
	 * @return int
	 */
	static public function maxATL() {
		if (self::$MAX_ATL == 0)
			self::calculateMaxValues();

		return self::$MAX_ATL;
	}

	/**
	 * Get maximum CTL
	 * @return int
	 */
	static public function maxCTL() {
		if (self::$MAX_CTL == 0)
			self::calculateMaxValues();

		return self::$MAX_CTL;
	}

	/**
	 * Get maximum TRIMP
	 * @return int
	 */
	static public function maxTRIMP() {
		if (self::$MAX_TRIMP == 0)
			self::calculateMaxValues();

		return self::$MAX_TRIMP;
	}

	/**
	 * Get factor A for calculation
	 * @return int
	 */
	static private function factorA() {
		return UserData::isMale() ? self::$FACTOR_MALE_A : self::$FACTOR_FEMALE_A;
	}

	/**
	 * Get factor B for calculation
	 * @return int
	 */
	static private function factorB() {
		return UserData::isMale() ? self::$FACTOR_MALE_B : self::$FACTOR_FEMALE_B;
	}

	/**
	 * Check for max values at a given timestamp
	 * @param int $time 
	 */
	static public function checkForMaxValuesAt($time) {
		self::ATL($time);
		self::CTL($time);
	}

	/**
	 * Get colored string for a given trimp value
	 * @param int $trimp
	 * @return string 
	 */
	static public function coloredString($trimp) {
		return Running::StresscoloredString($trimp);
	}

	/**
	 * Get minutes need to reach a given TRIMP-value
	 * @param float $trimpToReach
	 * @return float in minutes
	 */
	static public function minutesForTrimp($trimpToReach) {
		$Sport = new Sport(CONF_MAINSPORT);

		return $trimpToReach / ( self::TrimpFactor($Sport->avgHF()) * 5.35 / 10);
	}

	/**
	 * Get TRIMP for a given training by array
	 * @param type $trainingData
	 * @return int 
	 */
	static public function forTraining(array $trainingData) {
		if (!isset($trainingData['pulse_avg']) || !isset($trainingData['s']) || !isset($trainingData['typeid']) || !isset($trainingData['sportid'])) {
			if (!isset($trainingData['id']))
				return 0;

			$trainingData = DB::getInstance()->query('SELECT `id`, `pulse_avg`, `s`, `typeid`, `sportid` FROM `'.PREFIX.'training` WHERE `id`="'.(int)$trainingData['id'].'" LIMIT 1')->fetch();
		}

		$Training = new TrainingObject($trainingData['id']);
		if ($Training->GpsData()->getTotalTime() == 0) $Training = new TrainingObject($trainingData);
		$avgHF    = $Training->avgHF();
		$s        = $Training->getTimeInSeconds();
		$RPE      = $Training->RPE();

		$HRzonearr=$Training->GpsData()->getPulseZonesBy5();
		if (count($HRzonearr)>0){
			$Trimp = 0;
			foreach ($HRzonearr as $zone=>$data){
				$zone=$zone/100-.025;
				$Trimp += round($data['time']/60 * $zone * self::factorA() * exp(self::factorB() * $zone));
			}
		} else
			$Trimp = round($s/60 * self::TrimpFactor($avgHF));


		if ($Trimp > self::$MAX_TRIMP)
			self::setMaxTRIMP($Trimp);

		return $Trimp;
	}

	/**
	 * Get TRIMP for a given training by ID
	 * @param int $trainingID
	 * @return int
	 */
	static public function forTrainingID($trainingID) {
		return self::forTraining(array('id' => $trainingID));
	}

	/**
	 * Get trimp factor
	 * @param int $avgHF
	 * @return float 
	 */
	static private function TrimpFactor($avgHF) {
		$HFperRest = ($avgHF - HF_REST) / (HF_MAX - HF_REST);

		return $HFperRest * self::factorA() * exp(self::factorB() * $HFperRest);
	}

	/**
	 * Get ATL in percent
	 * @param int $time [optional] timestamp
	 * @return double
	 */
	static public function ATLinPercent($time = 0) {
		return round(100*self::ATL($time)/self::maxATL());
	}

	/**
	 * Get CTL in percent
	 * @param int $time [optional] timestamp
	 * @return double
	 */
	static public function CTLinPercent($time = 0) {
		return round(100*self::CTL($time)/self::maxCTL());
	}

	/**
	 * Calculating ActualTrainingLoad (at a given timestamp)
	 * @uses CONF_ATL_DAYS
	 * @uses DAY_IN_S
	 * @param int $time [optional] timestamp
	 */
	static public function ATL($time = 0) {
		if ($time == 0) {
			$time = time();
		}

		$time = mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));

		$Data = DB::getInstance()->query('
			SELECT
				SUM(`trimp`) as `sum`
			FROM `'.PREFIX.'training`
			WHERE `time` BETWEEN '.($time - CONF_ATL_DAYS*DAY_IN_S).' AND '.$time.'
			LIMIT 1
		')->fetch();

		$ATL = round($Data['sum']/CONF_ATL_DAYS);

		if ($ATL > self::maxATL())
			self::setMaxATL($ATL);

		return $ATL;
	}

	/**
	 * Calculating ChronicTrainingLoad (at a given timestamp)
	 * @uses CONF_CTL_DAYS
	 * @uses DAY_IN_S
	 * @param int $time [optional] timestamp
	 */
	static public function CTL($time = 0) {
		if ($time == 0) {
			$time = time();
		}

		$time = mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));

		$Data = DB::getInstance()->query('
			SELECT
				SUM(`trimp`) as `sum`
			FROM `'.PREFIX.'training`
			WHERE `time` BETWEEN '.($time - CONF_CTL_DAYS*DAY_IN_S).' AND '.$time.'
			LIMIT 1
		')->fetch();

		$CTL = round($Data['sum']/CONF_CTL_DAYS);

		if ($CTL > self::maxCTL())
			self::setMaxCTL($CTL);

		return $CTL;
	}

	/**
	 * Calculating TrainingStressBalance (at a given timestamp)
	 * @uses self::CTL
	 * @uses self::ATL
	 * @param int $time [optional] timestamp
	 */
	static public function TSB($time = 0) {
		return self::CTL($time) - self::ATL($time);
	}

	/**
	 * Get array with ATL/CTL (in percent) and TSB - faster than single calls
	 * @param int $time [optional] timestamp
	 * @return array array('ATL' => ..., 'CTL' => ..., 'TSB' => ...)
	 */
	static public function arrayForATLandCTLandTSBinPercent($time = 0) {
		$ATL = self::ATL($time);
		$CTL = self::CTL($time);

		return array(
			'ATL'	=> round(100*$ATL/self::maxATL()),
			'CTL'	=> round(100*$CTL/self::maxCTL()),
			'TSB'	=> $CTL - $ATL
		);
	}

	/**
	 * Calculate max values for atl/ctl/trimp again
	 * Calculations are implemented again because normal ones are too slow
	 * ATL/CTL: SUM(`trimp`) for CONF_ATL_DAYS / CONF_CTL_DAYS
	 * Attention: Values must not be zero!
	 */
	public static function calculateMaxValues() {
		$start_i = 365*START_YEAR;
		$end_i   = 365*(date("Y") + 1) - $start_i;
		$Trimp   = array_fill(0, $end_i, 0);
		$maxATL  = 1;
		$maxCTL  = 1;

		$Data    = DB::getInstance()->query('
			SELECT
				YEAR(FROM_UNIXTIME(`time`)) as `y`,
				DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
				SUM(`trimp`) as `trimp`
			FROM `'.PREFIX.'training`
			GROUP BY `y`, `d`
			ORDER BY `y` ASC, `d` ASC
		')->fetchAll();
		
		foreach ($Data as $dat) {
			$atl           = 0;
			$ctl           = 0;
			$index         = $dat['y']*365 + $dat['d'] - $start_i;
			$Trimp[$index] = $dat['trimp'];

			if ($index >= CONF_ATL_DAYS)
				$atl   = array_sum(array_slice($Trimp, 1 + $index - CONF_ATL_DAYS, CONF_ATL_DAYS)) / CONF_ATL_DAYS;
			if ($index >= CONF_CTL_DAYS)
				$ctl   = array_sum(array_slice($Trimp, 1 + $index - CONF_CTL_DAYS, CONF_CTL_DAYS)) / CONF_CTL_DAYS;

			if ($atl > $maxATL)
				$maxATL = $atl;
			if ($ctl > $maxCTL)
				$maxCTL = $ctl;
		}

		self::setMaxATL($maxATL);
		self::setMaxCTL($maxCTL);
		self::setMaxTRIMP( max(max($Trimp), 1) );
	}

	/**
	 * Set MAX_ATL
	 * @param int $maxATL 
	 */
	private static function setMaxATL($maxATL) {
		ConfigValue::update('MAX_ATL', $maxATL);

		self::$MAX_ATL = $maxATL;
	}

	/**
	 * Set MAX_CTL
	 * @param int $maxCTL 
	 */
	private static function setMaxCTL($maxCTL) {
		ConfigValue::update('MAX_CTL', $maxCTL);

		self::$MAX_CTL = $maxCTL;
	}

	/**
	 * Set MAX_TRIMP
	 * @param int $maxTRIMP 
	 */
	private static function setMaxTRIMP($maxTRIMP) {
		if (is_nan($maxTRIMP))
			return;

		ConfigValue::update('MAX_TRIMP', $maxTRIMP);

		self::$MAX_TRIMP = $maxTRIMP;
	}
}