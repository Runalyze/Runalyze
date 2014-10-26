<?php
/**
 * This file contains class::Monotony
 * @package Runalyze\Calculation
 */

namespace Runalyze\Calculation;

use Runalyze\Calculation\Scale;

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
	 * Maximum
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
		$this->Avg = array_sum($this->TRIMP) / $this->Count;
		$var = 0;

		foreach ($this->TRIMP as $Trimp) {
			$var += ($Trimp - $this->Avg) * ($Trimp - $this->Avg);
		}

		$var /= $this->Count;

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
		return $this->Avg * $this->value();
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
		$max = 2 * \Configuration::Data()->maxATL();
		$Scale = new Scale\Percental();
		$Scale->setMaximum($max);

		return $Scale->transform($this->trainingStrain());
	}
}