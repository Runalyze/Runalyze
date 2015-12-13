<?php
/**
 * This file contains class::PaceSmoother
 * @package Runalyze\Calculation\Activity
 */

namespace Runalyze\Calculation\Activity;

use Runalyze\Model\Trackdata;

/**
 * Smooth pace curve
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Activity
 */
class PaceSmoother {
	/**
	 * @var int
	 */
	const MODE_STEP = 0;

	/**
	 * @var int
	 */
	const MODE_TIME = 1;

	/**
	 * @var int
	 */
	const MODE_DISTANCE = 2;

	/**
	 * @var \Runalyze\Model\Trackdata\Entity
	 */
	protected $Trackdata;

	/**
	 * @var \Runalyze\Model\Trackdata\Loop
	 */
	protected $Loop;

	/**
	 * @var array
	 */
	protected $Smoothed = array();

	/**
	 * @var int
	 */
	protected $Mode = 0;

	/**
	 * @var int|float
	 */
	protected $StepSize = 1;

	/**
	 * @var boolean
	 */
	protected $KeepArraySize = false;

	/**
	 * Smoother
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param boolean $keepArraySize [optional]
	 */
	public function __construct(Trackdata\Entity $trackdata, $keepArraySize = false) {
		$this->Trackdata = $trackdata;
		$this->Loop = new Trackdata\Loop($trackdata);
		$this->KeepArraySize = $keepArraySize;
	}

	/**
	 * Smooth data
	 * @param int|float $stepSize integer as steps, float as distance [km], int as time [s]
	 * @param int $mode [optional]
	 * @return array
	 */
	public function smooth($stepSize, $mode = self::MODE_STEP) {
		$this->reset();
		$this->set($stepSize, $mode);
		$this->runFastSmoothing();

		return $this->result();
	}

	/**
	 * Reset internals
	 */
	protected function reset() {
		$this->Smoothed = array();
		$this->Loop->reset();
	}

	/**
	 * Set internals
	 * @param int|float $stepSize
	 * @param int $mode
	 */
	protected function set($stepSize, $mode) {
		$this->StepSize = $stepSize;
		$this->Mode = $mode;
		$this->Loop->setStepSize($stepSize);
	}

	/**
	 * @return array
	 */
	public function result() {
		return $this->Smoothed;
	}

	/**
	 * Run fast smoothing
	 */
	protected function runFastSmoothing() {
		switch ($this->Mode) {
			case self::MODE_STEP:
				$this->runFastSmoothingForSteps();
				break;

			case self::MODE_TIME:
				$this->runFastSmoothingForKey(Trackdata\Entity::TIME);
				break;

			case self::MODE_DISTANCE:
				$this->runFastSmoothingForKey(Trackdata\Entity::DISTANCE);
				break;
		}
	}

	/**
	 * Run fast smoothing for step size
	 * 
	 * Although this does not look nice and is not the cleanest code,
	 * direct access to the arrays is approx. 5-times faster.
	 * (0.02s vs 0.11s for an array of 10.000 elements)
	 */
	protected function runFastSmoothingForSteps() {
		$distance = $this->Trackdata->distance();
		$time = $this->Trackdata->time();
		$lastDist = 0;
		$lastTime = 0;

		foreach ($distance as $i => $dist) {
			if ($i != 0 && $i % $this->StepSize == 0) {
				$pace = $dist - $lastDist > 0 ? round(($time[$i] - $lastTime)/($dist - $lastDist)) : 0;

				if ($this->KeepArraySize) {
					for ($j = 0; $j < $this->StepSize; ++$j) {
						$this->Smoothed[] = $pace;
					}
				} else {
					$this->Smoothed[] = $pace;
				}

				$lastDist = $dist;
				$lastTime = $time[$i];
			}
		}

		if ($this->KeepArraySize && !empty($distance)) {
			$pace = $dist - $lastDist > 0 ? round(($time[$i] - $lastTime)/($dist - $lastDist)) : 0;

			for ($j = count($this->Smoothed), $num = $this->Trackdata->num(); $j < $num; ++$j) {
				$this->Smoothed[] = $pace;
			}
		}
	}

	/**
	 * @param string $key Object key to move
	 */
	protected function runFastSmoothingForKey($key) {
		$started = false;

		while (!$this->Loop->isAtEnd()) {
			$this->Loop->move($key, $this->StepSize);
			$dist = $this->Loop->difference(Trackdata\Entity::DISTANCE);
			$pace = $dist > 0 ? round($this->Loop->difference(Trackdata\Entity::TIME)/$dist) : 0;

			if ($this->KeepArraySize) {
				$steps = $this->Loop->currentStepSize();

				if (!$started) {
					$steps += 1;
					$started = true;
				}

				for ($i = 0; $i < $steps; ++$i) {
					$this->Smoothed[] = $pace;
				}
			} else {
				$this->Smoothed[] = $pace;
			}
		}
	}
}