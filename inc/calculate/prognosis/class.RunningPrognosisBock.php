<?php
/**
 * This file contains class::RunningPrognosisBock
 * @package Runalyze\Calculations\Prognosis
 */
/**
 * Class: RunningPrognosisBock
 * 
 * Competition prediction based on CPP method by Robert Bock.
 * CPP stands for 'Competitive Performance Predictor'.
 * This method does not require the additional basic endurance calculation.
 * @see http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
class RunningPrognosisBock extends RunningPrognosisStrategy {
	/**
	 * Const K
	 * @var float 
	 */
	protected $CONST_K = 0;

	/**
	 * Const e
	 * @var float
	 */
	protected $CONST_e = 1;

	/**
	 * Minimal distance for best results
	 * @var float
	 */
	protected $MINIMAL_DISTANCE = 3;

	/**
	 * Running setup from database
	 */
	public function setupFromDatabase() {
		$TopResults = $this->getTopResults(2, $this->MINIMAL_DISTANCE);

		if (count($TopResults) < 2)
			return;

		if ($TopResults[0]['distance'] > $TopResults[1]['distance']) {
			$ResultShort = $TopResults[1];
			$ResultLong  = $TopResults[0];
		} else {
			$ResultShort = $TopResults[0];
			$ResultLong  = $TopResults[1];
		}

		$this->setFromResults($ResultShort['distance'], $ResultShort['s'], $ResultLong['distance'], $ResultLong['s']);
	}

	/**
	 * Set from results
	 * 
	 * Set const K/e from given results.
	 * The documented version does not work. Log-version is from source-code.
	 * @see http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html
	 * @see http://www.robert-bock.de/Sonstiges/cpp2.htm
	 * 
	 * @param float $distance_short distance in km for shorter result
	 * @param float $time_short time in seconds for shorter result
	 * @param float $distance_long distance in km for longer result
	 * @param float $time_long time in seconds for longer result
	 */
	public function setFromResults($distance_short, $time_short, $distance_long, $time_long) {
		if ($distance_short > $distance_long)
			list($distance_short, $time_short, $distance_long, $time_long) = array($distance_long, $time_long, $distance_short, $time_short);

		//$this->CONST_e = (($time_long - $time_short) / $time_short) * $distance_short / ($distance_long - $distance_short);
		$this->CONST_e = log($time_long / $time_short) / log($distance_long / $distance_short);
		$this->CONST_K = $time_long / pow($distance_long, $this->CONST_e);
	}

	/**
	 * Set minimal distance to look at
	 * 
	 * CPP method is not working good for very short distances.
	 * The minimal distance to recognize can be set.
	 * 
	 * @param float $distance distance in km
	 */
	public function setMinimalDistance($distance) {
		$this->MINIMAL_DISTANCE = $distance;
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance in kilometer
	 * @return float prognosis in seconds
	 */
	public function inSeconds($distance) {
		$seconds = $this->CONST_K * pow($distance, $this->CONST_e);

		return ($distance > 3) ? round($seconds) : $seconds;
	}

	/**
	 * Get K
	 * @return float
	 */
	public function getK() {
		return $this->CONST_K;
	}

	/**
	 * Get e
	 * @return float
	 */
	public function getE() {
		return $this->CONST_e;
	}
}