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
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Tag
 */
class Object extends Model\ObjectWithID {
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
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
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
		));
	}

	/**
	 * Tag
	 * @return string
	 */
	public function tag() {
		return $this->Data[self::TAG];
	}

}