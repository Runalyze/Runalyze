<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Type
 */

namespace Runalyze\Model\Type;

use Runalyze\Model;

/**
 * Type entity
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Type
 */
class Entity extends Model\EntityWithID {
	/**
	 * Key: name
	 * @var string
	 */
	const NAME = 'name';

	/**
	 * Key: short display
	 * @var string
	 */
	const ABBREVIATION = 'abbr';

	/**
	 * Key: sport id
	 * @var string
	 */
	const SPORTID = 'sportid';

	/**
	 * Key: short mode
	 * @var string
	 */
	const SHORT = 'short';

	/**
	 * Key: avg. heart rate
	 * @var string
	 */
	const HR_AVG = 'hr_avg';

	/**
	 * Key: quality session
	 * @var string
	 */
	const QUALITY_SESSION = 'quality_session';

	/**
	 * All properties
	 * @return array
	 */
	public static function allDatabaseProperties() {
		return array(
			self::NAME,
			self::ABBREVIATION,
			self::SPORTID,
			self::SHORT,
			self::HR_AVG,
			self::QUALITY_SESSION
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
	 * Abbreviation
	 * @return string
	 */
	public function abbreviation() {
		return $this->Data[self::ABBREVIATION];
	}

	/**
	 * Sportid
	 * @return int
	 */
	public function sportid() {
		return $this->Data[self::SPORTID];
	}

	/**
	 * Uses short mode?
	 * @return boolean
	 */
	public function usesShortMode() {
		return ($this->Data[self::SHORT] == 1);
	}

	/**
	 * Avg. heart rate
	 * @return int
	 */
	public function hrAvg() {
		return $this->Data[self::HR_AVG];
	}

	/**
	 * Is quality session?
	 * @return bool
	 */
	public function isQualitySession() {
		return ($this->Data[self::QUALITY_SESSION] == 1);
	}
}