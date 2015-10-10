<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\HRV
 */

namespace Runalyze\Model\HRV;

use Runalyze\Model;

/**
 * HRV object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\HRV
 */
class Object extends Model\ObjectWithID implements Model\Loopable {
	/**
	 * Key: acitivityid
	 * @var string
	 */
	const ACTIVITYID = 'activityid';

	/**
	 * Key: data
	 * @var string
	 */
	const DATA = 'data';

	/**
	 * All properties
	 * @return array
	 */
	public static function allProperties() {
		return array(
			self::ACTIVITYID,
			self::DATA
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
		return ($key == self::DATA);
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::DATA:
				return true;
		}

		return false;
	}

	/**
	 * Ignore a key while checking for emptiness
	 * @param enum $key
	 * @return boolean
	 */
	protected function ignoreNonEmptyValue($key) {
		return ($key == self::ACTIVITYID);
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
	 * Get activitiy id
	 * @return int
	 */
	public function activityID() {
		return $this->Data[self::ACTIVITYID];
	}

	/**
	 * Data
	 * @return array unit: [ms]
	 */
	public function data() {
		return $this->Data[self::DATA];
	}
}