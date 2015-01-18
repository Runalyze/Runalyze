<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Type
 */

namespace Runalyze\Model\Type;

use Runalyze\Model;

/**
 * Type object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Type
 */
class Object extends Model\ObjectWithID {
	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: img
	 * @var string
	 */
	const ABBREVIATION = 'abbr';

	/**
	 * Key: RPE
	 * @var string
	 */
	const RPE = 'RPE';

	/**
	 * Key: short display
	 * @var string
	 */
	const SPORTID = 'sportid';

	/**
	 * All properties
	 * @return array
	 */
	static public function allProperties() {
		return array(
			self::NAME,
			self::ABBREVIATION,
			self::RPE,
			self::SPORTID
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
	 * Name
	 * @return string
	 */
	public function name() {
		return $this->Data[self::NAME];
	}

	/**
	 * Abbreviation
	 * @return string
	 */
	public function abbreviation() {
		return $this->Data[self::ABBREVIATION];
	}

	/**
	 * RPE value
	 * @return int
	 */
	public function rpe() {
		return $this->Data[self::RPE];
	}

	/**
	 * Sportid
	 * @return int
	 */
	public function sportid() {
		return $this->Data[self::SPORTID];
	}
}