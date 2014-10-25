<?php
/**
 * This file contains class::Monotony
 * @package Runalyze\Calculation
 */

namespace Runalyze\Calculation;

/**
 * Monotony
 * 
 * @see http://fellrnr.com/wiki/Training_Monotony
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\TrainingLoad
 */
class Monotony {
	/**
	 * Maximum
	 */
	const MAX = 10;

	/**
	 * Trimp data
	 * @var array
	 */
	protected $TRIMP = array();

	/**
	 * Number of days
	 * @var int
	 */
	protected $Count;

	/**
	 * Trimp sum
	 * @var int
	 */
	protected $Sum = 0;

	/**
	 * Value
	 * @var float
	 */
	protected $Value = null;

	/**
	 * Construct
	 * @param array $trimpData
	 * @param int $count [optional]
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $trimpData, $count = 0) {
		if (empty($trimpData)) {
			throw new \InvalidArgumentException('Trimp array must not be empty.');
		}

		$this->TRIMP = $trimpData;
		$this->Count = ($count > 0) ? $count : count($trimpData);
	}

	/**
	 * Calculate
	 */
	public function calculate() {
		$this->Sum = array_sum($this->TRIMP);
		$mean = $this->Sum / $this->Count;
		$var = 0;

		foreach ($this->TRIMP as $Trimp) {
			$var += ($Trimp - $mean) * ($Trimp - $mean);
		}

		$var /= $this->Count;

		$this->Value = ($var == 0) ? self::MAX : $mean / sqrt($var);
	}

	/**
	 * Get complete arrays
	 * @return array array(enum => data)
	 * @throws \RuntimeException
	 */
	public function value() {
		if (is_null($this->Value)) {
			throw new \RuntimeException('Monotony has to be calculated first.');
		}

		return min($this->Value, self::MAX);
	}

	/**
	 * Training strain
	 * @return float
	 */
	public function trainingStrain() {
		return $this->Sum * $this->value();
	}
}