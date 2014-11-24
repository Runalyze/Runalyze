<?php
/**
 * This file contains class::Partner
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use Runalyze\Model\StringArrayObject;

/**
 * Training partner
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class Partner extends StringArrayObject {
	/**
	 * Separator
	 * @var string
	 */
	const SEPARATOR = ',';

	/**
	 * From string
	 * @param string $string
	 */
	public function fromString($string) {
		$this->Elements = array_map('trim', explode(self::SEPARATOR, $string));
	}

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		return implode(self::SEPARATOR.' ', $this->Elements);
	}
	
}