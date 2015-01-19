<?php
/**
 * This file contains class::Monotony
 * @package Runalyze\Calculation
 */

namespace Runalyze\Calculation;

use Runalyze\Calculation\Scale;
use Runalyze\Configuration;

/**
 * Monotony
 * 
 * @see http://fellrnr.com/wiki/Training_Monotony
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation
 */
class Monotony {
	/**
	 * Maximum
	 */
	const MAX = 10;

	/**
	 * Minimum
	 */
	const MIN = 0;

	/**
	 * Warning
	 */
	const WARNING = 1.5;

	/**
	 * Maximum
	 */
	const CRITICAL = 2;

	/**
	 * Number of days
	 */
	const DAYS = 7;

	/**
	 * Trimp data
	 * @var array
	 */
	protected $TRIMP = array();

	/**
	 * Trimp average
	 * @var int
	 */
	protected $Avg = 0;

	/**
	 * Value
	 * @var float
	 */
	protected $Value = null;

	/**
	 * Construct
	 * @param array $trimpData
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $trimpData) {
		$this->TRIMP = $trimpData;
	}

	/**
	 * Calculate
	 */
	public function calculate() {
		if (empty($this->TRIMP)) {
			$this->Value = 0;
			return;
		}

		$this->Avg = array_sum($this->TRIMP) / static::DAYS;
		$var = 0;

		foreach ($this->TRIMP as $Trimp) {
			$var += ($Trimp - $this->Avg) * ($Trimp - $this->Avg);
		}

		$var += (static::DAYS - count($this->TRIMP)) * $this->Avg * $this->Avg;

		$var /= static::DAYS;

		$this->Value = ($var == 0) ? self::MAX : $this->Avg / sqrt($var);
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

		return max(self::MIN, min($this->Value, self::MAX));
	}

	/**
	 * Training strain
	 * We use the Trimp-average instead of the sum to keep this value comparable.
	 * @return float
	 */
	public function trainingStrain() {
		return $this->Avg * static::DAYS * $this->value();
	}

	/**
	 * Scale value for percentage
	 * @return int
	 */
	public function valueAsPercentage() {
		$Scale = new Scale\TwoPartPercental();
		$Scale->setMinimum(self::MIN);
		$Scale->setInflectionPoint(self::CRITICAL);
		$Scale->setMaximum(self::MAX);

		return $Scale->transform($this->value());
	}

	/**
	 * Scale value for percentage
	 * @return int
	 */
	public function trainingStrainAsPercentage() {
		// TODO: Use another maximum?
		$max = 2 * Configuration::Data()->maxATL() * static::DAYS;
		$Scale = new Scale\Percental();
		$Scale->setMaximum($max);

		return $Scale->transform($this->trainingStrain());
	}
}