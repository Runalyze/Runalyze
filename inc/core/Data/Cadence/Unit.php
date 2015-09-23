<?php
/**
 * This file contains class::Unit
 * @package Runalyze\Data\Cadence
 */

namespace Runalyze\Data\Cadence;

/**
 * Cadence unit
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Cadence
 */
class Unit {
	/**
	 * @var int
	 */
	const GENERAL = 1;

	/**
	 * @var int
	 */
	const RUNNING = 2;

	/**
	 * Complete list
	 * @return array
	 */
	public static function completeList() {
		return array(
			self::GENERAL,
			self::RUNNING
		);
	}

	/**
	 * Create cadence
	 * @param int $identifier a class constant
	 * @param int $value optional
	 * @return \Runalyze\Data\Cadence\AbstractCadence
	 */
	public function get($identifier, $value = 0) {
		switch ($identifier) {
			case self::RUNNING:
				return new Running($value);
			case self::GENERAL:
			default:
				return new General($value);
		}
	}
}