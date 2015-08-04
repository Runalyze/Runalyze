<?php
/**
 * This file contains class::Single
 * @package Runalyze\Model\Activity\Clothes
 */

namespace Runalyze\Model\Activity\Clothes;

use Runalyze\Model;

/**
 * Single clothes
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
	 * Short name
	 * @var string
	 */
	const SHORT_NAME = 'short';

	/**
	 * Group order
	 * @var string
	 */
	const GROUP_ORDER = 'order';

	/**
	 * All properties
	 * @return array
	 */
	static public function allDatabaseProperties() {
		return array(
			self::NAME,
			self::SHORT_NAME,
			self::GROUP_ORDER
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