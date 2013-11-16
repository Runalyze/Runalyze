<?php
/**
 * This file contains class::RunningPrognosisCameron
 * @package Runalyze\Calculations\Prognosis
 */
/**
 * Prognosis by David Cameron
 * 
 * Competition prediction based on formulas by David Cameron.
 * General formular: T2 = T1 x (D2 / D1) x (a / b), a and b special formulas.
 * Remark: distances in meters, times in minutes
 * @see http://www.infobarrel.com/Runners_Math_How_to_Predict_Your_Race_Time
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
class RunningPrognosisCameron extends RunningPrognosisStrategy {
	/**
	 * Best result: distance
	 * @var float in kilometers
	 */
	protected $BestDistance = 0;

	/**
	 * Best result: time
	 * @var float in seconds
	 */
	protected $BestTime = 0;

	/**
	 * Running setup from database
	 */
	public function setupFromDatabase() {
		$TopResult = $this->getTopResults(1);

		if (!empty($TopResult))
			$this->setReferenceResult($TopResult['distance'], $TopResult['s']);
	}

	/**
	 * Set reference result
	 * @param float $distance distance in km
	 * @param int $timeInSeconds time in s
	 */
	public function setReferenceResult($distance, $timeInSeconds) {
		$this->BestDistance = $distance;
		$this->BestTime     = $timeInSeconds;
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance in kilometer
	 * @return float prognosis in seconds
	 */
	public function inSeconds($distance) {
		if ($distance <= 0 || $this->BestDistance <= 0)
			return 0;

		$T1 = $this->BestTime / 60;
		$D1 = $this->BestDistance * 1000;
		$D2 = $distance * 1000;

		return 60 * $T1 * ($D2 / $D1) * ($this->aORb($D1) / $this->aOrB($D2));
	}

	/**
	 * Calculate a/b
	 * @param float $distanceInMeters
	 * @return float
	 */
	protected function aORb($distanceInMeters) {
		return 13.49681 - (0.000030363 * $distanceInMeters) + (835.7114 / pow($distanceInMeters, 0.7905) );
	}
}