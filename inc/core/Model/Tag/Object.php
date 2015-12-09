<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Tag
 */

namespace Runalyze\Model\Tag;

use Runalyze\Model;

/**
 * Tag object
 * 
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Model\Tag
 */
class Object extends Model\EntityWithID {
	/**
	 * Key: tag
	 * @var string
	 */
	const TAG = 'tag';



	/**
	 * All properties
	 * @return array
	 */
	static public function allDatabaseProperties() {
		return array(
			self::TAG,
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
	 * Tag
	 * @return string
	 */
	public function tag() {
		return $this->Data[self::TAG];
	}

}