<?php
/**
 * This file contains class::Gender
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Gender
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class Gender extends ConfigurationValueSelect {
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
	 * Construct gender
	 * @param string $Key
	 */
	public function __construct($Key) {
		parent::__construct($Key, array(
			'default'		=> self::NONE,
			'label'			=> __('Gender'),
			'options'		=> array(
				self::NONE		=>	'--- '.__('please choose'),
				self::MALE		=>	__('male'),
				self::FEMALE	=>	__('female')
			),
			'onchange'		=> Ajax::$RELOAD_ALL
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

	/**
	 * As string
	 * @return string
	 */
	public function asString() {
		if ($this->isMale()) {
			return __('male');
		}

		if ($this->isFemale()) {
			return __('female');
		}

		return __('unknown');
	}
}