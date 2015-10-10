<?php
/**
 * This file contains class::Prognosis
 * @package Runalyze\Calculation\Prognosis
 */

namespace Runalyze\Calculation\Prognosis;

/**
 * General class for a race prognosis
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Prognosis
 */
class Prognosis {
	/**
	 * Strategy
	 * @var \Runalyze\Calculation\Prognosis\AbstractStrategy
	 */
	private $Strategy = null;

	/**
	 * Set strategy
	 * @param \Runalyze\Calculation\Prognosis\AbstractStrategy $strategy
	 */
	public function setStrategy(AbstractStrategy $strategy) {
		$this->Strategy = $strategy;
	}

	/**
	 * Prognosis in seconds
	 * @param float $distance distance in km
	 * @return int
	 */
	public function inSeconds($distance) {
		if (null === $this->Strategy) {
			throw new \RuntimeException('Prognosis class requires a strategy to be set.');
		}

		return $this->Strategy->inSeconds($distance);
	}
}