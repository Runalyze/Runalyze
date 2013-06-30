<?php
/**
 * This file contains class::ElevationCalculator
 * @package Runalyze\Data\GPS
 */
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
	static public $DISTANCE_BETWEEN_POINTS = 50;

	/**
	 * Used treshold
	 * @var int
	 */
	static private $TRESHOLD = CONF_ELEVATION_MIN_DIFF;

	/**
	 * Used algorithm for weeding
	 * @var enum
	 */
	static private $ALGORITHM = CONF_ELEVATION_METHOD;

	/**
	 * Algorithm: none
	 * @var enum
	 */
	static public $ALGORITHM_NONE = 'none';

	/**
	 * Algorithm: Treshold
	 * @var enum
	 */
	static public $ALGORITHM_TRESHOLD = 'treshold';

	/**
	 * Algorithm: Douglas-Peucker
	 * @var enum
	 */
	static public $ALGORITHM_DOUGLAS_PEUCKER = 'douglas-peucker';

	/**
	 * Algorithm: Reumann-Witkamm
	 * @var enum
	 */
	static public $ALGORITHM_REUMANN_WITKAMM = 'reumann-witkamm';

	/**
	 * Elevation points
	 * @var array
	 */
	protected $ElevationPoints = array();

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
	 * Set treshold
	 * @param int $treshold
	 */
	static public function setTreshold($treshold) {
		self::$TRESHOLD = $treshold;
	}

	/**
	 * Set algorithm
	 * @param enum $algorithm
	 */
	static public function setAlgorithm($algorithm) {
		self::$ALGORITHM = $algorithm;
	}

	/**
	 * Get name of current algorithm
	 * @return string
	 */
	static public function nameOfCurrentAlgorithm() {
		switch (self::$ALGORITHM) {
			case self::$ALGORITHM_REUMANN_WITKAMM:
				return 'Reumann-Witkamm-Algorithmus';
			case self::$ALGORITHM_DOUGLAS_PEUCKER:
				return 'Douglas-Peucker-Algorithmus';
			case self::$ALGORITHM_TRESHOLD:
				return 'Schwellenwert-Methode';
			case self::$ALGORITHM_NONE:
			default:
				return 'keine Gl&auml;ttung';
		}
	}

	/**
	 * Constructor
	 * @param array $ElevationPoints
	 */
	public function __construct($ElevationPoints) {
		$this->ElevationPoints = $ElevationPoints;
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

		switch (self::$ALGORITHM) {
			case self::$ALGORITHM_REUMANN_WITKAMM:
				$this->runAlgorithmReumannWitkamm();
				break;
			case self::$ALGORITHM_DOUGLAS_PEUCKER:
				$this->runAlgorithmDouglasPeucker();
				break;
			case self::$ALGORITHM_TRESHOLD:
				$this->runAlgorithmTreshold();
				break;
			case self::$ALGORITHM_NONE:
			default:
				$this->IndicesOfElevationPointsWeeded = range(0, count($this->ElevationPoints) - 1);
				break;
		}
	}

	/**
	 * Run algorithm: Treshold
	 */
	protected function runAlgorithmTreshold() {
		$i = 1;
		$this->ElevationPointsWeeded = array($this->ElevationPoints[0]);
		$this->IndicesOfElevationPointsWeeded = array(0);

		while (isset($this->ElevationPoints[$i+1])) {
			$isLastStepUp    = $this->ElevationPoints[$i] > end($this->ElevationPointsWeeded) && $this->ElevationPoints[$i+1] <= $this->ElevationPoints[$i];
			$isLastStepDown  = $this->ElevationPoints[$i] < end($this->ElevationPointsWeeded) && $this->ElevationPoints[$i+1] >= $this->ElevationPoints[$i];
			$isAboveTreshold = abs(end($this->ElevationPointsWeeded) - $this->ElevationPoints[$i]) > self::$TRESHOLD;

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
		$this->ElevationPointsWeeded = $this->douglasPeuckerAlgorithm($this->ElevationPoints, self::$TRESHOLD);

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
				self::$DISTANCE_BETWEEN_POINTS*$i, $pointList[$i],
				self::$DISTANCE_BETWEEN_POINTS*0, $pointList[0],
				self::$DISTANCE_BETWEEN_POINTS*($totalPoints-1), $pointList[$totalPoints-1]
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
			$recResults2 = $this->douglasPeuckerAlgorithm(array_slice($pointList, $index, $totalPoints - $index), $epsilon, $offset + $index);;

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