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
class Object extends Model\Object {
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
	 * Get activity id
	 * @return int
	 */
	public function activityID() {
		return $this->Data[self::ACTIVITYID];
	}

	/**
	 * Get time
	 * @return array
	 */
	public function time() {
		return $this->Data[self::TIME];
	}

	/**
	 * Get distance
	 * @return array
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Get pace
	 * @return array
	 */
	public function pace() {
		return $this->Data[self::PACE];
	}

	/**
	 * Get heart rate
	 * @return array
	 */
	public function heartRate() {
		return $this->Data[self::HEARTRATE];
	}

	/**
	 * Get cadence
	 * @return array
	 */
	public function cadence() {
		return $this->Data[self::CADENCE];
	}

	/**
	 * Get power
	 * @return array
	 */
	public function power() {
		return $this->Data[self::POWER];
	}

	/**
	 * Get temperature
	 * @return array
	 */
	public function temperature() {
		return $this->Data[self::TEMPERATURE];
	}

	/**
	 * Get ground contact time
	 * @return array
	 */
	public function groundcontact() {
		return $this->Data[self::GROUNDCONTACT];
	}

	/**
	 * Get vertical oscillation
	 * @return array
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
		return !$this->Pauses->areEmpty();
	}
}