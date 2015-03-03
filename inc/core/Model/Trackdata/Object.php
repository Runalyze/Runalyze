<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Trackdata
 */

namespace Runalyze\Model\Trackdata;

use Runalyze\Model;

/**
 * Trackdata object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Trackdata
 */
class Object extends Model\Object implements Model\Loopable {
	/**
	 * Key: activity id
	 * @var string
	 */
	const ACTIVITYID = 'activityid';

	/**
	 * Key: time
	 * @var string
	 */
	const TIME = 'time';

	/**
	 * Key: distance
	 * @var string
	 */
	const DISTANCE = 'distance';

	/**
	 * Key: pace
	 * @var string
	 */
	const PACE = 'pace';

	/**
	 * Key: heart rate
	 * @var string
	 */
	const HEARTRATE = 'heartrate';

	/**
	 * Key: cadence
	 * @var string
	 */
	const CADENCE = 'cadence';

	/**
	 * Key: power
	 * @var string
	 */
	const POWER = 'power';

	/**
	 * Key: temperature
	 * @var string
	 */
	const TEMPERATURE = 'temperature';

	/**
	 * Key: ground contact time
	 * @var string
	 */
	const GROUNDCONTACT = 'groundcontact';

	/**
	 * Key: vertical oscillation
	 * @var string
	 */
	const VERTICAL_OSCILLATION = 'vertical_oscillation';

	/**
	 * Key: pauses
	 * @var string
	 */
	const PAUSES = 'pauses';

	/**
	 * Pauses
	 * @var \Runalyze\Model\Trackdata\Pauses
	 */
	protected $Pauses = null;

	/**
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		parent::__construct($data);

		$this->checkArraySizes();
		$this->readPauses();
	}

	/**
	 * Check array sizes
	 * @throws \RuntimeException
	 */
	protected function checkArraySizes() {
		foreach ($this->properties() as $key) {
			if ($this->isArray($key)) {
				try {
					$count = count($this->Data[$key]);

					if (($key == self::HEARTRATE || $key == self::CADENCE) && $this->numberOfPoints > 0) {
						if ($count == 1 + $this->numberOfPoints) {
							$this->Data[$key] = array_slice($this->Data[$key], 1);
						} elseif ($count == $this->numberOfPoints - 1) {
							array_unshift($this->Data[$key], $this->Data[$key][0]);
						}
					} else {
						$this->checkArraySize( $count );
					}
				} catch(\RuntimeException $E) {
					throw new \RuntimeException($E->getMessage().' (for '.$key.')');
				}
			}
		}
	}

	/**
	 * Read pauses
	 */
	protected function readPauses() {
		$this->Pauses = new Pauses($this->Data[self::PAUSES]);
	}

	/**
	 * Synchronize
	 */
	public function synchronize() {
		$this->Data[self::PAUSES] = $this->Pauses->asString();
	}

	/**
	 * All properties
	 * @return array
	 */
	static public function allProperties() {
		return array(
			self::ACTIVITYID,
			self::TIME,
			self::DISTANCE,
			self::PACE,
			self::HEARTRATE,
			self::CADENCE,
			self::POWER,
			self::TEMPERATURE,
			self::GROUNDCONTACT,
			self::VERTICAL_OSCILLATION,
			self::PAUSES
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allProperties();
	}

	/**
	 * Is the property an array?
	 * @param string $key
	 * @return bool
	 */
	public function isArray($key) {
		return ($key != self::PAUSES && $key != self::ACTIVITYID);
	}

	/**
	 * Clear
	 */
	public function clear() {
		parent::clear();

		$this->Pauses->clear();
	}

	/**
	 * Can set key?
	 * @param string $key
	 * @return boolean
	 */
	protected function canSet($key) {
		if ($key == self::PAUSES) {
			return false;
		}

		return true;
	}

	/**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}

	/**
	 * Value at
	 * 
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param enum $key
	 * @return mixed
	 */
	public function at($index, $key) {
		return $this->Data[$key][$index];
	}

	/**
	 * Get activity id
	 * @return int
	 */
	public function activityID() {
		return $this->Data[self::ACTIVITYID];
	}

	/**
	 * Get time
	 * @return array unit: [s]
	 */
	public function time() {
		return $this->Data[self::TIME];
	}

	/**
	 * Total time
	 * @return int
	 */
	public function totalTime() {
		return $this->Data[self::TIME][$this->numberOfPoints-1];
	}

	/**
	 * Get distance
	 * @return array unit: [km]
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Total distance
	 * @return int
	 */
	public function totalDistance() {
		return $this->Data[self::DISTANCE][$this->numberOfPoints-1];
	}

	/**
	 * Get pace
	 * @return array unit: [s/km]
	 */
	public function pace() {
		return $this->Data[self::PACE];
	}

	/**
	 * Get heart rate
	 * @return array unit: [bpm]
	 */
	public function heartRate() {
		return $this->Data[self::HEARTRATE];
	}

	/**
	 * Get cadence
	 * @return array unit: [rpm]
	 */
	public function cadence() {
		return $this->Data[self::CADENCE];
	}

	/**
	 * Get power
	 * @return array unit: [W]
	 */
	public function power() {
		return $this->Data[self::POWER];
	}

	/**
	 * Get temperature
	 * @return array unit: [°C]
	 */
	public function temperature() {
		return $this->Data[self::TEMPERATURE];
	}

	/**
	 * Get ground contact time
	 * @return array unit: [ms]
	 */
	public function groundcontact() {
		return $this->Data[self::GROUNDCONTACT];
	}

	/**
	 * Get vertical oscillation
	 * @return array unit: [mm]
	 */
	public function verticalOscillation() {
		return $this->Data[self::VERTICAL_OSCILLATION];
	}

	/**
	 * Get pauses
	 * @return \Runalyze\Model\Trackdata\Pauses
	 */
	public function pauses() {
		return $this->Pauses;
	}

	/**
	 * Are there pauses?
	 * @return bool
	 */
	public function hasPauses() {
		return !$this->Pauses->isEmpty();
	}
}