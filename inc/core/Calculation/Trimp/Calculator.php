<?php
/**
 * This file contains class::Calculator
 * @package Runalyze\Calculation\Trimp
 */

namespace Runalyze\Calculation\Trimp;

/**
 * TRIMP-Calculator
 * 
 * TRIMP stands for Training Impulse and gives a measurement for the training impact.
 * 
 * This class uses the exponential method with respect to the heart rate in %HRmax
 * and not in %HRrest to be independet of regular changes of the resting heart rate.
 * 
 * In generel a complete array of heart rate values is used:<br>
 * <code>$Trimp = new Calculator($Athlete, array(120 => 13, 121 => 47, ...));</code>
 * 
 * For an average only, use:<br>
 * <code>$Trimp = new Calculator($Athlete, array($avgHR => $totalDuration));</code>
 * 
 * To get consistent results for athletes without a maximal heart rate
 * (they may set it later), default values for HRmax and HRrest are used.
 * 
 * @see http://fellrnr.com/wiki/TRIMP
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Trimp
 */
class Calculator {
	/**
	 * Default HR max
	 * @int
	 */
	const DEFAULT_HR_MAX = 200;

	/**
	 * Default HR rest
	 * @int
	 */
	const DEFAULT_HR_REST = 60;

	/**
	 * Athlete
	 * @var \Runalyze\Athlete
	 */
	protected $Athlete;

	/**
	 * Data
	 * @var array
	 */
	protected $Data;

	/**
	 * Value
	 * @var int
	 */
	protected $value;

	/**
	 * Construct
	 * @param \Runalyze\Athlete $Athlete
	 * @param array $data array('hr' => 'time in seconds')
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\Runalyze\Athlete $Athlete, array $data) {
		$this->Athlete = $Athlete;
		$this->Data = $data;

		if (empty($data)) {
			throw new \InvalidArgumentException('Data array must not be empty.');
		}

		$this->calculate();
	}

	/**
	 * Calculate
	 * 
	 * Calculation is done for HR in [bpm] and time is transformed from [s] to [min].
	 */
	protected function calculate() {
		$Factor = new Factor($this->Athlete->gender());
		$max = $this->Athlete->knowsMaximalHeartRate() ? $this->Athlete->maximalHR() : self::DEFAULT_HR_MAX;
		$rest = $this->Athlete->knowsRestingHeartRate() ? $this->Athlete->restingHR() : self::DEFAULT_HR_REST;
		$sum = 0;
		$B = $Factor->B();

		foreach ($this->Data as $bpm => $t) {
			$hr = max(0, ($bpm - $rest) / ($max - $rest));
			$sum += $t / 60 * $hr * exp($B * $hr);
		}

		$this->value = $Factor->A() * $sum;
	}

	/**
	 * Value
	 * @return int
	 */
	public function value() {
		return $this->value;
	}
}