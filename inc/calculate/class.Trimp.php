<?php
/**
 * Class for calculating Training Load (ATL, CTL, TRIMP)
 * @author Hannes Christiansen <mail@laufhannes.de> 
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
	 * Disable constructor for public access 
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
	 * Get the TRIMP for a training or get the minutes needed for a given TRIMP
	 * @param int $trainingId     Training-ID
	 * @param mixed $trimpToReach [optional] If set, calculate backwards to this value, default: false     
	 * @return int
	 */
	static public function TRIMPfor($trainingId, $trimpToReach = false) {
		// Berechnung mit Trainingszonen waere:
		// 50%-60% (zone 1), 60%-70% (zone 2), 70%-80% (zone 3), 80%-90% (zone 4) and 90%-100% (zone 5)
		// default settings are 1 (zone 1), 1.1 (zone 2), 1.2 (zone 3), 2.2 (zone 4), and 4.5 (zone 5)
		$Training    = new Training($trainingId);
		$HFperRest   = ($Training->avgHF() - HF_REST) / (HF_MAX - HF_REST);
		$TrimpFactor = $HFperRest * self::factorA() * exp(self::factorB() * $HFperRest);

		if ($trimpToReach !== false)
			return $trimpToReach / ( TrimpFactor * 5.35 / 10 );

		$Trimp = round($Training->get('s')/60 * $TrimpFactor * $Training->RPE() / 10);

		if ($Trimp > self::$MAX_TRIMP)
			self::setMaxTRIMP($Trimp);

		return $Trimp;
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
		if ($time == 0)
			$time = time();

		$dat = Mysql::getInstance()->fetch('SELECT SUM(`trimp`) as `sum` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($time-CONF_ATL_DAYS*DAY_IN_S).' AND "'.$time.'"');
		$ATL = round($dat['sum']/CONF_ATL_DAYS);

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
		if ($time == 0)
			$time = time();

		$dat = Mysql::getInstance()->fetch('SELECT SUM(`trimp`) as `sum` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($time-CONF_CTL_DAYS*DAY_IN_S).' AND "'.$time.'"');
		$CTL = round($dat['sum']/CONF_CTL_DAYS);

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
	 * Calculate max values for atl/ctl/trimp again
	 * Calculations are implemented again because normal ones are too slow
	 * ATL/CTL: SUM(`trimp`) for CONF_ATL_DAYS / CONF_CTL_DAYS
	 * Attention: Values must not be zero!
	 */
	public static function calculateMaxValues() {
		$start_i = 365*START_YEAR;
		$end_i   = 365*(date("Y") + 1) - $start_i;
		$Trimp   = array_fill(0, $end_i, 0);
		$Data    = Mysql::getInstance()->fetchAsArray('
			SELECT
				YEAR(FROM_UNIXTIME(`time`)) as `y`,
				DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
				SUM(`trimp`) as `trimp`
			FROM `'.PREFIX.'training`
			GROUP BY `y`, `d`
			ORDER BY `y` ASC, `d` ASC');
		
		if (empty($Data)) {
			self::setMaxATL(1);
			self::setMaxCTL(1);
			self::setMaxTRIMP(1);

			return;
		}
		
		$maxATL  = 1;
		$maxCTL  = 1;
		
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
		Config::update('MAX_ATL', $maxATL);

		self::$MAX_ATL = $maxATL;
	}

	/**
	 * Set MAX_CTL
	 * @param int $maxCTL 
	 */
	private static function setMaxCTL($maxCTL) {
		Config::update('MAX_CTL', $maxCTL);

		self::$MAX_CTL = $maxCTL;
	}

	/**
	 * Set MAX_TRIMP
	 * @param int $maxTRIMP 
	 */
	private static function setMaxTRIMP($maxTRIMP) {
		Config::update('MAX_TRIMP', $maxTRIMP);

		self::$MAX_TRIMP = $maxTRIMP;
	}
}