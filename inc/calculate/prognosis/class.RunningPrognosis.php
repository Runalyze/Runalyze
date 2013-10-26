<?php
/**
 * This file contains class::RunningPrognosis
 * @package Runalyze\Calculations\Prognosis
 */
/**
 * Class: RunningPrognosis
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
class RunningPrognosis {
	/**
	 * Strategy
	 * @var RunningPrognosisStrategy
	 */
	private $Strategy = null;

	/**
	 * Set strategy
	 * @param RunningPrognosisStrategy $Strategy
	 */
	public function setStrategy(RunningPrognosisStrategy &$Strategy) {
		$this->Strategy = $Strategy;
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance distance in km
	 * @return int
	 */
	public function inSeconds($distance) {
		if (is_null($this->Strategy)) {
			Error::getInstance()->addError('RunningPrognosis requires a strategy to be set.');
			return 0;
		}

		return $this->Strategy->inSeconds($distance);
	}
}