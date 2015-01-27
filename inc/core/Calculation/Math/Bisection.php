<?php
/**
 * This file contains class::Bisection
 * @package Runalyze\Calculation\Math
 */

namespace Runalyze\Calculation\Math;

/**
 * Bisection
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math
 */
class Bisection {
	/**
	 * Maximum number of iterations
	 * @var int
	 */
	protected $MaxIterations = 100;

	/**
	 * Precision
	 * @var float
	 */
	protected $Epsilon = 0.01;

	/**
	 * Initial lower bound
	 * @var float
	 */
	protected $Lower;

	/**
	 * Current value
	 * @var float
	 */
	protected $Current;

	/**
	 * Initial upper bound
	 * @var float
	 */
	protected $Upper;

	/**
	 * Value to look for
	 * @var float
	 */
	protected $Goal;

	/**
	 * Function to evaluate
	 * @var \Closure
	 */
	protected $Function;

	/**
	 * Is the function decreasing?
	 * @var int
	 */
	protected $decreasingFactor;

	/**
	 * New bisection
	 * @param float $goal
	 * @param float $lowerBound
	 * @param float $upperBound
	 * @param \Closure $function
	 */
	public function __construct($goal, $lowerBound, $upperBound, \Closure $function) {
		$this->Goal = $goal;
		$this->Lower = $lowerBound;
		$this->Upper = $upperBound;
		$this->Function = $function;

		$this->Current = ($this->Lower + $this->Upper)/2;
		$this->decreasingFactor = ($this->Function->__invoke($this->Lower) > $this->Function->__invoke($this->Upper)) ? -1 : 1;
	}

	/**
	 * Set number of iterations
	 * @param int $iterations
	 */
	public function setIterations($iterations) {
		$this->MaxIterations = $iterations;
	}

	/**
	 * Set epsilon
	 * @param float $epsilon
	 */
	public function setEpsilon($epsilon) {
		$this->Epsilon = $epsilon;
	}

	/**
	 * Find value
	 * @return float
	 */
	public function findValue() {
		for ($i = 0; $i < $this->MaxIterations; ++$i) {
			$value = $this->Function->__invoke($this->Current);

			if (abs($value - $this->Goal) < $this->Epsilon) {
				break;
			} elseif ($this->decreasingFactor*$value > $this->Goal*$this->decreasingFactor ) {
				$this->Upper = $this->Current;
			} else {
				$this->Lower = $this->Current;
			}

			$this->Current = ($this->Lower + $this->Upper)/2;
		}

		return $this->Current;
	}
}