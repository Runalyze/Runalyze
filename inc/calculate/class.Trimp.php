<?php
// TODO
class Trimp {
	static private $FACTOR_MALE_A = 0.64;
	static private $FACTOR_MALE_B = 1.92;
	static private $FACTOR_FEMALE_A = 0.86;
	static private $FACTOR_FEMALE_B = 1.67;

	/**
	 * Disable constructor for public access 
	 */
	private function __construct() {}

	static public function maxATL() {
		
	}

	/**
	 * Get current ATL in percent
	 * @return double
	 */
	static public function currentATL() {
		return 0;
	}

	/**
	 * Get the TRIMP for a training or get the minutes needed for a given TRIMP
	 * @param int $training_id   Training-ID
	 * @param bool $trimp        [optional] If set, calculate backwards, default: false     
	 * @return int
	 */
	public static function _TRIMP($training_id, $trimp = false) {
		$dat = Mysql::getInstance()->fetch(PREFIX.'training', $training_id);
		if ($dat === false)
			$dat = array();

		$factor_a  = (CONF_GENDER == 'm') ? self::$FACTOR_MALE_A : self::$FACTOR_FEMALE_A;
		$factor_b  = (CONF_GENDER == 'm') ? self::$FACTOR_MALE_B : self::$FACTOR_FEMALE_B;
		$sportid   = ($dat['sportid'] != 0) ? $dat['sportid'] : CONF_MAINSPORT;
		$Sport     = new Sport($sportid);
		if ($Sport->hasTypes() && $dat['typeid'] != 0)
			$Type  = new Type($dat['typeid']);
		$HFavg     = ($dat['pulse_avg'] != 0) ? $dat['pulse_avg'] : $Sport->avgHF();
		$RPE       = (isset($Type)) ? $Type->RPE() : $Sport->RPE();
		$HFperRest = ($HFavg - HF_REST) / (HF_MAX - HF_REST);
		$TRIMP     = $dat['s']/60 * $HFperRest * $factor_a * exp($factor_b * $HFperRest) * $RPE / 10;

		// Berechnung mit Trainingszonen waere:
		// 50%-60% (zone 1), 60%-70% (zone 2), 70%-80% (zone 3), 80%-90% (zone 4) and 90%-100% (zone 5)
		// default settings are 1 (zone 1), 1.1 (zone 2), 1.2 (zone 3), 2.2 (zone 4), and 4.5 (zone 5)
	
		if ($trimp === false)
			return round($TRIMP);

		// Anzahl der noetigen Minuten fuer $back als TRIMP-Wert
		return $trimp / ( $HFperRest * $factor_a * exp($factor_b * $HFperRest) * 5.35 / 10 );
	}

	/**
	 * Calculating ActualTrainingLoad (at a given timestamp)
	 * @uses CONF_ATL_DAYS
	 * @uses DAY_IN_S
	 * @param int $time [optional] timestamp
	 */
	public static function ATL($time = 0) {
		if ($time == 0)
			$time = time();

		$dat = Mysql::getInstance()->fetch('SELECT SUM(`trimp`) as `sum` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($time-CONF_ATL_DAYS*DAY_IN_S).' AND "'.$time.'"');
		return round($dat['sum']/CONF_ATL_DAYS);
	}

	/**
	 * Calculating ChronicTrainingLoad (at a given timestamp)
	 * @uses CONF_CTL_DAYS
	 * @uses DAY_IN_S
	 * @param int $time [optional] timestamp
	 */
	public static function CTL($time = 0) {
		if ($time == 0)
			$time = time();

		$dat = Mysql::getInstance()->fetch('SELECT SUM(`trimp`) as `sum` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($time-CONF_CTL_DAYS*DAY_IN_S).' AND "'.$time.'"');
		return round($dat['sum']/CONF_CTL_DAYS);
	}

	/**
	 * Calculating TrainingStressBalance (at a given timestamp)
	 * @uses self::CTL
	 * @uses self::ATL
	 * @param int $time [optional] timestamp
	 */
	public static function TSB($time = 0) {
		return self::CTL($time) - self::ATL($time);
	}
}