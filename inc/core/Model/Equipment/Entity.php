<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Equipment
 */

namespace Runalyze\Model\Equipment;

use Runalyze\Model;

/**
 * Type entity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Equipment
 */
class Entity extends Model\EntityWithID {
	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: typeid
	 * @var string
	 */
	const TYPEID = 'typeid';

	/**
	 * Key: notes
	 * @var string
	 */
	const NOTES = 'notes';

	/**
	 * Key: distance
	 * @var string
	 */
	const DISTANCE = 'distance';

	/**
	 * Key: duration
	 * @var string
	 */
	const TIME = 'time';

	/**
	 * Key: additional distance
	 * @var string
	 */
	const ADDITIONAL_KM = 'additional_km';

	/**
	 * Key: start date
	 * @var string
	 */
	const DATE_START = 'date_start';

	/**
	 * Key: end date
	 * @var string
	 */
	const DATE_END = 'date_end';

	/**
	 * All properties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::NAME,
			self::TYPEID,
			self::NOTES,
			self::DISTANCE,
			self::TIME,
			self::ADDITIONAL_KM,
			self::DATE_START,
			self::DATE_END
		);
	}

	/**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allDatabaseProperties();
	}

	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
			case self::DATE_START:
			case self::DATE_END:
				return true;
		}

		return false;
	}

	/**
	 * Synchronize
	 */
	public function synchronize() {
		parent::synchronize();

		$this->ensureAllNumericValues();
	}

	/**
	 * Ensure that numeric fields get numeric values
	 */
	protected function ensureAllNumericValues() {
		$this->ensureNumericValue(array(
			self::DISTANCE,
			self::TIME,
			self::ADDITIONAL_KM
		));
	}

	/**
	 * Name
	 * @return string
	 */
	public function name() {
		return $this->Data[self::NAME];
	}

	/**
	 * Equipment type id
	 * @return int
	 */
	public function typeid() {
		return $this->Data[self::TYPEID];
	}

	/**
	 * Notes
	 * @return string
	 */
	public function notes() {
		return $this->Data[self::NOTES];
	}

	/**
	 * Distance
	 * @return int [km]
	 */
	public function distance() {
		return $this->Data[self::DISTANCE];
	}

	/**
	 * Duration
	 * @return int [s]
	 */
	public function duration() {
		return $this->Data[self::TIME];
	}

	/**
	 * Additional distance
	 * @return int [km]
	 */
	public function additionalDistance() {
		return $this->Data[self::ADDITIONAL_KM];
	}

	/**
	 * Total distance
	 * @return int [km]
	 */
	public function totalDistance() {
		return $this->Data[self::DISTANCE] + $this->Data[self::ADDITIONAL_KM];
	}

	/**
	 * Start date
	 * @return string Y-m-d
	 */
	public function startDate() {
		return $this->Data[self::DATE_START];
	}

	/**
	 * Has this equipment a start date?
	 * @return boolean
	 */
	public function hasStartDate() {
		return (null !== $this->Data[self::DATE_START]);
	}

	/**
	 * End date
	 * @return string Y-m-d
	 */
	public function endDate() {
		return $this->Data[self::DATE_END];
	}

	/**
	 * Is this equipment still in use?
	 * @return boolean
	 */
	public function isInUse() {
		return (null === $this->Data[self::DATE_END]);
	}
}