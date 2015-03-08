<?php
/**
 * This file contains class::Model
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Model for human performance
 * 
 * @see http://fellrnr.com/wiki/Modeling_Human_Performance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Performance
 */
abstract class Model {
	/**
	 * @var int
	 */
	const FITNESS = 0;

	/**
	 * @var int
	 */
	const FATIGUE = 1;

	/**
	 * @var int
	 */
	const PERFORMANCE = 2;

	/**
	 * Trimp data
	 * @var array
	 */
	protected $TRIMP = array();

	/**
	 * Fitness data
	 * @var array
	 */
	protected $Fitness = array();

	/**
	 * Fatigue data
	 * @var array
	 */
	protected $Fatigue = array();

	/**
	 * Performance data
	 * @var array
	 */
	protected $Performance = array();

	/**
	 * Time range
	 * @var array array('from' => int, 'to' => int)
	 */
	protected $Range = array();

	/**
	 * Construct
	 * @param array $trimpData array('days back' => 'trimp value')
	 */
	public function __construct(array $trimpData) {
		ksort($trimpData);

		$this->TRIMP = $trimpData;
	}

	/**
	 * Set time range
	 * @param int $from
	 * @param int $to
	 */
	final public function setRange($from, $to) {
		$this->Range = array(
			'from' => $from,
			'to' => $to
		);
	}

	/**
	 * Calculate values
	 */
	final public function calculate() {
		if (empty($this->Range)) {
			if (empty($this->TRIMP)) {
				$this->setRange(0, 0);
			} else {
				$Keys = array_keys($this->TRIMP);
				$this->setRange($Keys[0], max(0,end($Keys)));
			}
		}

		$this->prepareArrays();
		$this->calculateArrays();
		$this->finishArrays();
	}

	/**
	 * Prepare arrays
	 */
	protected function prepareArrays() {
		$this->Fitness[$this->Range['from']-1] = 0;
		$this->Fatigue[$this->Range['from']-1] = 0;
		$this->Performance[$this->Range['from']-1] = 0;
	}

	/**
	 * Finish arrays
	 */
	protected function finishArrays() {
		unset($this->Fitness[$this->Range['from']-1]);
		unset($this->Fatigue[$this->Range['from']-1]);
		unset($this->Performance[$this->Range['from']-1]);
	}

	/**
	 * Calculate arrays
	 */
	abstract protected function calculateArrays();

	/**
	 * Get complete arrays
	 * @return array array(enum => data)
	 */
	final public function getArrays() {
		return array(
			self::FITNESS => $this->Fitness,
			self::FATIGUE => $this->Fatigue,
			self::PERFORMANCE => $this->Performance
		);
	}

	/**
	 * @return int
	 */
	final public function maxFitness() {
		return round(max($this->Fitness));
	}

	/**
	 * @return int
	 */
	final public function maxFatigue() {
		return round(max($this->Fatigue));
	}

	/**
	 * @return int
	 */
	final public function maxPerformance() {
		return round(max($this->Performance));
	}

	/**
	 * @return int
	 */
	final public function minPerformance() {
		return round(min($this->Performance));
	}

	/**
	 * Fitness
	 * @param int $index 0 for today
	 * @return int
	 */
	final public function fitnessAt($index) {
		return $this->at($index, self::FITNESS);
	}

	/**
	 * Fatigue
	 * @param int $index 0 for today
	 * @return int
	 */
	final public function fatigueAt($index) {
		return $this->at($index, self::FATIGUE);
	}

	/**
	 * Performance
	 * @param int $index 0 for today
	 * @return int
	 */
	final public function performanceAt($index) {
		return $this->at($index, self::PERFORMANCE);
	}

	/**
	 * Get value
	 * @param int $index e.g. -1 for yesterday
	 * @param int $enum enum for value
	 * @return int
	 */
	private function at($index, $enum) {
		$Array = $this->arrayFor($enum);

		if (isset($Array[$index])) {
			return round($Array[$index]);
		}

		return 0;
	}

	/**
	 * Get array for enum
	 * @param int $enum
	 * @return array
	 */
	private function arrayFor($enum) {
		switch ($enum) {
			case self::FITNESS:
				return $this->Fitness;

			case self::FATIGUE:
				return $this->Fatigue;

			case self::PERFORMANCE:
				return $this->Performance;
		}

		return array();
	}
}