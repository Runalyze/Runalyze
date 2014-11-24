<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Activity\Clothes
 */

namespace Runalyze\Model\Activity\Clothes;

use Runalyze\Model\StringArrayObject;

/**
 * Clothes object
 * 
 * Contains single clothes as IDs
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Clothes
 */
class Object extends StringArrayObject {
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