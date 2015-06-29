<?php
/**
 * This file contains class::Single
 * @package Runalyze\Model\Activity\Equipment
 */

namespace Runalyze\Model\Activity\Equipment;

use Runalyze\Model;

/**
 * Single Equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Clothes
 */
class Single extends Model\ObjectWithID {
	/**
	 * Name
	 * @var string
	 */
	const NAME = 'name';


	/**
	 * All properties
	 * @return array
	 */
	static public function allProperties() {
		return array(
			self::NAME
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
	 * Short name
	 * @return string
	 */
	public function shortName() {
		return $this->Data[self::SHORT_NAME];
	}

	/**
	 * Group order
	 * @return int
	 */
	public function groupOrder() {
		return $this->Data[self::GROUP_ORDER];
	}
}