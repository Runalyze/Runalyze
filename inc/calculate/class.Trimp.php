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
	const FACTOR_MALE_A = 0.64;

	/**
	 * Factor B for male
	 * @var double
	 */
	const FACTOR_MALE_B = 1.92;

	/**
	 * Factor A for female
	 * @var double
	 */
	const FACTOR_FEMALE_A = 0.86;

	/**
	 * Factor B for female
	 * @var double
	 */
	const FACTOR_FEMALE_B = 1.67;

	/**
	 * Maximum value for ATL
	 * @var int
	 */
	static private $MAX_ATL = -1;

	/**
	 * Maximum value for CTL
	 * @var int
	 */
	static private $MAX_CTL = -1;

	/**
	 * Maximum value for TRIMP
	 * @var int
	 */
	static private $MAX_TRIMP = -1;

	/**
	 * Constructor is private
	 */
	private function __construct() {}

	/**
	 * Get maximum ATL
	 * @return int
	 */
	static public function maxATL() {
		if (self::$MAX_ATL == -1)
			self::$MAX_ATL = Configuration::Data()->maxATL();

		if (self::$MAX_ATL == 0)
			self::calculateMaxValues();

		return self::$MAX_ATL;
	}

	/**
	 * Get maximum CTL
	 * @return int
	 */
	static public function maxCTL() {
		if (self::$MAX_CTL == -1)
			self::$MAX_CTL = Configuration::Data()->maxCTL();

		if (self::$MAX_CTL == 0)
			self::calculateMaxValues();

		return self::$MAX_CTL;
	}

	/**
	 * Get maximum TRIMP
	 * @return int
	 */
	static public function maxTRIMP() {
		if (self::$MAX_TRIMP == -1)
			self::$MAX_TRIMP = Configuration::Data()->maxTrimp();

		if (self::$MAX_TRIMP == 0)
			self::calculateMaxValues();

		return self::$MAX_TRIMP;
	}

	/**
	 * Get factor A for calculation
	 * @return int
	 */
	static public function factorA() {
		return Configuration::General()->gender()->isMale() ? self::FACTOR_MALE_A : self::FACTOR_FEMALE_A;
	}

	/**
	 * Get factor B for calculation
	 * @return int
	 */
	static public function factorB() {
		return Configuration::General()->gender()->isMale() ? self::FACTOR_MALE_B : self::FACTOR_FEMALE_B;
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
	 * Get TRIMP for a given training by array
	 * @param type $trainingData
	 * @return int 
	 */
	static public function forTraining(array $trainingData) {
		if (!isset($trainingData['pulse_avg']) || !isset($trainingData['s']) || !isset($trainingData['typeid']) || !isset($trainingData['sportid'])) {
			if (!isset($trainingData['id']))
				return 0;

			$trainingData = DB::getInstance()->query('SELECT `id`, `pulse_avg`, `arr_heart`, `arr_time`, `s`, `typeid`, `sportid` FROM `'.PREFIX.'training` WHERE `id`="'.(int)$trainingData['id'].'" LIMIT 1')->fetch();
		}

		$Training = new TrainingObject($trainingData['id']);

		if ($Training->hasArrayHeartrate()) {
			$Collector = new \Runalyze\Calculation\Trimp\DataCollector($Training->getArrayHeartrate(), $Training->getArrayTime());
			$data = $Collector->result();
		} else {
			$data = array($Training->getPulseAvg() => $Training->getTimeInSeconds());
		}

		$Athlete = \Runalyze\Context::Athlete();
		$Calculator = new \Runalyze\Calculation\Trimp\Calculator($Athlete, $data);

		$Trimp = round($Calculator->value());

		/*if ($Training->GpsData()->getTotalTime() == 0) $Training = new TrainingObject($trainingData);
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
			$Trimp = round($s/60 * self::TrimpFactor($avgHF));*/


		if ($Trimp > self::maxTRIMP())
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
	static public function TrimpFactor($avgHF) {
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
			WHERE `time` BETWEEN '.($time - Configuration::Trimp()->daysForATL()*DAY_IN_S).' AND '.$time.'
			LIMIT 1
		')->fetch();

		$ATL = round($Data['sum']/Configuration::Trimp()->daysForATL());

		if ($ATL > self::maxATL())
			self::setMaxATL($ATL);

		return $ATL;
	}

	/**
	 * Calculating ChronicTrainingLoad (at a given timestamp)
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
			WHERE `time` BETWEEN '.($time - Configuration::Trimp()->daysForCTL()*DAY_IN_S).' AND '.$time.'
			LIMIT 1
		')->fetch();

		$CTL = round($Data['sum']/Configuration::Trimp()->daysForCTL());

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
	 * ATL/CTL: SUM(`trimp`) for Configuration::Trimp()->daysForATL() / Configuration::Trimp()->daysForCTL()
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

		$ATLdays = Configuration::Trimp()->daysForATL();
		$CTLdays = Configuration::Trimp()->daysForCTL();
		
		foreach ($Data as $dat) {
			$atl           = 0;
			$ctl           = 0;
			$index         = $dat['y']*365 + $dat['d'] - $start_i;
			$Trimp[$index] = $dat['trimp'];

			if ($index >= $ATLdays)
				$atl   = array_sum(array_slice($Trimp, 1 + $index - $ATLdays, $ATLdays)) / $ATLdays;
			if ($index >= $CTLdays)
				$ctl   = array_sum(array_slice($Trimp, 1 + $index - $CTLdays, $CTLdays)) / $CTLdays;

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
		Configuration::Data()->updateMaxATL($maxATL);

		self::$MAX_ATL = $maxATL;
	}

	/**
	 * Set MAX_CTL
	 * @param int $maxCTL 
	 */
	private static function setMaxCTL($maxCTL) {
		Configuration::Data()->updateMaxCTL($maxCTL);

		self::$MAX_CTL = $maxCTL;
	}

	/**
	 * Set MAX_TRIMP
	 * @param int $maxTRIMP 
	 */
	private static function setMaxTRIMP($maxTRIMP) {
		if (is_nan($maxTRIMP))
			return;

		Configuration::Data()->updateMaxTrimp($maxTRIMP);

		self::$MAX_TRIMP = $maxTRIMP;
	}
}