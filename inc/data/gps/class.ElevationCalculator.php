<?php
/**
 * This file contains class::ElevationCalculator
 * @package Runalyze\Data\GPS
 */

use Runalyze\Configuration;
use Runalyze\Parameter\Application\ElevationMethod;

/**
 * Elevation calculator
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class ElevationCalculator {
	/**
	 * Distance (in x direction) between two points
	 * @var int
	 */
	const DISTANCE_BETWEEN_POINTS = 50;

	/**
	 * Elevation points
	 * @var array
	 */
	protected $ElevationPoints = array();

	/**
	 * Elevation method
	 * @var \Runalyze\Parameter\Application\ElevationMethod
	 */
	protected $Method;

	/**
	 * Treshold
	 * @var int
	 */
	protected $Treshold;

	/**
	 * Elevation points weeded
	 * @var array
	 */
	protected $ElevationPointsWeeded = array();

	/**
	 * Indices of weeded elevation points
	 * @var array
	 */
	protected $IndicesOfElevationPointsWeeded = array();

	/**
	 * Up/down points
	 * @var array
	 */
	protected $UpDownPoints = array();

	/**
	 * Constructor
	 * 
	 * If no options are set, the current configuration settings are used.
	 * 
	 * @param array $ElevationPoints
	 * @param \Runalyze\Parameter\Application\ElevationMethod $Method [optional]
	 * @param int $Treshold [optional]
	 */
	public function __construct($ElevationPoints, ElevationMethod $Method = null, $Treshold = null) {
		$this->ElevationPoints = $ElevationPoints;
		$this->Method = !is_null($Method) ? $Method : Configuration::ActivityView()->elevationMethod();
		$this->Treshold = !is_null($Treshold) ? $Treshold : Configuration::ActivityView()->elevationMinDiff();
	}

	/**
	 * Set treshold
	 * @param int $treshold
	 */
	public function setTreshold($treshold) {
		$this->Treshold = $treshold;
	}

	/**
	 * Set method
	 * @param \Runalyze\Parameter\Application\ElevationMethod $Method
	 */
	public function setMethod(ElevationMethod $Method) {
		$this->Method = $Method;
	}

	/**
	 * Get elevation
	 * @return int
	 */
	public function getElevation() {
		return max($this->getElevationUp(), $this->getElevationDown());
	}

	/**
	 * Get elevation up
	 * @return int
	 */
	public function getElevationUp() {
		return array_sum(array_filter($this->UpDownPoints, 'ElevationCalculator_Filter_Positive'));
	}

	/**
	 * Get elevation up
	 * @return int
	 */
	public function getElevationDown() {
		return -1 * array_sum(array_filter($this->UpDownPoints, 'ElevationCalculator_Filter_Negative'));
	}

	/**
	 * Get weeded elevation points
	 * @return array
	 */
	public function getElevationPointsWeeded() {
		return $this->ElevationPointsWeeded;
	}

	/**
	 * Get indices of weeded elevation points
	 * @return array
	 */
	public function getIndicesOfElevationPointsWeeded() {
		return $this->IndicesOfElevationPointsWeeded;
	}

	/**
	 * Calculate elevation
	 */
	public function calculateElevation() {
		$this->runAlgorithm();

		if (empty($this->ElevationPointsWeeded))
			$this->ElevationPointsWeeded = $this->ElevationPoints;

		$num = count($this->ElevationPointsWeeded);
		$this->UpDownPoints = array();

		for ($i = 1; $i < $num; $i++)
			$this->UpDownPoints[$i-1] = $this->ElevationPointsWeeded[$i] - $this->ElevationPointsWeeded[$i-1];
	}

	/**
	 * Run algorithm
	 */
	protected function runAlgorithm() {
		if (count($this->ElevationPoints) == 0)
			return;

		if ($this->Method->usesReumannWitkamm()) {
			$this->runAlgorithmReumannWitkamm();
		} elseif ($this->Method->usesDouglasPeucker()) {
			$this->runAlgorithmDouglasPeucker();
		} elseif ($this->Method->usesTreshold()) {
			$this->runAlgorithmTreshold();
		} else {
			$this->IndicesOfElevationPointsWeeded = range(0, count($this->ElevationPoints) - 1);
		}
	}

	/**
	 * Run algorithm: Treshold
	 */
	protected function runAlgorithmTreshold() {
		$i = 0;
		$this->ElevationPointsWeeded = array($this->ElevationPoints[0]);
		$this->IndicesOfElevationPointsWeeded = array(0);

		while (isset($this->ElevationPoints[$i+1])) {
			$isLastStepUp    = $this->ElevationPoints[$i] > end($this->ElevationPointsWeeded) && $this->ElevationPoints[$i+1] <= $this->ElevationPoints[$i];
			$isLastStepDown  = $this->ElevationPoints[$i] < end($this->ElevationPointsWeeded) && $this->ElevationPoints[$i+1] >= $this->ElevationPoints[$i];
			$isAboveTreshold = abs(end($this->ElevationPointsWeeded) - $this->ElevationPoints[$i]) > $this->Treshold;

			if (($isLastStepUp || $isLastStepDown) && $isAboveTreshold) {
				$this->IndicesOfElevationPointsWeeded[] = $i;
				$this->ElevationPointsWeeded[] = $this->ElevationPoints[$i];
			}

			$i++;
		}

		$this->IndicesOfElevationPointsWeeded[] = $i;
		$this->ElevationPointsWeeded[] = $this->ElevationPoints[$i];
	}

	/**
	 * Run algorithm: Douglas-Peucker
	 */
	protected function runAlgorithmDouglasPeucker() {
		$this->IndicesOfElevationPointsWeeded = array(0, count($this->ElevationPoints) - 1);
		$this->ElevationPointsWeeded = $this->douglasPeuckerAlgorithm($this->ElevationPoints, $this->Treshold);

		sort($this->IndicesOfElevationPointsWeeded);
	}

	/**
	 * Run douglas peucker algorithm
	 * @param array $pointList
	 * @param int $epsilon
	 * @param int $offset
	 * @return array
	 */
	protected function douglasPeuckerAlgorithm($pointList, $epsilon, $offset = 0) {
		$dmax = 0;
		$index = 0;
		$totalPoints = count($pointList);

		// Find point with maximum distance
		for ($i = 1; $i < ($totalPoints - 1); $i++) {
			$d = self::perpendicularDistance(
				self::DISTANCE_BETWEEN_POINTS*$i, $pointList[$i],
				self::DISTANCE_BETWEEN_POINTS*0, $pointList[0],
				self::DISTANCE_BETWEEN_POINTS*($totalPoints-1), $pointList[$totalPoints-1]
			);

			if ($d > $dmax) {
				$index = $i;
				$dmax = $d;
			}
		}

		// If max distance is greater than epsilon, recursively simplify
		if ($dmax > $epsilon) {
			$this->IndicesOfElevationPointsWeeded[] = $offset + $index;

			$recResults1 = $this->douglasPeuckerAlgorithm(array_slice($pointList, 0, $index + 1), $epsilon, $offset);
			$recResults2 = $this->douglasPeuckerAlgorithm(array_slice($pointList, $index, $totalPoints - $index), $epsilon, $offset + $index);

			return array_merge(array_slice($recResults1, 0, count($recResults1) - 1), array_slice($recResults2, 0, count($recResults2)));
		}

		return array($pointList[0], $pointList[$totalPoints-1]);
	}

	/**
	 * Run algorithm: Reumann-Witkamm
	 */
	protected function runAlgorithmReumannWitkamm() {
		Error::getInstance()->addTodo('Sorry, Reumann-Witkamm-Algorithm isn\'t implemented yet.');
	}

	/**
	 * Perpendicular distance from point to line
	 */
	static private function perpendicularDistance($pointX, $pointY, $line1x, $line1y, $line2x, $line2y) {
		if ($line2x == $line1x)
			return abs($pointX - $line2x);

		$slope        = ($line2y - $line1y) / ($line2x - $line1x);
        $passThroughY = -$line1x * $slope + $line1y;

		return (abs(($slope * $pointX) - $pointY + $passThroughY)) / (sqrt($slope*$slope + 1));
	}
}


/**
 * Filter function to remove all negative/zero values
 * @param mixed $value
 * @return boolean
 */
function ElevationCalculator_Filter_Positive($value) {
	return $value > 0;
}

/**
 * Filter function to remove all nonnegative values
 * @param mixed $value
 * @return boolean
 */
function ElevationCalculator_Filter_Negative($value) {
	return $value < 0;
}