<?php
/**
 * This file contains class::InverseCalculator
 * @package Runalyze\Calculation\Trimp
 */

namespace Runalyze\Calculation\Trimp;

/**
 * Inverse TRIMP-Calculator
 * 
 * TRIMP stands for Training Impulse and gives a measurement for the training impact.
 * 
 * This class computes the inverse of the standard Trimp calculator.
 * 
 * @see http://fellrnr.com/wiki/TRIMP
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Trimp
 */
class InverseCalculator {
	/**
	 * Athlete
	 * @var \Runalyze\Athlete
	 */
	protected $Athlete;

	/**
	 * Value
	 * @var int
	 */
	protected $value;

	/**
	 * Construct
	 * @param \Runalyze\Athlete $Athlete
	 * @param int $heartRate [bpm]
	 * @param int $trimp value to reach
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\Runalyze\Athlete $Athlete, $heartRate, $trimp) {
		$this->Athlete = $Athlete;

		if ($heartRate <= 0 || $trimp <= 0) {
			throw new \InvalidArgumentException('Heart rate and trimp have to be greater than zero.');
		}

		$this->calculate($heartRate, $trimp);
	}

	/**
	 * Inverse calculation
	 * 
	 * Calculation is done for HR in [bpm] and time is transformed from [s] to [min].
	 * 
	 * @param int $bpm [bpm]
	 * @param int $trimp value to reach
	 */
	protected function calculate($bpm, $trimp) {
		$Factor = new Factor($this->Athlete->gender());
		$max = $this->Athlete->knowsMaximalHeartRate() ? $this->Athlete->maximalHR() : Calculator::DEFAULT_HR_MAX;
		$rest = $this->Athlete->knowsRestingHeartRate() ? $this->Athlete->restingHR() : Calculator::DEFAULT_HR_REST;

		$hr = max(0, ($bpm - $rest) / ($max - $rest));

		$sum = $trimp / ( $hr * exp($Factor->B() * $hr) );

		$this->value = 1 / $Factor->A() * $sum * 60;
	}

	/**
	 * Value
	 * @return int
	 */
	public function value() {
		return $this->value;
	}
}