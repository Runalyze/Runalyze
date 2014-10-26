<?php
/**
 * This file contains class::MaximumCalculator
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Calculate maximum values for fitness/fatigue
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Performance
 */
class MaximumCalculator {
	/**
	 * Maximal fitness
	 * @var int
	 */
	protected $MaxFitness = 0;

	/**
	 * Maximal fatigue
	 * @var int
	 */
	protected $MaxFatigue = 0;

	/**
	 * Maximal trimp
	 * @var int
	 */
	protected $MaxTrimp = 0;

	/**
	 * Constructor
	 * @param \Closure $ModelCreator Closure that takes an trimp array as argument and creates a performance model.
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\Closure $ModelCreator, array $Data) {
		$Model = $ModelCreator($Data);

		if ($Model instanceof Model) {
			$Model->calculate();
			$Result = $Model->getArrays();

			if (!empty($Result)) {
				$this->MaxFitness = max($Result[Model::FITNESS]);
				$this->MaxFatigue = max($Result[Model::FATIGUE]);
			}

			if (!empty($Data)) {
				$this->MaxTrimp = max($Data);
			}
		} else {
			throw new \InvalidArgumentException('Closure has to create an instance of Model.');
		}
	}

	/**
	 * Maximal fitness
	 * @return int
	 */
	public function maxFitness() {
		return round($this->MaxFitness);
	}

	/**
	 * Maximal fatigue
	 * @return int
	 */
	public function maxFatigue() {
		return round($this->MaxFatigue);
	}

	/**
	 * Maximal trimp
	 * @return int
	 */
	public function maxTrimp() {
		return round($this->MaxTrimp);
	}
}