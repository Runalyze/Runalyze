<?php
/**
 * This file contains class::Entity
 * @package Runalyze\Model\Tag
 */

namespace Runalyze\Model\Tag;

use Runalyze\Model;

/**
 * Tag entity
 * 
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Model\Tag
 */
class Entity extends Model\EntityWithID {
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