<?php
/**
 * This file contains class::Gender
 * @package Runalyze\Parameter\Application
 */

namespace Runalyze\Parameter\Application;

/**
 * Gender
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class Gender extends \Runalyze\Parameter\Select {
	/**
	 * None
	 * @var string
	 */
	const NONE = 'none';

	/**
	 * Male
	 * @var string
	 */
	const MALE = 'm';

	/**
	 * Female
	 * @var string
	 */
	const FEMALE = 'f';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::NONE, array(
			'options'		=> array(
				self::NONE		=>	'--- '.__('please choose'),
				self::MALE		=>	__('male'),
				self::FEMALE	=>	__('female')
			)
		));
	}

	/**
	 * Is gender set?
	 * @return bool
	 */
	public function hasGender() {
		return !($this->value() == self::NONE);
	}

	/**
	 * Is male?
	 * @return bool
	 */
	public function isMale() {
		return ($this->value() == self::MALE);
	}

	/**
	 * Is female?
	 * @return bool
	 */
	public function isFemale() {
		return ($this->value() == self::FEMALE);
	}
}