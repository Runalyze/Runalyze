<?php
/**
 * This file contains class::Loop
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Loop through object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
class Loop {
	/**
	 * Current index
	 * @var int
	 */
	protected $Index = 0;

	/**
	 * Last index
	 * @var int
	 */
	protected $LastIndex = 0;

	/**
	 * Step size
	 * @var int
	 */
	protected $StepSize = 1;

	/**
	 * Total size
	 * @var int
	 */
	protected $TotalSize;

	/**
	 * Object
	 * @var \Runalyze\Model\Loopable
	 */
	protected $Object;

	/**
	 * Construct
	 * @param \Runalyze\Model\Loopable $object
	 */
	public function __construct(Loopable $object) {
		$this->Object = $object;

		$this->countTotalSize();
		$this->reset();
	}

	/**
	 * Count total size
	 */
	protected function countTotalSize() {
		$this->TotalSize = $this->Object->num();
	}

	/**
	 * Reset
	 * 
	 * Sets the internal pointer to the beginning.
	 * This method does not change the step size.
	 */
	public function reset() {
		$this->Index = 0;
		$this->LastIndex = 0;
	}

	/**
	 * Set step size
	 * @param int $size
	 */
	public function setStepSize($size) {
		$this->StepSize = $size;
	}

	/**
	 * Move index forward
	 * @return boolean
	 */
	public function nextStep() {
		$this->LastIndex = $this->Index;
		$this->Index += $this->StepSize;

		if ($this->Index >= $this->TotalSize - 1) {
			$this->Index = $this->TotalSize - 1;
			return false;
		}

		return true;
	}

	/**
	 * Is at the end?
	 * @return boolean
	 */
	public function isAtEnd() {
		return ($this->Index >= $this->TotalSize - 1);
	}

	/**
	 * Move pointer
	 * @param enum $key
	 * @param float $value to move
	 * @throws \InvalidArgumentException
	 */
	protected function move($key, $value) {
		$this->moveTo($key, $this->current($key) + $value);
	}

	/**
	 * @return int
	 */
	public function index() {
		return $this->Index;
	}

	/**
	 * Go to index
	 * @param int $index
	 */
	public function goToIndex($index) {
		$this->LastIndex = $this->Index;
		$this->Index = $index;
	}

	/**
	 * Move pointer to
	 * @param enum $key
	 * @param float $target to move
	 * @throws \InvalidArgumentException
	 */
	protected function moveTo($key, $target) {
		if (!$this->Object->has($key)) {
			throw new \InvalidArgumentException('No array available.');
		}

		if ($target < $this->current($key)) {
			throw new \InvalidArgumentException('Target must be larger than current value.');
		}

		$this->LastIndex = $this->Index;

		do {
			$this->Index++;
		} while(
			!$this->isAtEnd() &&
			$this->Object->at($this->Index, $key) < $target
		);
	}

	/**
	 * Current value
	 * @param enum $key
	 * @return int
	 */
	public function current($key) {
		if ($this->Object->has($key)) {
			return $this->Object->at($this->Index, $key);
		}

		return 0;
	}

	/**
	 * Difference for current section
	 * @param enum $key
	 * @return float
	 */
	public function difference($key) {
		if ($this->Object->has($key)) {
			return $this->Object->at($this->Index, $key) - $this->Object->at($this->LastIndex, $key);
		}

		return 0;
	}

	/**
	 * Sum values for current section
	 * @param enum $key
	 * @return float
	 */
	public function sum($key) {
		$sum = 0;

		if ($this->Object->has($key)) {
			for ($i = $this->LastIndex; $i <= $this->Index; ++$i) {
				$sum += $this->Object->at($i, $key);
			}
		}

		return $sum;
	}

	/**
	 * Average value for current section
	 * @param enum $key
	 * @return int
	 */
	public function average($key) {
		if ($this->LastIndex == $this->Index) {
			return 0;
		}

		return $this->sum($key) / ($this->Index - $this->LastIndex + 1);
	}
}