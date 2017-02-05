<?php

namespace Runalyze\Mathematics\Numerics;

class Bisection
{
	/** @var int */
	protected $MaxIterations = 100;

	/** @var float */
	protected $Epsilon = 0.01;

	/** @var float */
	protected $Lower;

	/** @var float */
	protected $Current;

	/** @var float */
	protected $Upper;

	/** @var float */
	protected $Target;

	/** @var \Closure */
	protected $Function;

	/** @var int */
	protected $decreasingFactor;

	/**
	 * @param float $target
	 * @param float $lowerBound
	 * @param float $upperBound
	 * @param \Closure $function
	 */
	public function __construct($target, $lowerBound, $upperBound, \Closure $function)
    {
		$this->Target = $target;
		$this->Lower = $lowerBound;
		$this->Upper = $upperBound;
		$this->Function = $function;

		$this->Current = ($this->Lower + $this->Upper)/2;
		$this->decreasingFactor = ($this->Function->__invoke($this->Lower) > $this->Function->__invoke($this->Upper)) ? -1 : 1;
	}

	/**
	 * @param int $iterations
	 */
	public function setIterations($iterations)
    {
		$this->MaxIterations = $iterations;
	}

	/**
	 * @param float $epsilon
	 */
	public function setEpsilon($epsilon)
    {
		$this->Epsilon = $epsilon;
	}

	/**
	 * @return float
	 */
	public function findValue()
    {
		for ($i = 0; $i < $this->MaxIterations; ++$i) {
			$value = $this->Function->__invoke($this->Current);

			if (abs($value - $this->Target) < $this->Epsilon) {
				break;
			} elseif ($this->decreasingFactor*$value > $this->Target*$this->decreasingFactor ) {
				$this->Upper = $this->Current;
			} else {
				$this->Lower = $this->Current;
			}

			$this->Current = ($this->Lower + $this->Upper)/2;
		}

		return $this->Current;
	}
}
